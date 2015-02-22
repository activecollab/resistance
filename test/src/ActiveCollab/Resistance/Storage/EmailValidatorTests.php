<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Field\StringField;
  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;

  class EmailValidatorTests extends Storage
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
        'email' => (new StringField)->required()->isEmail(),
      ]);
    }
  }
