<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Field\TimestampField;
  use ActiveCollab\Resistance\Storage\Collection;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class TimestampFromStringTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([ 'timestamp' => new TimestampField ]);
    }
  }