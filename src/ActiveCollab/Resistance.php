<?php
  namespace ActiveCollab;

  use ActiveCollab\Resistance\Storage\Migration;
  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;
  use ActiveCollab\Resistance\Error\Error;
  use DirectoryIterator, ReflectionClass;

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

    // ---------------------------------------------------
    //  Migrations
    // ---------------------------------------------------

    /**
     * Discover and execute all migrations
     *
     * @param  string  $path
     * @param  string  $namespace
     * @return integer
     * @throws Error
     */
    public static function migrate($path, $namespace = '')
    {
      $executed_migrations = 0;

      foreach (self::discoverMigrations($path, $namespace) as $migration) {
        if (!self::isMigrationExecuted($migration)) {
          self::$connection->transaction(function ($t) use (&$migration) {
            $migration->up($t); // Execute migration

            self::$connection->sadd(self::getExecutedMigrationsSetKey(), get_class($migration)); // Remember that this migration is executed
          });

          $executed_migrations++;
        }
      }

      return $executed_migrations;
    }

    /**
     * Load migrations from the given path
     *
     * @param  string      $path
     * @param  string      $namespace
     * @return Migration[]
     */
    public static function discoverMigrations($path, $namespace = '')
    {
      $result = [];

      foreach (new DirectoryIterator($path) as $file) {
        if ($file->isFile()) {
          if ($migration_class_name = self::getMigrationClassName($file->getBasename())) {
            if ($namespace) {
              $migration_class_name = "$namespace\\$migration_class_name";
            }

            require_once $file->getPathname();

            $reflection = new ReflectionClass($migration_class_name);

            if ($reflection->isSubclassOf('ActiveCollab\\Resistance\\Storage\\Migration') && !$reflection->isAbstract()) {
              $result[] = new $migration_class_name;
            }
          }
        }
      }

      return $result;
    }

    /**
     * Return true if $migration has been executed
     *
     * @param  Migration $migration
     * @return bool
     * @throws Error
     */
    public static function isMigrationExecuted(Migration $migration)
    {
      if (self::$connection) {
        return self::$connection->sismember(self::getExecutedMigrationsSetKey(), get_class($migration));
      } else {
        throw new Error('Not connected to Redis database');
      }
    }

    /**
     * Return name of the which we'll store
     *
     * @return string
     */
    private static function getExecutedMigrationsSetKey()
    {
      return self::$namespace . '::_xrm'; // Executed Resistance Migrations
    }

    /**
     * Validate migration file basename and get migration class from it
     *
     * @param  string $basename
     * @return bool
     */
    private static function getMigrationClassName($basename)
    {
      if ($basename) {
        $bits = explode('.', $basename);

        switch (count($bits)) {
          case 2:
            return preg_match('/^Migration[0-9]{4}$/', $bits[0]) && $bits[1] === 'php' ? $bits[0] : false;
          case 3:
            return preg_match('/^Migration[0-9]{4}$/', $bits[0]) && $bits[2] === 'php' ? $bits[0] : false;
        }
      }

      return false;
    }
  }