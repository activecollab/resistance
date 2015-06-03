<?php
  namespace ActiveCollab\Resistance\Storage;

  use Doctrine\Common\Inflector\Inflector;
  use Redis;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  abstract class Storage
  {
    /**
     * @var Redis|RedisCluster
     */
    protected $connection;

    /**
     * @var string
     */
    protected $namespace;

//    /**
//     * Construct a new storage instance
//     *
//     * @param Redis|RedisCluster $connection
//     * @param string             $application_namespace
//     */
//    public function __construct($connection, $application_namespace)
//    {
//      $this->connection = $connection;
//      $this->namespace = $application_namespace;
//
//      if ($this->namespace) {
//        $this->namespace .= ':';
//      }
//
//      $this->namespace .= $this->getStorageNamespace();
//    }

    /**
     * Construct a new storage instance
     *
     * @param Redis|RedisCluster $connection
     * @param string             $application_namespace
     */
    public function setConnection(&$connection, $application_namespace)
    {
      $this->connection = $connection;
      $this->namespace = $application_namespace;

      if ($this->namespace) {
        $this->namespace .= ':';
      }

      $this->namespace .= $this->getStorageNamespace();
    }

    /**
     * @param callable $callback
     */
    protected function transaction(callable $callback)
    {
      call_user_func($callback, $this->connection);
    }

    /**
     * Return storage namespace
     *
     * @return string
     */
    protected function getStorageNamespace()
    {
      $class_name_bits = explode('\\', get_class($this));

      return '{' . Inflector::tableize(array_pop($class_name_bits)) . '}';
    }

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
     * Return keyspace
     *
     * @return array
     */
    public function getKeyspace()
    {
      return $this->getKeysByPattern("$this->namespace:*");
    }

    /**
     * Return keys by pattern
     *
     * @todo Make it use SCAN instead of KEYS
     *
     * @param  string $pattern
     * @return array
     */
    protected function getKeysByPattern($pattern)
    {
      $result = [];

      foreach ($this->connection->keys($pattern) as $key) {
        $result[] = $key;
      }

      return $result;
    }

    /**
     * Clear the storage
     */
    public function clear()
    {
      foreach ($this->getKeyspace() as $key) {
        $this->connection->del($key);
      }
    }
  }