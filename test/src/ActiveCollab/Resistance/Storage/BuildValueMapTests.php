<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Collection;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class BuildValueMapTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([ 'map_field' => new StringField ]);
    }
  }