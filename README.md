# Resistance

[![Build Status](https://travis-ci.org/activecollab/resistance.svg?branch=master)](https://travis-ci.org/activecollab/resistance)

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
  use ActiveCollab\Resistance\Storage\Field\BooleanField;

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

## Field Settings

Common field settings:

1. ``map`` - Map ID-s and values and make them accessible via ``getIdsBy()`` method,
1. ``protect`` - Protect field from being set on ``insert()``, 
2. ``required`` - Value is required not to be empty,
3. ``unique`` - Make sure that field value is unique in the storage (required is implied). This setting is not applicable to boolean fields,

String field settings:

1. ``format`` - Value is required and needs to match the given format,
2. ``isEmail`` - Value is required and needs to be a valid email address,
3. ``isUrl`` - Value is required and needs to be a valid URL,
4. ``modifier`` - Make sure that values go through this callback or function before they are stored.


## Read, Update, Delete

Instantiate it using a ``\ActiveCollab\Resistance::factory()``:

```php
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->insert([], [], …);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->update($id, []);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->get($id);
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->getFieldValue($id, 'url');
\ActiveCollab\Resistance::factory("\My\App\Storage\MyObjects")->delete($id);
```

## Migrations

Resistance implements a simple data migration system. to use migrations, create directory in your project where you will store migrations and name them like ``Migration0001.ThisIsMigrationDescription.php`` or ``Migration0001.php``. Class names of migrations should be in ``Migration0001`` format and they need to extend ``ActiveCollab\Resistance\Storage\Migration`` class. Description bit is optional and ignored - it's just for your reference. Example:
 
 
    /my/awesome/project/migrations/Migration0001.AddedNewField.php
    /my/awesome/project/migrations/Migration0002.MappedFieldValue.php
    /my/awesome/project/migrations/Migration0003.MadeFieldUnqiue.php
    /my/awesome/project/migrations/Migration0004.NoLongerNeedsToBeUnique.php
    
You can instruct Resistance to execute all migrations using:

```php
\ActiveCollab\Resistance::migrate('/my/awesome/project/migrations/', 'MyOrg\MyProject\Migrations');
```

First parameter is path to the folder where you have your migration classes stored, and second parameter is your migrations namespace. If you are not namespacing your migrations (not recommended), ommit the second parameter blank.

Useful methods that you can use in migrations:

* ``\ActiveCollab\Resistance\Storage\Collection::bulkSetFieldValue($field_name, $value)`` - Bulk set field value. ``$value`` can be callback that is called for each record, or a value that will be cast and stored, 
* ``\ActiveCollab\Resistance\Storage\Collection::bulkRemoveFieldValue($field_name)`` - Clean up values from the database, usually after field has been dropped from the collection, 
* ``\ActiveCollab\Resistance\Storage\Collection::buildValueMap($field_name)`` - Create value map for the field, usually after field has been added to the collection, 
* ``\ActiveCollab\Resistance\Storage\Collection::removeValueMap($field_name)`` - Remove value map from the field, after field was removed or mapping is no longer needed, 
* ``\ActiveCollab\Resistance\Storage\Collection::buildUniquenessMap($field_name)`` - Create uniqueness map for the field, usually after field has been added to the collection, 
* ``\ActiveCollab\Resistance\Storage\Collection::removeUniquenessMap($field_name)`` - Remove uniqueness map from the field, after field was removed or it no longer needs to be marked as unique. 
    
## How to contribute?

Five simple steps to contribute.

1. Fork the repo, 
2. Clone to your computer,
3. ``cd`` to checkout folder and run ``composer install`` to update dependencies, 
4. Make and push the changes. Make sure that you have tests in place,
5. Send a Pull Request.

Thank you!
