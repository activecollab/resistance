<?php
  namespace ActiveCollab\Resistance\Storage;

  use Predis\Client;
  use Predis\Collection\Iterator\Keyspace;
  use Doctrine\Common\Inflector\Inflector;

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
    protected $namespace;

    /**
     * Construct a new storage instance
     *
     * @param Client $connection
     * @param string $application_namespace
     */
    public function setConnection(Client &$connection, $application_namespace)
    {
      $this->connection = $connection;
      $this->namespace = $application_namespace;

      if ($this->namespace) {
        $this->namespace .= ':';
      }

      $this->namespace .= $this->getStorageNamespace();
    }

    /**
     * Return storage namespace
     *
     * @return string
     */
    protected function getStorageNamespace()
    {
      $class_name_bits = explode('\\', get_class($this));

      return Inflector::tableize(array_pop($class_name_bits));
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
      $result = [];

      foreach (new Keyspace($this->connection, "$this->namespace:*") as $key) {
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