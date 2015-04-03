<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;

  require_once __DIR__ . '/Storage/AddingMigrationTests.php';
  require_once __DIR__ . '/Storage/RemovingMigrationTests.php';

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
     * @var \ActiveCollab\Resistance\Test\Storage\RemovingMigrationTests
     */
    private $removing_storage;

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

      $this->removing_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\RemovingMigrationTests');
      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\RemovingMigrationTests', $this->removing_storage);

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
      $this->migrate();

      foreach ($this->migrations as $migration) {
        $this->assertTrue(Resistance::isMigrationExecuted($migration));
      }
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionBecauseTheresNotYetAMapField()
    {
      $this->adding_storage->insert(
        [ 'not_yet_a_map' => 'Value #1', 'not_yet_unique' => 'Unique #1' ],
        [ 'not_yet_a_map' => 'Value #2', 'not_yet_unique' => 'Unique #2' ],
        [ 'not_yet_a_map' => 'Value #3', 'not_yet_unique' => 'Unique #3' ]
      );

      $this->adding_storage->getIdsBy('not_yet_a_map', 'Value #1');
    }

    /**
     * Test if map is properly added after migration
     */
    public function testNoNotYetAMapExceptionAfterMigration()
    {
      $this->adding_storage->insert(
        [ 'not_yet_a_map' => 'Value #1', 'not_yet_unique' => 'Unique #1' ],
        [ 'not_yet_a_map' => 'Value #2', 'not_yet_unique' => 'Unique #2' ],
        [ 'not_yet_a_map' => 'Value #3', 'not_yet_unique' => 'Unique #3' ]
      );

      $keyspace = $this->adding_storage->getKeyspace();

      $this->migrate();

      $this->assertEquals([ 1 ], $this->adding_storage->getIdsBy('not_yet_a_map', 'Value #1'));
      $this->assertCount(count($keyspace) + 4, $this->adding_storage->getKeyspace(), 'Four new keys - three mapped values and one uniquness key');
    }

    /**
     * Test remove map from a field
     */
    public function testRemoveMap()
    {
      $this->removing_storage->insert(
        [ 'mapped_field' => 'Value #1', 'unique_field' => 'Unique #1' ],
        [ 'mapped_field' => 'Value #1', 'unique_field' => 'Unique #2' ],
        [ 'mapped_field' => 'Value #2', 'unique_field' => 'Unique #3' ]
      );

      $keyspace = $this->removing_storage->getKeyspace();

      $this->assertTrue($this->removing_storage->isMapped('mapped_field'));

      $this->migrate();

      $this->assertFalse($this->removing_storage->isMapped('mapped_field'));

      $this->assertCount(count($keyspace) - 3, $this->removing_storage->getKeyspace(), 'Three keys less, two mapped values and one unique key');
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testBuildUniquenessMapExceptionWhenThereAreNonUniqueValues()
    {
      $this->adding_storage->insert(
        [ 'not_yet_a_map' => 'Value #1', 'not_yet_unique' => 'Unique #1' ],
        [ 'not_yet_a_map' => 'Value #2', 'not_yet_unique' => 'Unique #2' ],
        [ 'not_yet_a_map' => 'Value #3', 'not_yet_unique' => 'Unique #1' ]
      );

      $this->adding_storage->buildUniquenessMap('not_yet_unique');
    }

    /**
     * Test if uniqueness map is properly set
     */
    public function testBuildUniquenessMap()
    {
      $this->adding_storage->insert(
        [ 'not_yet_a_map' => 'Value #1', 'not_yet_unique' => 'Unique #1' ],
        [ 'not_yet_a_map' => 'Value #2', 'not_yet_unique' => 'Unique #2' ],
        [ 'not_yet_a_map' => 'Value #3', 'not_yet_unique' => 'Unique #3' ]
      );

      $keyspace = $this->adding_storage->getKeyspace();
      $this->assertFalse($this->adding_storage->isUnique('not_yet_unique'));

      $this->adding_storage->buildUniquenessMap('not_yet_unique');

      $this->assertCount(count($keyspace) + 1, $this->adding_storage->getKeyspace(), 'New three keys for three values');
      $this->assertTrue($this->adding_storage->isUnique('not_yet_unique'));
    }

    /**
     * Test and make sure that uniquness map is removed
     */
    public function testRemoveUniqunessMap()
    {
      $this->removing_storage->insert(
        [ 'mapped_field' => 'Value #1', 'unique_field' => 'Unique #1' ],
        [ 'mapped_field' => 'Value #1', 'unique_field' => 'Unique #2' ],
        [ 'mapped_field' => 'Value #2', 'unique_field' => 'Unique #3' ]
      );

      $keyspace = $this->removing_storage->getKeyspace();

      $this->assertTrue($this->removing_storage->isUnique('unique_field'));

      $this->migrate();

      $this->assertFalse($this->removing_storage->isUnique('unique_field'));

      $this->assertCount(count($keyspace) - 3, $this->removing_storage->getKeyspace(), 'Three keys less, two mapped values and one unique key');
    }

    /**
     * Migrate up
     */
    private function migrate()
    {
      $this->assertSame(4, Resistance::migrate(__DIR__ . '/Migrations', 'ActiveCollab\Resistance\Test\Migrations'));
    }
  }