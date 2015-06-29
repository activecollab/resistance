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
    public function testValidateEmailException()
    {
      require __DIR__ . '/Storage/EmailValidatorTests.php';

      Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\EmailValidatorTests')->insert([ 'email' => 'nije email' ]);
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
    public function testValidateFormatException()
    {
      require __DIR__ . '/Storage/FormatValidatorTests.php';

      Resistance::factory('\\ActiveCollab\\Resistance\\Test\\Storage\\FormatValidatorTests')->insert([ 'hash' => 'not a hash' ]);
    }

    /**
     * Test if proper exception is thrown when we try to add a record that already has a value that is marked to be unique
     *
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionOnUniqueInsertAttempt()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ]);
    }

    /**
     * Test if proper exception is thrown when we try to add a record that already has a value that is marked to be unique, case insensitive
     *
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionOnCaseInsensitiveUniqueInsertAttempt()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'aFiveOne',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.ACTIVRC.com',
      ]);
    }

    /**
     * Test exception when we try to update a unique field to value that is already used
     *
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionOnUniqueUpdateAttempt()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      list ($id1, $id2) = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '123',
        'subdomain' => 'feather',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);

      $this->accounts->update(2, [
        'subdomain' => 'afiveone',
      ]);
    }

    /**
     * Test exception when we try to update a unique field to value that is already used, case insensitive
     *
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testExceptionOnInsensitiveUniqueUpdateAttempt()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      list ($id1, $id2) = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'aFiveOne',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '123',
        'subdomain' => 'Feather',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);

      $this->accounts->update(2, [
        'subdomain' => 'afiveOne',
      ]);
    }

    /**
     * Test exception when we try to update a unique field to value that is already used, case insensitive
     *
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testIfExceptionIsThrownBecauseUniquenessMapIsUpdated()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      list ($id1, $id2) = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'aFiveOne',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '123',
        'subdomain' => 'Feather',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);

      $this->accounts->update(1, [ 'subdomain' => 'Endymion' ]); // Should update the map
      $this->accounts->update(2, [ 'subdomain' => 'endymioN' ]); // Should throw an exception
    }

    /**
     * Test if unique value is released when item is removed
     */
    public function testUniquenessReleaseOnRecordRemove()
    {
      $this->assertTrue($this->accounts->isUnique('subdomain'));

      $id = $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ])[0];

      $this->accounts->delete($id);

      $this->accounts->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ]);
    }
  }