<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  class BooleanFieldTest extends TestCase
  {
    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testBooleanRequiredFailure()
    {
      require __DIR__ . '/Storage/BooleanRequiredTests.php';

      Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\BooleanRequiredTests');
    }
  }