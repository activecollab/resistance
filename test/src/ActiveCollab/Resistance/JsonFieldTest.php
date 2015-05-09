<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  require __DIR__ . '/Storage/JsonFieldTests.php';

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class ExampleJsonSerializableImplementation implements \JsonSerializable {

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
      $this->data = $data;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
      return $this->data;
    }
  }

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class JsonFieldTest extends TestCase
  {
    /**
     * @var \ActiveCollab\Resistance\Test\Storage\JsonFieldTests $storage
     */
    private $storage;

    public function setUp()
    {
      parent::setUp();

      $this->storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\JsonFieldTests');

      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\JsonFieldTests', $this->storage);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testErrorOnUnsupportedValue()
    {
      $this->storage->insert([ 'json' => 123 ]);
    }

    /**
     * Test array in, array out
     */
    public function testArrayInArrayOut()
    {
      $to_serailize = [
        'int' => 12,
        'str' => "It's time",
        'float' => 1.25
      ];

      $id = $this->storage->insert([ 'json' => $to_serailize ])[0];

      $this->assertEquals(1, $id);

      $this->assertEquals($to_serailize, $this->storage->getFieldValue($id, 'json'));
    }

    /**
     * Test JsonSeriazable in, array out
     */
    public function testJsonSerializableInArrayOut()
    {
      $to_serailize = [
        'int' => 12,
        'str' => "It's time",
        'float' => 1.25
      ];

      $id = $this->storage->insert([ 'json' => new ExampleJsonSerializableImplementation($to_serailize) ])[0];

      $this->assertEquals(1, $id);

      $this->assertEquals($to_serailize, $this->storage->getFieldValue($id, 'json'));
    }
  }