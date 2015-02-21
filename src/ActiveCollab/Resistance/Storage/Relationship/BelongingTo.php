<?php
  namespace ActiveCollab\Resistance\Storage\Relationship;

  use Predis\Client;

  /**
   * @package ActiveCollab\Resistance\Storage\Relationship
   */
  final class BelongingTo extends Relationship
  {
    /**
     * @var Client
     */
    private $connection;

    /**
     * @var string
     */
    private $key;

    /**
     * @param Client $connection
     * @param string $context_key
     * @param string $relationship_name
     */
    public function __construct(Client &$connection, $context_key, $relationship_name)
    {
      $this->connection = $connection;
      $this->key = $context_key . ':' . $relationship_name;
    }

    /**
     * Return ID-s of all related records
     *
     * @return array
     */
    public function get()
    {
      return $this->connection->smembers($this->key);
    }

    /**
     * Return true if $related_object_id is related to this context
     *
     * @param  integer $related_object_id
     * @return boolean
     */
    public function has($related_object_id)
    {
      return (boolean) $this->connection->sismember($this->key, $related_object_id);
    }

    /**
     * Return number of related records
     *
     * @return integer
     */
    public function count()
    {
      return $this->connection->scard($this->key);
    }

    /**
     * Add related records
     */
    public function add()
    {
      if (func_num_args()) {
        $this->connection->sadd($this->key, func_get_args());

        if ($this->on_change) {
          call_user_func($this->on_change, $this->get());
        }

        if ($this->on_add) {
          call_user_func($this->on_add, $this->get());
        }
      }
    }

    /**
     * @param $related_object_id
     */
    public function remove($related_object_id)
    {
      $this->connection->srem($this->key, $related_object_id);

      if ($this->on_change) {
        call_user_func($this->on_change, $this->get());
      }

      if ($this->on_remove) {
        call_user_func($this->on_remove, $this->get());
      }
    }

    /**
     * Clear all related records
     */
    public function clear()
    {
      $this->connection->del($this->key);

      if ($this->on_change) {
        call_user_func($this->on_change, []);
      }

      if ($this->on_clear) {
        call_user_func($this->on_clear, []);
      }
    }

    // ---------------------------------------------------
    //  Callbacks
    // ---------------------------------------------------

    /**
     * @var callable
     */
    private $on_change, $on_add, $on_remove, $on_clear;

    /**
     * @param  callable $callback
     * @return $this
     */
    public function &onChange(callable $callback)
    {
      if (is_callable($callback)) {
        $this->on_change = $callback;
      }

      return $this;
    }

    /**
     * @param  callable $callback
     * @return $this
     */
    public function &onAdd(callable $callback)
    {
      if (is_callable($callback)) {
        $this->on_add = $callback;
      }

      return $this;
    }

    /**
     * @param  callable $callback
     * @return $this
     */
    public function &onRemove(callable $callback)
    {
      if (is_callable($callback)) {
        $this->on_remove = $callback;
      }

      return $this;
    }

    /**
     * @param  callable $callback
     * @return $this
     */
    public function &onClear(callable $callback)
    {
      if (is_callable($callback)) {
        $this->on_clear = $callback;
      }

      return $this;
    }
  }