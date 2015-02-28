<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance\Storage\Field\BooleanField;
  use ActiveCollab\Resistance\Storage\Collection;

  /**
   * @package ActiveCollab\Resistance\Test\Storage
   */
  class BooleanRequiredTests extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'makes_no_sense' => (new BooleanField)->unique()
      ]);
    }
  }