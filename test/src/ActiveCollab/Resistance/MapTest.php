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

      $this->assertTrue(in_array('rst:map_tests:map:map_field:Value 1', $keyspace));
      $this->assertTrue(in_array('rst:map_tests:map:map_field:Value 2', $keyspace));

      // ---------------------------------------------------
      //  Update value
      // ---------------------------------------------------

      $this->storage->update($id3, [ 'map_field' => 'Value 1' ]);

      $this->assertEquals([ 1, 2, 3 ], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 2'));

      $keyspace = $this->storage->getKeyspace();

      $this->assertTrue(in_array('rst:map_tests:map:map_field:Value 1', $keyspace));
      $this->assertFalse(in_array('rst:map_tests:map:map_field:Value 2', $keyspace));

      // ---------------------------------------------------
      //  Removal
      // ---------------------------------------------------

      $this->storage->delete($id1);
      $this->storage->delete($id2);
      $this->storage->delete($id3);

      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 1'));
      $this->assertEquals([], $this->storage->getIdsBy('map_field', 'Value 2'));

      $keyspace = $this->storage->getKeyspace();

      $this->assertFalse(in_array('rst:map_tests:map:map_field:Value 1', $keyspace));
      $this->assertFalse(in_array('rst:map_tests:map:map_field:Value 2', $keyspace));
    }
  }