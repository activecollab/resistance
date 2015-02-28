<?php
  namespace ActiveCollab\Resistance\Test\Storage;

  use ActiveCollab\Resistance;
  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Collection, ActiveCollab\Resistance\Storage\Field\IntegerField, ActiveCollab\Resistance\Storage\Field\StringField, ActiveCollab\Resistance\Storage\Field\BooleanField, ActiveCollab\Resistance\Storage\Relationship\BelongingTo;


  /**
   * Accounts storage
   *
   * @package ActiveCollab\Resistance\Storage
   */
  final class Accounts extends Collection
  {
    /**
     * Construct a new collection instance
     */
    public function __construct()
    {
      $this->setFields([
        'license_key'   => (new StringField)->required()->modifier('trim'),
        'subdomain'     => (new StringField)->required()->unique()->modifier('trim'),
        'url'           => (new StringField)->required()->isUrl()->modifier('trim'),
        'is_paid'       =>  new BooleanField,
        'members_count' =>  new IntegerField,
        'clients_count' =>  new IntegerField,
      ]);
    }

    /**
     * @param  integer     $id
     * @return BelongingTo
     */
    public function usersBelongingTo($id)
    {
      return (new BelongingTo($this->connection, $this->getKeyById($id), 'users'))->onChange(function(array $user_ids) use ($id) {
        $members_count = $clients_count = 0;

        foreach ($user_ids as $user_id) {
          switch (Resistance::factory('\ActiveCollab\Resistance\Test\Storage\Users')->get($user_id)['role']) {
            case 'Owner':
            case 'Member':
            case 'Subcontractor':
              $members_count++; break;
            case 'Client':
              $clients_count++;
          }
        }

        $this->update($id, [ 'members_count' => $members_count, 'clients_count' => $clients_count ]);
      });
    }
  }