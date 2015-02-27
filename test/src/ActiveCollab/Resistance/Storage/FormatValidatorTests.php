<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class FormatValidatorTests extends Storage
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
        'hash' => (new StringField)->required()->format(StringField::FORMAT_HASH),
      ]);
    }
  }
