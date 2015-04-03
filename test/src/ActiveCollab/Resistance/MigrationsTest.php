<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;

  require_once __DIR__ . '/Storage/AddingMigrationTests.php';

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class MigrationsTest extends TestCase
  {
    /**
     * @var \ActiveCollab\Resistance\Test\Storage\AddingMigrationTests
     */
    private $adding_storage;

    /**
     * @var Migration[]
     */
    private $migrations;

    /**
     * Set up and test factory
     */
    public function setUp()
    {
      parent::setUp();

      $this->adding_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\AddingMigrationTests');
      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\AddingMigrationTests', $this->adding_storage);

      $this->migrations = Resistance::discoverMigrations(__DIR__ . '/Migrations', 'ActiveCollab\Resistance\Test\Migrations');
    }

    /**
     * Test discover migrations in a folder
     */
    public function testDiscover()
    {
      $this->assertCount(4, $this->migrations);

      $this->assertInstanceOf('ActiveCollab\Resistance\Test\Migrations\Migration0001', $this->migrations[0]);
      $this->assertInstanceOf('ActiveCollab\Resistance\Test\Migrations\Migration0002', $this->migrations[1]);
      $this->assertInstanceOf('ActiveCollab\Resistance\Test\Migrations\Migration0003', $this->migrations[2]);
      $this->assertInstanceOf('ActiveCollab\Resistance\Test\Migrations\Migration0004', $this->migrations[3]);
    }

    /**
     * Make sure that no migrations are marked as executed on initial load
     */
    public function testNoMigrationsExecutedByDefault()
    {
      foreach ($this->migrations as $migration) {
        $this->assertFalse(Resistance::isMigrationExecuted($migration));
      }
    }

    /**
     * Test if all transactions are marked as executed on migrate up
     *
     * @throws Resistance\Error\Error
     */
    public function testEverythingExecutedAfterMigrateUp()
    {
      $this->assertSame(4, Resistance::migrate(__DIR__ . '/Migrations', 'ActiveCollab\Resistance\Test\Migrations'));

      foreach ($this->migrations as $migration) {
        $this->assertTrue(Resistance::isMigrationExecuted($migration));
      }
    }
  }