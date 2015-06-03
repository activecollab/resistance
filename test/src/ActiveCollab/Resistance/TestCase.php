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
      Resistance::connectToCluster([ '127.0.0.1:30001', '127.0.0.1:30002', '127.0.0.1:30003' ]);
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