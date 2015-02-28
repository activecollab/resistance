<?php
  namespace ActiveCollab;

  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;
  use ActiveCollab\Resistance\Error\Error;

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
     * @var int
     */
    private static $selected_database = 0;

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
        self::$selected_database = $select_database;
      }

      self::$namespace = $namespace;
    }

    /**
     * Return # of selected Redis database
     */
    public static function getSelectedDatabase()
    {
      return self::$selected_database;
    }

    /**
     * Return server info
     *
     * @return array
     */
    public static function getServerInfo()
    {
      return self::$connection->info();
    }

    /**
     * Create and store storage instances
     *
     * @param  string $class
     * @return Storage
     * @throws Error
     */
    public static function &factory($class)
    {
      if (empty(self::$factory_products[$class])) {
        $product = new $class;

        if ($product instanceof Storage) {
          $product->setConnection(self::$connection, self::$namespace);
          self::$factory_products[$class] = $product;
        } else {
          throw new Error("Class '$class' is not a valid Storage subclass");
        }
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