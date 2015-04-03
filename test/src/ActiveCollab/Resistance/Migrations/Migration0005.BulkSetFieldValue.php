<?php
  namespace ActiveCollab\Resistance\Test\Migrations;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;
  use ActiveCollab\Resistance\Test\Storage\AddingMigrationTests;

  /**
   * @package ActiveCollab\Resistance\Test\Migrations
   */
  class Migration0005 extends Migration
  {
    /**
     * Migrate up
     */
    public function up()
    {
      /** @var AddingMigrationTests $adding_storage */
      $adding_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\AddingMigrationTests');

      $counter = 1;

      $adding_storage->bulkSetFieldValue('bulk_set_values', function() use (&$counter) {
        return $counter++;
      });
    }
  }