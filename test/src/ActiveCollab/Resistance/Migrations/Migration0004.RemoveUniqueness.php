<?php
  namespace ActiveCollab\Resistance\Test\Migrations;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;
  use ActiveCollab\Resistance\Test\Storage\RemovingMigrationTests;

  class Migration0004 extends Migration
  {
    /**
     * Migrate up
     */
    public function up()
    {
      /** @var RemovingMigrationTests $adding_storage */
      $adding_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\RemovingMigrationTests');

      $adding_storage->removeUniquenessMap('unique_field');
    }
  }