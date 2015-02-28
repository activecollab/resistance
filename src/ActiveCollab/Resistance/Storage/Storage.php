<?php
  namespace ActiveCollab\Resistance\Storage;

  use Predis\Client;
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

      $class_name_bits = explode('\\', get_class($this));

      $this->namespace .= Inflector::tableize(array_pop($class_name_bits));
    }
  }