<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Test\Storage\Accounts;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class AccountsTest extends TestCase
  {
    /**
     * @var Accounts
     */
    private $accounts;

    /**
     * Set up test environment
     */
    public function setUp()
    {
      parent::setUp();

      $this->accounts = Resistance::factory('\ActiveCollab\Resistance\Test\Storage\Accounts');
    }

    /**
     * Test if factory returns proper instance
     */
    public function testFactory()
    {
      $this->assertInstanceOf('\ActiveCollab\Resistance\Test\Storage\Accounts', $this->accounts);
    }

    /**
     * Test namespace
     */
    public function testNamespace()
    {
      $this->assertEquals('rst:accounts', $this->accounts->getNamespace());
    }

    /**
     * Test default values
     */
    public function testDefaults()
    {
      $id = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://afiveone.manageprojects.com',
      ])[0];

      $this->assertEquals(1, $id);

      $record = $this->accounts->get($id);

      $this->assertTrue(is_array($record));

      $this->assertEquals(1, $record['_id']);
      $this->assertEquals('123', $record['license_key']);
      $this->assertEquals('afiveone', $record['subdomain']);
      $this->assertEquals('https://afiveone.manageprojects.com', $record['url']);
      $this->assertEquals('UTC', $record['timezone']);
      $this->assertEquals(false, $record['is_paid']);
    }
  }