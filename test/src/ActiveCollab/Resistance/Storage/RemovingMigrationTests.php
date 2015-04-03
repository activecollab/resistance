<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Collection;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class RemovingMigrationTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'mapped_field' => (new StringField)->map(),
        'unique_field' => (new StringField)->unique(),
      ]);
    }
  }