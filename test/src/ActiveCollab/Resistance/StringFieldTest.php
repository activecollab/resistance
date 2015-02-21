<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;
  use ActiveCollab\Resistance\Test\Storage\Accounts;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class StringFieldTest extends TestCase
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
     * Test string modifier
     */
    public function testStringModifier()
    {
      $id = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => '     afiveone ',
        'url' => 'https://www.activecollab.com',
      ])[0];

      $this->assertEquals('afiveone', $this->accounts->get($id)['subdomain']);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testValidateFormatException()
    {
      throw new Resistance\Error\ValidationError; // @TODO
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testValidateUrlException()
    {
      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'Merry Christmas',
      ]);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testValidateEmailException()
    {
      throw new Resistance\Error\ValidationError; // @TODO
    }
  }