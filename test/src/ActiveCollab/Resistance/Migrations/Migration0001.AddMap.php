<?php
  namespace ActiveCollab\Resistance\Test\Migrations;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;
  use ActiveCollab\Resistance\Test\Storage\AddingMigrationTests;

  /**
   * @package ActiveCollab\Resistance\Test\Migrations
   */
  class Migration0001 extends Migration
  {
    /**
     * Migrate up
     */
    public function up()
    {
      /** @var AddingMigrationTests $adding_storage */
      $adding_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\AddingMigrationTests');

      $adding_storage->buildValueMap('not_yet_a_map');
    }
  }