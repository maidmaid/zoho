Zoho
====

[Zoho](https://www.zoho.com) client library.

[![Build Status](https://travis-ci.org/maidmaid/zoho.svg?branch=master)](https://travis-ci.org/maidmaid/zoho)
[![Latest Stable Version](https://poser.pugx.org/maidmaid/zoho/v/stable)](https://packagist.org/packages/maidmaid/zoho)
[![License](https://poser.pugx.org/maidmaid/zoho/license)](https://packagist.org/packages/maidmaid/zoho)

## Installation

```
composer require maidmaid/zoho
```

## Usage

### Constructor

```php
use Maidmaid\Zoho\Client;

$client = new Client('your_authtoken');
```

See [Using Authentication Token in official doc](https://www.zoho.com/crm/help/api/using-authentication-token.html) for more infos.

### Insert records

> To insert records into the required Zoho CRM module.

```php
$records = $client->insertRecords($module = 'Contacts', $data = [
    10 => [
        'Last Name' => 'Holmes',
        'First Name' => 'Sherlock',
    ]
);
```

See [insertRecords Method in official doc](https://www.zoho.com/crm/help/api/insertrecords.html) for more infos.

### Update records

> To update or modify the records in Zoho CRM

```php
$records = $client->updateRecords($module, $data = [
    10 => [
        'Id' => 'the_ID',
        'First Name' => 'Sherlock',
    ]
]);
```

See [updateRecords Method in official doc](https://www.zoho.com/crm/help/api/updaterecords.html) for more infos.

### Delete records

> To delete the selected records.

```php
$client->deleteRecords($module = 'Contacts', 'the_ID');
```

See [deleteMethod Method in official doc](https://www.zoho.com/crm/help/api/deleterecords.html) for more infos.

### Get record by ID

> To retrieve individual records by record ID

```php
$records = $client->getRecordById($module = 'Contacts', ['the_ID_1', 'the_ID_2'])
```

See [getRecordById Method in official doc](https://www.zoho.com/crm/help/api/getrecordbyid.html) for more infos.

### Get records

> To retrieve all users data specified in the API request.

Fetch data from first page:

```php
$records = $client->getRecords($module = 'Contacts')
```

Fetch data with pagination:

```php
$page = 0;
while ($records = $client->getRecords($module = 'Contacts', ++$page)) {

}
```

See [getRecords Method in official doc](https://www.zoho.com/crm/help/api/getrecords.html) for more infos.

### Search records

> To retrieve the records that match your search criteria.

```php
$records = $client = searchRecords($module = 'Contacts', $criteria = ['Last Name' => 'Holmes']);
```

See [searchRecords Method in official doc](https://www.zoho.com/crm/help/api/searchrecords.html) for more infos.

### Get fields

> To retrieve details of fields available in a module.

```php
$client->getFields($module = 'Contacts');
```

See [getFields Method in official doc](https://www.zoho.com/crm/help/api/getfields.html) for more infos.

### Generic call

```php 
$result = $client->call($module, $method, $params = array(), $data = array())
```

### Check errors

You can get last errors on failed process records:

```php
$client->getLastErrors();
```

All calls thrown an exception if global response fails (e.g. if API key is wrong): 

```php
try {
    $results = $client->updateRecords('Contacts', $updates = []);
} catch (ZohoCRMException $e) {}
```

## Licence

Zoho client library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
