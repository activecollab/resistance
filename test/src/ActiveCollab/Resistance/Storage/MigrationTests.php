<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Field\BooleanField;
  use ActiveCollab\Resistance\Storage\Collection;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class MigrationTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'dont_allow_value_set_on_insert' => (new BooleanField)->protect(),
        'not_protected'                  =>  new BooleanField,
      ]);
    }
  }