<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance, ActiveCollab\Resistance\Test\Storage\Accounts, ActiveCollab\Resistance\Test\Storage\Users;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class UsersTest extends TestCase
  {
    /**
     * @var Accounts
     */
    private $accounts;

    /**
     * @var Users
     */
    private $users;

    /**
     * Set up test environment
     */
    public function setUp()
    {
      parent::setUp();

      $this->accounts = Resistance::factory('\ActiveCollab\Resistance\Test\Storage\Accounts');
      $this->users = Resistance::factory('\ActiveCollab\Resistance\Test\Storage\Users');
    }

    /**
     * Test factory
     */
    public function testFactory()
    {
      $this->assertInstanceOf('\ActiveCollab\Resistance\Test\Storage\Users', $this->users);
    }

    /**
     * Test if member and client counts are properly refreshed as users are added and removed from the list
     */
    public function testMembersAndClientsCount()
    {
      $afiveone_id = $this->accounts->insert([
        'subdomain' => 'afiveone',
        'url' => 'https://afiveone.activecollab.com',
        'license_key' => '123',
      ])[0];

      $this->assertEquals(1, $this->accounts->count());
      $this->assertEquals(1, $afiveone_id);

      list ($owner_id, $member_id, $subcontractor_id, $client1_id, $client2_id) = $this->users->insert(
        [ 'acid' => 1001, 'email' => 'owner@activecollab.com', 'role' => 'Owner' ],
        [ 'acid' => 1002, 'email' => 'member@activecollab.com', 'role' => 'Member' ],
        [ 'acid' => 1003, 'email' => 'subcontractor@activecollab.com', 'role' => 'Subcontractor' ],
        [ 'acid' => 1004, 'email' => 'client1@activecollab.com', 'role' => 'Client' ],
        [ 'acid' => 1005, 'email' => 'client2@activecollab.com', 'role' => 'Client' ]
      );

      $this->assertEquals(1, $owner_id);
      $this->assertEquals(2, $member_id);
      $this->assertEquals(3, $subcontractor_id);
      $this->assertEquals(4, $client1_id);
      $this->assertEquals(5, $client2_id);

      $afiveone_users = $this->accounts->usersBelongingTo(1);

      $this->assertInstanceOf('ActiveCollab\Resistance\Storage\Relationship\BelongingTo', $afiveone_users);

      $this->assertEquals(0, $this->accounts->getFieldValue(1, 'members_count'));
      $this->assertEquals(0, $this->accounts->getFieldValue(1, 'clients_count'));

      // ---------------------------------------------------
      //  Add members
      // ---------------------------------------------------

      $afiveone_users->add($owner_id, $member_id, $subcontractor_id);

      $this->assertEquals(3, $this->accounts->getFieldValue(1, 'members_count'));
      $this->assertEquals(0, $this->accounts->getFieldValue(1, 'clients_count'));

      // ---------------------------------------------------
      //  Add clients
      // ---------------------------------------------------

      $afiveone_users->add($client1_id, $client2_id);

      $this->assertEquals(3, $this->accounts->getFieldValue(1, 'members_count'));
      $this->assertEquals(2, $this->accounts->getFieldValue(1, 'clients_count'));

      // ---------------------------------------------------
      //  Remove one member and one client
      // ---------------------------------------------------

      $afiveone_users->remove($subcontractor_id);
      $afiveone_users->remove($client1_id);

      $this->assertEquals(2, $this->accounts->getFieldValue(1, 'members_count'));
      $this->assertEquals(1, $this->accounts->getFieldValue(1, 'clients_count'));

      // ---------------------------------------------------
      //  Clear
      // ---------------------------------------------------

      $afiveone_users->clear();

      $this->assertEquals(0, $this->accounts->getFieldValue(1, 'members_count'));
      $this->assertEquals(0, $this->accounts->getFieldValue(1, 'clients_count'));
    }
  }