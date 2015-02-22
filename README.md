# Resistance

Resistance is a simple Redis key manager for PHP. It does not try to be a fully featured ORM, just to make keyspace 
management easier.

## How to use?

Include it in your project using Composer:

```json
{
    "require": {
        "activecollab/resistance": "~0.1"
    }
}
```

Implement a storage:

```php
<?php
  namespace My\App\Storage;

  use Predis\Client;
  use ActiveCollab\Resistance\Storage\Storage;
  use ActiveCollab\Resistance\Storage\Field\StringField;
  use ActiveCollab\Resistance\Storage\Field\IntegerField;

  /**
   * @package ActiveCollab\GrandCentral\Storage
   */
  final class MyObjects extends Storage
  {
    /**
     * Construct a new storage instance
     *
     * @param Client $connection
     * @param string $application_namespace
     */
    public function __construct(Client &$connection, $application_namespace)
    {
      parent::__construct($connection, $application_namespace);

      $this->setFields([
        'url'           => (new StringField)->required()->unique()->isUrl()->modifier('trim'),
        'is_paid'       =>  new BooleanField,
        'members_count' =>  new IntegerField,
        'clients_count' =>  new IntegerField,
      ]);
    }
  }
```

Instantiate it using a ``\ActiveCollab\Resistance::factory()``:

```php
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->insert([], [], …);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->update($id, []);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->get($id);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->getFieldValue($id, 'url');
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->delete($id);
```
    
## How to contribute?

Five simple steps to contribute.

1. Fork the repo, 
2. Clone to your computer,
3. ``cd`` to checkout folder and run ``composer install`` to update dependencies, 
4. Make and push the changes. Make sure that you have tests in place,
5. Send a Pull Request.

Thank you!
