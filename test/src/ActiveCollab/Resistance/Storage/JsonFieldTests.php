<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Collection;
  use ActiveCollab\Resistance\Storage\Field\JsonField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class JsonFieldTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([ 'json' => new JsonField ]);
    }
  }