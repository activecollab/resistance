<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Collection;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class AddingMigrationTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'not_yet_a_map' => new StringField,
        'not_yet_unique' => new StringField,
      ]);
    }
  }