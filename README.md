# Zuora ORM for PHP aka Zorm

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
