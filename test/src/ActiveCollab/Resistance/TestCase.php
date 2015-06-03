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
      if (getenv('TEST_REDIS_CLUSTER')) {
        print "Resistance: Connecting to Redis Cluster...\n";
        Resistance::connectToCluster([ '127.0.0.1:30001', '127.0.0.1:30002', '127.0.0.1:30003', '127.0.0.1:30004', '127.0.0.1:30005' ]);
      } else {
        print "Resistance: Connecting to Standalone Redis...\n";
        Resistance::connect();
      }

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