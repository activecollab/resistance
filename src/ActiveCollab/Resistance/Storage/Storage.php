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
  }