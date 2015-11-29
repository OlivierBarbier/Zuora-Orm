# Zuora ORM for PHP aka Zorm

## Install via composer
`composer require "olivierbarbier/zuora-orm:dev-master"`

then edit the config.php

````
<?php
// vendor/olivierbarbier/zuora-orm/config/config.php

return [
    'wsdl'      => 'absolute/path/to/your/zuora.wsdl',
    'endpoint'  => 'https://apisandbox.zuora.com/apps/services/a/71.0',
    'user'      => 'your_zuora_login',
    'password'  => 'your_zuora_password',
];
````

## Get the first Zuora Account
```
<?php
require 'vendor/autoload.php';

use OlivierBarbier\Zorm\Zobject\Account;

$accountRepository = new Account;

$account = $accountRepository->first();

printf('Account Name: %s, Account Id: %s', $account->Name, $account->Id);
```

## Get an Account by Id
```
$sameAccount = $accountRepository->find($account->Id);


printf('Account Name: %s, Account Id: %s', $sameAccount->Name, $sameAccount->Id);
```

## Get all subscriptions for $sameAccount and loop over them
```
$subscriptions = $sameAccount->subscriptions;

// loop over them the procedural way
foreach($subscriptions as $subscription)
{
  echo $subscription->Name, "\n";
}

// loop over them the function way
$subscriptions->each(function($subscription) {
  echo $subscription->Name, "\n";
});
```

## Get all active subscriptions (1st approach)
```
$activeSubscriptions = $sameAccount->subscriptions()->where("Status", "=", "Active")->get();
```

## Get all active subscriptions (2nd approach)
```
$activeSubscriptions = $sameAccount->subscriptions->filter(function($subscription) {
  return $subscription->Status == "Active";
});
```
