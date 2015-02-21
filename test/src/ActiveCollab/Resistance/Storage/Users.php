<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage, ActiveCollab\Resistance\Storage\Field\IntegerField, ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  class Users extends Storage
  {
    /**
     * Construct a new storage instance
     *
     * @param Client $connection
     * @param string $application_namespace
     */
    public function __construct(Client &$connection, $application_namespace)
    {
      parent::__construct($connection, $application_namespace);

      $this->setFields([
        'acid'  => (new IntegerField)->required(),
        'email' => (new StringField)->isEmail(),
        'role'  => (new StringField)->required(),
      ]);
    }
  }