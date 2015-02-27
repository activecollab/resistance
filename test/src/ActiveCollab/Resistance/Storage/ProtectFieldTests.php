<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Field\BooleanField;
  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class ProtectFieldTests extends Storage
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
        'dont_allow_value_set_on_insert' => (new BooleanField)->protect(),
        'not_protected'                  =>  new BooleanField,
      ]);
    }
  }