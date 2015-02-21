<?php
  namespace ActiveCollab\Resistance\Storage;

  use ActiveCollab\Resistance\Error\Error;
  use Doctrine\Common\Inflector\Inflector;
  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Field\Field;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  abstract class Storage
  {
    /**
     * @var Client
     */
    protected $connection;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Construct a new storage instance
     *
     * @param Client $connection
     * @param string $application_namespace
     */
    public function __construct(Client &$connection, $application_namespace)
    {
      $this->connection = $connection;
      $this->namespace = $application_namespace;

      if ($this->namespace) {
        $this->namespace .= ':';
      }

      $this->namespace .= Inflector::camelize(array_pop(explode('\\', get_class($this))));
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
      $key = $this->getKeyById($id);

      if ($check_for_existance && !$this->connection->exists($key)) {
        throw new Error("Data not found at key '$key'");
      }

      $data = $this->connection->hgetall($key);

      foreach ($data as $k => $v) {
        if (isset($this->fields[$k])) {
          $data[$k] = $this->fields[$k]->cast($v);
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

        return $this->fields[$field]->cast($this->connection->hget($key, $field));
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
     */
    public function insert()
    {
      $ids = [];

      foreach (func_get_args() as $data) {
        foreach ($this->fields as $field_name => $field) {
          if (array_key_exists($field_name, $data)) {
            $data[$field_name] = $field->cast($data[$field_name]);
          } else {
            $data[$field_name] = $field->getDefaultValue();
          }

          $field->validate($field_name, $data[$field_name]);
        }

        $id = $this->getNextId();

        $this->connection->transaction(function ($t) use ($id, $data) {

          /** @var $t \Predis\Client */
          $t->hmset($this->getKeyById($id), $data); // $this->connection->hmset($this->getKeyById($id), $data);
          $t->sadd($this->getIdsKey(), [ $id ]);   // $this->connection->sadd($this->getIdsKey(), [ $id ]);
          $t->incr($this->getCountKey());           // $this->connection->incr($this->getCountKey());
          $t->set($this->getNextIdKey(), $id + 1);  // $this->connection->set($this->getNextIdKey(), $id + 1);
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
          } else {
            unset($data[$k]);
          }
        }

        $this->connection->transaction(function ($t) use ($key, $data) {

          /** @var $t \Predis\Client */
          foreach ($data as $k => $v) {
            $t->hset($key, $k, $v);
          }
        });
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
        $this->connection->transaction(function ($t) use ($key, $id) {

          /** @var $t \Predis\Client */
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
      return (integer) $this->connection->get($this->getNamespace() . '_count');
    }

    /**
     * Clear the storage
     */
    public function clear()
    {
      if ($this->count()) {
        foreach ($this->getIds() as $id) {
          $this->connection->del($this->getKeyById($id));
        }
      }

      $this->connection->del($this->getNextIdKey());
      $this->connection->del($this->getCountKey());
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

      return $next_id;
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
    }

    // ---------------------------------------------------
    //  Namespace and keys
    // ---------------------------------------------------

    /**
     * Return data namespace in the Redis storage
     *
     * @return string
     */
    public function getNamespace()
    {
      return $this->namespace;
    }

    /**
     * Return ID-s list key
     *
     * @return string
     */
    public function getIdsKey()
    {
      return "{$this->namespace}_ids";
    }

    /**
     * @return string
     */
    public function getNextIdKey()
    {
      return "{$this->namespace}_next_id";
    }

    /**
     * @return string
     */
    public function getCountKey()
    {
      return "{$this->namespace}_count";
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
  }