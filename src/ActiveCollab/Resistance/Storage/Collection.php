<?php
  namespace ActiveCollab\Resistance\Storage;

  use ActiveCollab\Resistance\Storage\Field\Field;
  use ActiveCollab\Resistance\Error\Error;
  use Redis;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  abstract class Collection extends Storage
  {
    /**
     * Return true if we have a record under $id
     *
     * @param  integer $id
     * @return bool
     */
    public function exists($id)
    {
      return (boolean) $this->connection->exists($this->getKeyById($id));
    }

    /**
     * Return by ID
     *
     * @param  integer $id
     * @param  boolean $check_for_existance
     * @return array
     * @throws Error
     */
    public function get($id, $check_for_existance = true)
    {
      $id = (integer) $id; // Make sure that ID is int, because we will return it

      $key = $this->getKeyById($id);

      if ($check_for_existance && !$this->connection->exists($key)) {
        throw new Error("Data not found at key '$key'");
      }

      $data = $this->connection->hgetall($key);

      foreach ($data as $k => $v) {
        if (isset($this->fields[$k])) {
          $data[$k] = $this->fields[$k]->unserialize($v);
        } else {
          unset($data[$k]);
        }
      }

      return array_merge([ '_id' => $id ], $data);
    }

    /**
     * Return field value for the given record
     *
     * @param  integer $id
     * @param  string  $field
     * @param  bool    $check_for_existance
     * @return string
     * @throws Error
     */
    public function getFieldValue($id, $field, $check_for_existance = true)
    {
      if ($this->fields[$field]) {
        $key = $this->getKeyById($id);

        if ($check_for_existance && !$this->connection->exists($key)) {
          throw new Error("Data not found at key '$key'");
        }

        return $this->fields[$field]->unserialize($this->connection->hget($key, $field));
      } else {
        throw new Error("Field '$field' is not defined");
      }
    }

    /**
     * Return all records
     *
     * @param callable $callback
     */
    public function each(callable $callback)
    {
      if ($this->count()) {
        $iteration = 1;

        foreach ($this->getIds() as $id) {
          if (call_user_func($callback, $this->get($id, false), $iteration++) === false) {
            return; // Break on FALSE
          }
        }
      }
    }

    /**
     * Insert a new record into the data store
     *
     * @return integer[]
     * @throws Error
     */
    public function insert()
    {
      $ids = [];

      foreach (func_get_args() as $data) {
        foreach ($this->protected_fields as $protected_field) {
          if (array_key_exists($protected_field, $data)) {
            throw new Error("Field '$protected_field' is protected and can't be set on insert");
          }
        }

        foreach ($this->fields as $field_name => $field) {
          if (array_key_exists($field_name, $data)) {
            $data[$field_name] = $field->cast($data[$field_name]);
          } else {
            $data[$field_name] = $field->getDefaultValue();
          }

          $field->validate($field_name, $data[$field_name]);
          $data[$field_name] = $field->serialize($data[$field_name]);
        }

        if (!empty($this->unique_fields)) {
          foreach ($data as $field_name => $field_value) {
            if (in_array($field_name, $this->unique_fields) && $this->connection->sismember($this->getUniquenessKeyByField($field_name), $field_value)) {
              throw new Error("Value of '$field_name' needs to be unique");
            }
          }
        }

        $id = $this->getNextId();

        $this->transaction(function ($t) use ($id, $data) {

          /** @var $t Redis */
          $t->hmset($this->getKeyById($id), $data);
          $t->sadd($this->getIdsKey(), $id);
          $t->incr($this->getCountKey());

          foreach ($this->unique_fields as $field_name) {
            $t->sadd($this->getUniquenessKeyByField($field_name), $data[$field_name]);
          }

          foreach ($this->mapped_fields as $field_name) {
            $t->sadd($this->getMapKeyByFieldAndValue($field_name, $data[$field_name]), $id);
          }

          $t->set($this->getNextIdKey(), $id + 1);
        });

        $ids[] = $id;
      }

      return $ids;
    }

    /**
     * @param  integer $id
     * @param  array   $data
     * @throws Error
     */
    public function update($id, array $data)
    {
      $key = $this->getKeyById($id);

      if ($this->connection->exists($key)) {
        foreach ($data as $k => $v) {
          if (isset($this->fields[$k])) {
            $data[$k] = $this->fields[$k]->cast($v);

            $this->fields[$k]->validate($k, $data[$k]);
            $data[$k] = $this->fields[$k]->serialize($data[$k]);
          } else {
            unset($data[$k]);
          }
        }

        if (!empty($this->unique_fields)) {
          foreach ($data as $field_name => $field_value) {
            if (in_array($field_name, $this->unique_fields) && $field_value != $this->getFieldValue($id, $field_name) && $this->connection->sismember($this->getUniquenessKeyByField($field_name), $field_value)) {
              throw new Error("Value of '$field_name' needs to be unique");
            }
          }
        }

        $mapped_fields_to_update = [];

        foreach ($this->mapped_fields as $field_name) {
          if (array_key_exists($field_name, $data)) {
            $mapped_fields_to_update[] = $field_name;
          }
        }

        foreach ($mapped_fields_to_update as $field_name) {
          $field_value_key = $this->getMapKeyByFieldAndValue($field_name, $this->getFieldValue($id, $field_name));

          if ($this->connection->scard($field_value_key) > 1) {
            $this->connection->srem($field_value_key, $id);
          } else {
            $this->connection->del($field_value_key);
          }
        }

        $this->transaction(function ($t) use ($id, $key, $data) {
          /** @var Redis $t */
          foreach ($data as $k => $v) {
            $t->hset($key, $k, $v);
          }
        });

        foreach ($mapped_fields_to_update as $field_name) {
          $this->connection->sadd($this->getMapKeyByFieldAndValue($field_name, $data[$field_name]), $id);
        }
      } else {
        throw new Error("Data not found at key '$key'");
      }
    }

    /**
     * Remove by ID
     *
     * @param integer $id
     */
    public function delete($id)
    {
      $key = $this->getKeyById($id);

      if ($this->connection->exists($key)) {
        $this->transaction(function ($t) use ($key, $id) {

          foreach ($this->mapped_fields as $field_name) {
            $this->connection->srem($this->getMapKeyByFieldAndValue($field_name, $this->getFieldValue($id, $field_name)), $id);
          }

          foreach ($this->unique_fields as $field_name) {
            $this->connection->srem($this->getUniquenessKeyByField($field_name), $this->getFieldValue($id, $field_name));
          }

          /** @var Redis $t */
          $t->del($key);
          $t->srem($this->getIdsKey(), $id);
          $t->decr($this->getCountKey());
        });
      }
    }

    /**
     * Return number of records that are in the storage
     *
     * @return integer
     */
    public function count()
    {
      return (integer) $this->connection->get($this->getCountKey());
    }

    /**
     * Return next item ID
     *
     * @return integer
     */
    public function getNextId()
    {
      $next_id = $this->connection->get($this->getNextIdKey());

      if (!$next_id) {
        $this->connection->set($this->getNextIdKey(), 1);

        return 1;
      }

      return (integer) $next_id;
    }

    /**
     * Return ID-s stored in this storage
     *
     * @return int[]
     */
    public function getIds()
    {
      $ids = array_map('intval', $this->connection->smembers($this->getIdsKey()));

      if (count($ids)) {
        sort($ids);
      }

      return $ids;
    }

    /**
     * Get ID-s of records where $field_name has $field_value
     *
     * Note that filed needs to be mapped for this to work. If not, this method will throw an exection
     *
     * @param  string $field_name
     * @param  string $value
     * @return array
     * @throws Error
     */
    public function getIdsBy($field_name, $value)
    {
      if ($this->isMapped($field_name)) {
        $ids = array_map('intval', $this->connection->smembers($this->getMapKeyByFieldAndValue($field_name, $value)));

        if (count($ids)) {
          sort($ids);
        }

        return $ids;
      } else {
        throw new Error("Field '$field_name' is not mapped");
      }
    }

    /**
     * Return first ID by field and value
     *
     * @param  string $field_name
     * @param  string $value
     * @return int
     * @throws Error
     */
    public function getIdBy($field_name, $value)
    {
      if ($this->isMapped($field_name)) {
        $ids = $this->connection->smembers($this->getMapKeyByFieldAndValue($field_name, $value));

        if (empty($ids)) {
          return 0;
        } else if (count($ids) == 1) {
          return (integer) array_shift($ids);
        } else {
          throw new Error("Multiple ID-s found for '$field_name' field '$value' value");
        }
      } else {
        throw new Error("Field '$field_name' is not mapped");
      }
    }

    // ---------------------------------------------------
    //  Fields
    // ---------------------------------------------------

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * Set up field configuration
     *
     * @param Field[] $fields
     */
    protected function setFields(array $fields)
    {
      $this->fields = $fields;

      foreach ($this->fields as $field_name => $field) {
        if ($field->isMapped()) {
          $this->mapped_fields[] = $field_name;
        }

        if ($field->isUnique()) {
          $this->unique_fields[] = $field_name;
        }

        if ($field->isProtected()) {
          $this->protected_fields[] = $field_name;
        }
      }
    }

    /**
     * Return true if $field_name field exists in this collection
     *
     * @param  string  $field_name
     * @return boolean
     */
    public function fieldExists($field_name)
    {
      return isset($this->fields[$field_name]) && $this->fields[$field_name] instanceof Field;
    }

    /**
     * @var string[]
     */
    private $mapped_fields = [], $unique_fields = [], $protected_fields = [];

    /**
     * @param  string $field_name
     * @return bool
     */
    public function isRequired($field_name)
    {
      return $this->fieldExists($field_name) && $this->fields[$field_name]->isRequired();
    }

    /**
     * Return true if $field_name is mapped
     *
     * @param  string $field_name
     * @return bool
     */
    public function isMapped($field_name)
    {
      return $this->fieldExists($field_name) && in_array($field_name, $this->mapped_fields);
    }

    /**
     * Return true if $field_name is unique in this storage
     *
     * @param  string $field_name
     * @return bool
     */
    public function isUnique($field_name)
    {
      return $this->fieldExists($field_name) && in_array($field_name, $this->unique_fields);
    }

    /**
     * Return true if $field_name is unique in this storage
     *
     * @param  string $field_name
     * @return bool
     */
    public function isProtected($field_name)
    {
      return $this->fieldExists($field_name) && in_array($field_name, $this->protected_fields);
    }

    // ---------------------------------------------------
    //  Migration routines
    // ---------------------------------------------------

    /**
     * Walk through all records and set value of $field_name to $default_value
     *
     * If $default_value is callable, it will be called for each record
     *
     * @param  string $field_name
     * @param  mixed  $default_value
     * @throws Error
     */
    public function bulkSetFieldValue($field_name, $default_value = null)
    {
      if ($this->fieldExists($field_name)) {
        foreach ($this->getIds() as $id) {
          $this->update($id, [
            $field_name => is_callable($default_value) ? call_user_func($default_value, $id) : $default_value
          ]);
        }
      } else {
        throw new Error("Field '$field_name' does not exist");
      }
    }

    /**
     * Clean up Redis database when field is removed
     *
     * @param string $field_name
     */
    public function bulkRemoveFieldValue($field_name)
    {
      foreach ($this->getIds() as $id) {
        $this->connection->hdel($this->getKeyById($id), $field_name);
      }
    }

    /**
     * Build value map for the given field
     *
     * @param string $field_name
     */
    public function buildValueMap($field_name)
    {
      $this->transaction(function ($t) use ($field_name) {
        /** @var Redis $t */
        $this->each(function($data) use (&$t, $field_name) {
          $this->connection->sadd($this->getMapKeyByFieldAndValue($field_name, $data[$field_name]), $data['_id']);
        });
      });

      if (!in_array($field_name, $this->mapped_fields)) {
        $this->mapped_fields[] = $field_name;
      }
    }

    /**
     * @param string $field_name
     */
    public function removeValueMap($field_name)
    {
      $keys = $this->getKeysByPattern($this->getMapKeyByFieldAndValue($field_name, '*'));

      if (!empty($keys)) {
        $this->connection->del($keys);
      }

      if (($key = array_search($field_name, $this->mapped_fields)) !== false) {
        unset($this->mapped_fields[$key]);
      }
    }

    /**
     * @param  string $field_name
     * @throws Error
     */
    public function buildUniquenessMap($field_name)
    {
      $values = [];

      foreach ($this->getIds() as $id) {
        $value = $this->getFieldValue($id, $field_name);

        if (in_array($value, $values)) {
          throw new Error("Can't make field '$field_name' unique. Value '$value' is stored at least twice");
        }

        $values[] = $value;
      }

      if (count($values)) {
        $uniqueness_key = $this->getUniquenessKeyByField($field_name);

        foreach ($values as $value) {
          $this->connection->sadd($uniqueness_key, $value);
        }
      }

      if (!in_array($field_name, $this->unique_fields)) {
        $this->unique_fields[] = $field_name;
      }
    }

    /**
     * Remove uniquness from the given field
     *
     * @param string $field_name
     */
    public function removeUniquenessMap($field_name)
    {
      $this->connection->del([ $this->getUniquenessKeyByField($field_name) ]);

      if (($key = array_search($field_name, $this->unique_fields)) !== false) {
        unset($this->unique_fields[$key]);
      }
    }

    // ---------------------------------------------------
    //  Namespace and keys
    // ---------------------------------------------------

    /**
     * Return ID-s list key
     *
     * @return string
     */
    public function getIdsKey()
    {
      return "{$this->namespace}:ids";
    }

    /**
     * @return string
     */
    public function getNextIdKey()
    {
      return "{$this->namespace}:next_id";
    }

    /**
     * @return string
     */
    public function getCountKey()
    {
      return "{$this->namespace}:count";
    }

    /**
     * Return expected key by record ID
     *
     * @param  integer $id
     * @return string
     */
    public function getKeyById($id)
    {
      return "$this->namespace:$id";
    }

    /**
     * Get key name for field value mapping
     *
     * @param  mixed  $field_name
     * @param  mixed  $value
     * @return string
     * @throws Error
     */
    public function getMapKeyByFieldAndValue($field_name, $value)
    {
      if ($this->fieldExists($field_name)) {
        $casted_to_be_used_in_key = $this->fields[$field_name]->castForMapKey($value);

        if (empty($casted_to_be_used_in_key)) {
          return "{$this->namespace}:map:{$field_name}-empty";
        } else {
          return "{$this->namespace}:map:{$field_name}:$casted_to_be_used_in_key";
        }
      } else {
        throw new Error("Field '$field_name' is not present");
      }
    }

    /**
     * Return key where we'll store uniqueness data for a given field
     *
     * @param  string $field_name
     * @return string
     */
    public function getUniquenessKeyByField($field_name)
    {
      return "{$this->namespace}:unq:{$field_name}";
    }
  }