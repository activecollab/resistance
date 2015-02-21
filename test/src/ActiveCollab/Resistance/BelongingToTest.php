<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Test\Storage\Accounts;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class BelongingToTest extends TestCase
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

      $afiveone_id = $this->accounts->insert([
        'subdomain' => 'afiveone',
        'url' => 'https://afiveone.activecollab.com',
        'license_key' => '123',
      ])[0];

      $this->assertEquals(1, $this->accounts->count());
      $this->assertEquals(1, $afiveone_id);
    }

    /**
     * Test instance
     */
    public function testUsersBelongingToInstance()
    {
      $this->assertInstanceOf('ActiveCollab\Resistance\Storage\Relationship\BelongingTo', $this->accounts->usersBelongingTo(1));
    }

    /**
     * Test add members
     */
    public function testAdd()
    {
      $feather_id = $this->accounts->insert([
        'subdomain' => 'feather',
        'url' => 'https://feather.activecollab.com',
        'license_key' => '456',
      ])[0];

      $this->assertEquals(2, $this->accounts->count());
      $this->assertEquals(2, $feather_id);

      $users_in_afiveone = $this->accounts->usersBelongingTo(1);
      $users_in_feather = $this->accounts->usersBelongingTo(2);

      $this->assertEquals([], $users_in_afiveone->get());
      $this->assertEquals([], $users_in_feather->get());

      $this->createUsers(10);

      $users_in_afiveone->add(1, 2, 3, 5);
      $users_in_feather->add(8, 9, 10);

      $this->assertEquals([ 1, 2, 3, 5 ], $users_in_afiveone->get());
      $this->assertEquals([ 8, 9, 10 ], $users_in_feather->get());

      $this->assertTrue($users_in_afiveone->has(1));
      $this->assertFalse($users_in_afiveone->has(4));

      $this->assertEquals(4, $users_in_afiveone->count());
      $this->assertEquals(3, $users_in_feather->count());
    }

    /**
     * Test remove from relation
     */
    public function testRemove()
    {
      $this->createUsers(5);

      $users_in_afiveone = $this->accounts->usersBelongingTo(1);

      $users_in_afiveone->add(1, 2, 3, 5);
      $this->assertEquals([ 1, 2, 3, 5 ], $users_in_afiveone->get());

      $users_in_afiveone->remove(2);
      $users_in_afiveone->remove(3);

      $this->assertEquals([ 1, 5 ], $users_in_afiveone->get());

      $users_in_afiveone->clear();

      $this->assertEquals([], $users_in_afiveone->get());
    }

    /**
     * @param integer $how_many
     */
    private function createUsers($how_many)
    {
      $users = Resistance::factory('\ActiveCollab\Resistance\Test\Storage\Users');

      for ($i = 1; $i <= $how_many; $i++) {
        $this->assertEquals($i, $users->insert([ 'acid' => $i, 'email' => "user{$i}@acivecollab.com", 'role' => 'Member' ])[0]);
      }

      $this->assertEquals($how_many, $users->count());
    }
  }