<?php
  namespace ActiveCollab\Resistance\Test;

  use ActiveCollab\Resistance;

  /**
   * @package ActiveCollab\Resistance\Test
   */
  class StorageTest extends TestCase
  {
    /**
     * Test if namescamp is properly set from storage class
     */
    public function testNamespace()
    {
      $this->assertEquals('gc:accounts', GrandCentral::accounts()->getNamespace());
    }

    /**
     * Test next ID "locking" before it is used
     */
    public function testNextId()
    {
      $this->assertEquals(1, GrandCentral::accounts()->getNextId());
      $this->assertEquals(1, GrandCentral::accounts()->getNextId());
      $this->assertEquals(1, GrandCentral::accounts()->getNextId());
    }

    /**
     * Test get existing record
     */
    public function testGetExistingRecord()
    {
      $this->assertEquals(0, GrandCentral::accounts()->count());

      $id = GrandCentral::accounts()->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ])[0];

      $this->assertEquals(1, $id);
      $this->assertEquals(1, GrandCentral::accounts()->count());
      $this->assertEquals('afiveone', GrandCentral::accounts()->get($id)['subdomain']);
    }

    /**
     * @expectedException \ActiveCollab\Resistance\Error\Error
     */
    public function testGetNonExistingRecord()
    {
      GrandCentral::accounts()->get(1983);
    }

    /**
     * Test insert many and each() callback
     */
    public function testInsertManyAndEach()
    {
      $this->assertEquals(0, GrandCentral::accounts()->count());

      list ($id1, $id2, $id3) = GrandCentral::accounts()->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '456',
        'subdomain' => 'feather',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '789',
        'subdomain' => 'supportyard',
      'url' => 'https://www.activecollab.com',
      ]);

      $this->assertEquals(1, $id1);
      $this->assertEquals(2, $id2);
      $this->assertEquals(3, $id3);

      $this->assertEquals(3, GrandCentral::accounts()->count());

      $callback_triggered = 0;

      GrandCentral::accounts()->each(function($data, $iteration) use (&$callback_triggered) {

        switch ($data['_id']) {
          case 1:
            $this->assertEquals('afiveone', $data['subdomain']);
            $this->assertEquals(1, $iteration);
            break;
          case 2:
            $this->assertEquals('feather', $data['subdomain']);
            $this->assertEquals(2, $iteration);
            break;
          case 3:
            $this->assertEquals('supportyard', $data['subdomain']);
            $this->assertEquals(3, $iteration);
            break;
          default:
            $this->fail('Unexpected ID');
        }

        $callback_triggered++;
      });

      $this->assertEquals(3, $callback_triggered);
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
      $id = GrandCentral::accounts()->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ])[0];

      $record = GrandCentral::accounts()->get($id);

      $this->assertSame(false, $record['is_paid']);
    }

    /**
     * Test record update
     */
    public function testUpdate()
    {
      $this->assertEquals(0, GrandCentral::accounts()->count());

      $id = GrandCentral::accounts()->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ])[0];

      $this->assertEquals(1, GrandCentral::accounts()->count());
      $this->assertEquals('afiveone', GrandCentral::accounts()->get($id)['subdomain']);
      $this->assertEquals('123', GrandCentral::accounts()->get($id)['license_key']);

      GrandCentral::accounts()->update($id, [ 'subdomain' => '      farfaraway ', 'license_key' => '456' ]);

      $this->assertEquals(1, GrandCentral::accounts()->count());
      $this->assertEquals('farfaraway', GrandCentral::accounts()->get($id)['subdomain']);
      $this->assertEquals('456', GrandCentral::accounts()->get($id)['license_key']);
    }

    /**
     * Test remove IDs
     */
    public function testRemove()
    {
      $this->assertEquals(1, GrandCentral::accounts()->getNextId());
      $this->assertEquals(0, GrandCentral::accounts()->count());

      GrandCentral::accounts()->insert([
        'license_key' => '123',
        'subdomain' => 'afiveone',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '456',
        'subdomain' => 'feather',
        'url' => 'https://www.activecollab.com',
      ], [
        'license_key' => '789',
        'subdomain' => 'supportyard',
        'url' => 'https://www.activecollab.com',
      ]);

      $this->assertEquals(3, GrandCentral::accounts()->count());
      $this->assertEquals([ 1, 2, 3 ], GrandCentral::accounts()->getIds());

      GrandCentral::accounts()->delete(2);

      $this->assertEquals(2, GrandCentral::accounts()->count());
      $this->assertEquals([ 1, 3 ], GrandCentral::accounts()->getIds());
    }
  }