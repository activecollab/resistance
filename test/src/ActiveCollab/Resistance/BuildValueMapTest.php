<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  require_once __DIR__ . '/Storage/BuildValueMapTests.php';

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class BuildValueMapTest extends TestCase
  {
    /**
     * @var \ActiveCollab\Resistance\Test\Storage\BuildValueMapTests
     */
    private $storage;

    /**
     * Set up and test factory
     */
    public function setUp()
    {
      parent::setUp();

      $this->storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\BuildValueMapTests');
      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\BuildValueMapTests', $this->storage);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testFieldNotMapped()
    {
      $this->storage->insert([ 'map_field' => 'Value #1' ]);
      $this->storage->getIdsBy('map_field', 'Value #1');
    }

    public function testBuildValueMap()
    {
      $this->storage->insert(
        [ 'map_field' => 'Value #1' ],
        [ 'map_field' => 'Value #2' ],
        [ 'map_field' => 'Value #3' ],
        [ 'map_field' => 'Value #1' ],
        [ 'map_field' => 'Value #3' ]
      );

      $this->assertEquals(5, $this->storage->count());

      $this->storage->buildValueMap('map_field');

      $this->assertEquals([ 1, 4 ], $this->storage->getIdsBy('map_field', 'Value #1'));
      $this->assertEquals([ 2 ], $this->storage->getIdsBy('map_field', 'Value #2'));
      $this->assertEquals([ 3, 5 ], $this->storage->getIdsBy('map_field', 'Value #3'));
    }
  }