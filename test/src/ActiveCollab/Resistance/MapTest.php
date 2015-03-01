<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  require_once __DIR__ . '/Storage/MapTests.php';

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class MapTest extends TestCase
  {
    /**
     * @var \ActiveCollab\Resistance\Test\Storage\MapTests
     */
    private $storage;

    /**
     * Set up and test factory
     */
    public function setUp()
    {
      parent::setUp();

      $this->storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\MapTests');
      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\MapTests', $this->storage);
    }

    /**
     * Test isMapped() call
     */
    public function testIsMapped()
    {
      $this->assertTrue($this->storage->isMapped('map_field'));
      $this->assertFalse($this->storage->isMapped('not_map_field'));
    }

    /**
     * Test to make sure that Resistance allows mapped field not to be required
     */
    public function testMappedFieldDoesNotNeedToBeRequired()
    {
      $this->assertFalse($this->storage->isRequired('map_field'));
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionWhenCallingGetIdsByOnFieldThatIsNotMapped()
    {
      $this->storage->getIdsBy('not_map_field', 'some value');
    }

    /**
     * Test Storage::getIdsBy() functionality
     */
    public function testGetIdsBy()
    {
      list ($id1, $id2, $id3, $id4, $id5) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ],
        [ 'map_field' => null ],
        [ 'map_field' => null ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);
      $this->assertEquals(4, $id4);
      $this->assertEquals(5, $id5);

      $this->assertEquals([ 1, 2 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 3'));
      $this->assertEquals([ 4, 5 ], $this->storage->getIdsBy('map_field', null));
    }

    /**
     * Test if maps are case-insensitive
     */
    public function testCaseInsensitiveMapping()
    {
      list ($id1, $id2, $id3) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'VALUE 1' ],
        [ 'map_field' => 'valuE 1' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->assertEquals([ 1, 2, 3 ], $this->storage->getIdsBy('map_field', 'value 1'));
    }

    /**
     * Test if 0 is returned when we try to get ID for a value that does not exist
     */
    public function testGetIdByWhenRecordIsNotFound()
    {
      $this->assertSame(0, $this->storage->getIdBy('map_field', 'some value'));
    }

    /**
     * Test if we get the proper ID for a given value
     */
    public function testGetIdByWhenRecordIsFound()
    {
      list ($id1, $id2) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);

      $this->assertEquals(1, $this->storage->getIdBy('map_field', 'Value 1'));
      $this->assertEquals(2, $this->storage->getIdBy('map_field', 'Value 2'));
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testGetIdByWhenThereAreMultipleRecords()
    {
      list ($id1, $id2, $id3) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->storage->getIdBy('map_field', 'Value 1');
    }

    /**
     * Test if maps are updated when record is updated
     */
    public function testGetByIdsChangesOnItemUpdate()
    {
      list ($id1, $id2, $id3) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->assertEquals([ 1, 2 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $this->storage->update($id1, [ 'map_field' => 'Value 2' ]);

      $this->assertEquals([ 2 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 1, 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $this->storage->update($id2, [ 'map_field' => 'Value 2' ]);

      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 1, 2, 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));
    }

    /**
     * Test that maps are updated when item is removed
     */
    public function testGetByIdsChangesOnItemRemoval()
    {
      list ($id1, $id2, $id3) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->assertEquals([ 1, 2 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $this->storage->delete($id2);

      $this->assertEquals([ 1 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $this->storage->delete($id1);

      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $this->storage->delete($id3);

      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 2'));
    }

    /**
     * Test keyspace changes when elements are added, updated and removed
     */
    public function testKeyspace()
    {
      list ($id1, $id2, $id3) = $this->storage->insert(
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 1' ],
        [ 'map_field' => 'Value 2' ]
      );

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->assertEquals([ 1, 2 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([ 3 ], $this->storage->getIdsBy('map_field', 'Value 2'));

      $keyspace = $this->storage->getKeyspace();

      $this->assertTrue(in_array('rst:map_tests:map:map_field:value 1', $keyspace));
      $this->assertTrue(in_array('rst:map_tests:map:map_field:value 2', $keyspace));

      // ---------------------------------------------------
      //  Update value
      // ---------------------------------------------------

      $this->storage->update($id3, [ 'map_field' => 'Value 1' ]);

      $this->assertEquals([ 1, 2, 3 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 2'));

      $keyspace = $this->storage->getKeyspace();

      $this->assertTrue(in_array('rst:map_tests:map:map_field:value 1', $keyspace));
      $this->assertFalse(in_array('rst:map_tests:map:map_field:value 2', $keyspace));

      // ---------------------------------------------------
      //  Removal
      // ---------------------------------------------------

      $this->storage->delete($id1);
      $this->storage->delete($id2);
      $this->storage->delete($id3);

      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 2'));

      $keyspace = $this->storage->getKeyspace();

      $this->assertFalse(in_array('rst:map_tests:map:map_field:value 1', $keyspace));
      $this->assertFalse(in_array('rst:map_tests:map:map_field:value 2', $keyspace));
    }

    /**
     * Test if we can accept string up to 1024 in length
     */
    public function testUpTo1024CharacatersPassing()
    {
      $this->assertEquals(1, $this->storage->insert([ 'map_field' => $this->make_string(1024) ])[0]);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testMoreThan1024CharacatersFailing()
    {
      $this->storage->insert([ 'map_field' => $this->make_string(1025) ]);
    }

    /**
     * Make random string
     *
     * @param integer $length
     * @param string $allowed_chars
     * @return string
     */
    private function make_string($length = 10, $allowed_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
    {
      $allowed_chars_len = strlen($allowed_chars);

      $result = '';

      for ($i = 0; $i < $length; $i++) {
        $result .= mb_substr($allowed_chars, rand(0, $allowed_chars_len), 1);
      }

      while (mb_strlen($result) < $length) {
        $result .= mb_substr($allowed_chars, rand(0, $allowed_chars_len), 1);
      }

      $this->assertEquals($length, mb_strlen($result));

      return $result;
    }
  }