<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;
  use ActiveCollab\Resistance\Storage\Field\StringField;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class MapTests extends Storage
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
        'map_field'     => (new StringField)->map(),
        'not_map_field' =>  new StringField
      ]);
    }
  }