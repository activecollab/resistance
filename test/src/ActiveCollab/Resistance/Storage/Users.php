<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Collection;
  use ActiveCollab\Resistance\Storage\Field\IntegerField;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  class Users extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'acid'  => (new IntegerField)->required(),
        'email' => (new StringField)->isEmail(),
        'role'  => (new StringField)->required(),
      ]);
    }
  }