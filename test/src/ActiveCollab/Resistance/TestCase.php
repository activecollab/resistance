<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  abstract class TestCase extends \PHPUnit_Framework_TestCase
  {
    /**
     * Switch to test database
     */
    public function setUp()
    {
      Resistance::connect('rst', 15);
      Resistance::reset();
    }

    /**
     * Tear down test database
     */
    public function tearDown()
    {
      Resistance::reset();
    }
  }