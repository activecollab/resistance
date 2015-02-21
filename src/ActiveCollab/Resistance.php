<?php
  namespace ActiveCollab;

  use Predis\Client;

  ini_set('error_report', E_ALL);
  ini_set('display_errors', 1);

  /**
   * @package ActiveCollab
   */
  final class Resistance
  {
    const VERSION = '1.0.0';

    /**
     * @var Client
     */
    private static $connection;

    /**
     * @var string
     */
    private static $namespace;

    /**
     * @var array
     */
    private static $factory_products = [];

    /**
     * @param string     $namespace
     * @param integer    $select_database
     * @param array|null $connection_params
     * @param array|null $connection_options
     */
    public static function connect($namespace, $select_database = 0, $connection_params = null, $connection_options = null)
    {
      self::$connection = new Client($connection_params, $connection_options);

      if ($select_database > 0) {
        self::$connection->select($select_database);
      }

      self::$namespace = $namespace;
    }

    /**
     * Create and store storage instances
     *
     * @param  string $class
     * @return object
     */
    public static function &factory($class)
    {
      if (empty(self::$factory_products[$class])) {
        self::$factory_products[$class] = new $class(self::$connection, self::$namespace);
      }

      return self::$factory_products[$class];
    }

    /**
     * Hard reset (clear database and drop all storage instances)
     */
    public static function reset()
    {
      self::$connection->flushdb();
      self::$factory_products = [];
    }
  }