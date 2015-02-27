<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  require_once __DIR__ . '/Storage/ProtectFieldTests.php';

  class ProtectFieldTest extends TestCase
  {
    /**
     * @var \ActiveCollab\Resistance\Test\Storage\ProtectFieldTests
     */
    private $storage;

    /**
     * Set up test environment
     */
    public function setUp()
    {
      parent::setUp();

      $this->storage = Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\ProtectFieldTests');
      $this->assertInstanceOf('\\ActiveCollab\\Resistance\\Test\\Storage\\ProtectFieldTests', $this->storage);
    }

    /**
     * Test isProtected() call
     */
    public function testIsProtected()
    {
      $this->assertTrue($this->storage->isProtected('dont_allow_value_set_on_insert'));
      $this->assertFalse($this->storage->isProtected('not_protected'));
    }

    /**
     * Test no error on regular insert
     */
    public function testNoExceptionOnNoValue()
    {
      $this->assertEquals(1, $this->storage->insert([])[0]);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionWhenWeSetValueOfProtectedField()
    {
      $this->storage->insert([ 'dont_allow_value_set_on_insert' => 123 ]);
    }
  }