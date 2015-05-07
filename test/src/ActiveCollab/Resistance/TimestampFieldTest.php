<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Storage\Collection;

  require __DIR__ . '/Storage/TimestampFromStringTests.php';

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class TimestampFieldTest extends TestCase
  {
    /**
     * Test set timestamp from string
     */
    public function testSetAsString()
    {
      /** @var Collection $storage */
      $storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\TimestampFromStringTests');

      $record_id = $storage->insert([ 'timestamp' => '2015-05-07' ])[0];

      $this->assertEquals(1, $record_id);
      $this->assertEquals(1430956800, $storage->getFieldValue($record_id, 'timestamp'));
    }
  }