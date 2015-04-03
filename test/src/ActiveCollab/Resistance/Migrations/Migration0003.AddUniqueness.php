<?php
  namespace ActiveCollab\Resistance\Test\Migrations;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Migration;
  use ActiveCollab\Resistance\Test\Storage\AddingMigrationTests;

  class Migration0003 extends Migration
  {
    /**
     * Migrate up
     */
    public function up()
    {
      /** @var AddingMigrationTests $adding_storage */
      $adding_storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\AddingMigrationTests');

      $adding_storage->buildUniquenessMap('not_yet_unique');
    }
  }