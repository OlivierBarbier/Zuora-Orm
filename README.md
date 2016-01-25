# Zuora ORM

[![StyleCI](https://styleci.io/repos/47043649/shield?v2)](https://styleci.io/repos/47043649)
[![Build Status](https://travis-ci.org/OlivierBarbier/Zuora-Orm.svg?branch=master)](https://travis-ci.org/OlivierBarbier/Zuora-Orm)
[![Latest Stable Version](https://poser.pugx.org/olivierbarbier/zuora-orm/v/stable)](https://packagist.org/packages/olivierbarbier/zuora-orm) [![Total Downloads](https://poser.pugx.org/olivierbarbier/zuora-orm/downloads)](https://packagist.org/packages/olivierbarbier/zuora-orm) [![Latest Unstable Version](https://poser.pugx.org/olivierbarbier/zuora-orm/v/unstable)](https://packagist.org/packages/olivierbarbier/zuora-orm) [![License](https://poser.pugx.org/olivierbarbier/zuora-orm/license)](https://packagist.org/packages/olivierbarbier/zuora-orm)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/3f674c15cd2443dea5d5d3d6eb58e136)](https://www.codacy.com/app/obarbier/Zuora-Orm)
- [Introduction](#introduction)
- [Basic Usage](#basic-usage)
- [Insert, Update, Delete](#insert-update-delete)
- [Relationships](#relationships)
- [Querying Relations](#querying-relations)
- [Collections](#collections)


<a name="introduction"></a>
## Introduction

The Zuora ORM provides a simple way for working with Zuora objects without writing ZOQL queries. Each zuora object has a corresponding "Model" which is used to interact with that object.

<a name="basic-usage"></a>
## Basic Usage

#### Retrieving A Record By Primary Key

	$account = Account::find(1);

	var_dump($account->Name);

#### Querying Using Zuora ORM Models

	$accounts = Account::where('Status', '=', 'Active')->get();

	foreach ($accounts as $account)
	{
		var_dump($account->name);
	}

#### Zuora ORM Aggregates

Of course, you may also use the query builder aggregate functions.

	$count = Account::where('Status', '=', 100)->get()->count();

<a name="insert-update-delete"></a>
## Insert, Update, Delete

#### Using The Model Create Method

	// Create a new user in Zuora...
	$account = Account::create(array('name' => 'my test account'));

#### Updating A Retrieved Model

To update a model, you may retrieve it, change an attribute, and use the `save` method:

	$account = Account::find(1);

	$account->status = 'Active';

	$account->save();

#### Deleting An Existing Model

To delete a model, simply call the `delete` method on the instance:

	$account = Account::find(1);

	$account->delete();

<a name="relationships"></a>
## Relationships

<a name="one-to-one"></a>
### One To One

#### Retrieve A One To One Relation

For example, a `Subscription` model have one `Account`.

We may retrieve it using Zuora ORM's [dynamic properties](#dynamic-properties):

	$account = Subscription::find(1)->account;

The SQL performed by this statement will be as follows:

	select * from Subscription where Id = 1

	select * from Account where Id = $subscription->Id

<a name="one-to-many"></a>
### One To Many

An example of a one-to-many relation is an account that "has many" subscriptions.

We can access the account's subscriptionss through the [dynamic property](#dynamic-properties):

	$subscriptions = Account::find(1)->subscriptions;

If you need to add further constraints to which subscriptions are retrieved, you may call the `subscriptions` method and continue chaining conditions:

	$subscriptions = Account::find(1)->subscriptions()->where('Status', '=', 'Active')->get()->first();

<a name="querying-relations"></a>
## Querying Relations

#### Querying Relations When Selecting

When accessing the records for a model, you may wish to limit your results based on the existence of a relationship. For example, you wish to pull all accounts that have at least one subscription:

	$accounts = Account::all()->filter(function($account) { return $account->subscription()->count() > 0; });

<a name="dynamic-properties"></a>
### Dynamic Properties

Zuora ORM allows you to access your relations via dynamic properties. Zuora ORM will automatically load the relationship for you, and is even smart enough to know whether to call the `get` (for one-to-many relationships) or `first` (for one-to-one relationships) method.  It will then be accessible via a dynamic property by the same name as the relation.

	$subscription = Subscription::find(1);

Instead of echoing the user's email like this:

	echo $subscription->account()->get()->first()->Name;

It may be shortened to simply:

	echo $subscription->account->Name;

> **Note:** Relationships that return many results will return an instance of the `Illuminate\Support\Collection` class.

<a name="collections"></a>
## Collections

All multi-result sets returned by Zuora ORM, either via the `get` method or a `relationship`, will return a collection object. This object implements the `IteratorAggregate` PHP interface so it can be iterated over like an array. However, this object also has a variety of other helpful methods for working with result sets.

#### Iterating Collections

Zuora ORM collections also contain a few helpful methods for looping and filtering the items they contain:

#### Filtering Collections

When filtering collections, the callback provided will be used as callback for [array_filter](http://php.net/manual/en/function.array-filter.php).

	$accounts = $accounts->filter(function($account)
	{
		return $account->hasSubcriptions();
	});

#### Applying A Callback To Each Collection Object

	$subscriptions = Account::find(1)->subscriptions;

	$subscriptions->each(function($subscription)
	{
		//
	});

#### Sorting A Collection By A Value

	$subscriptions = $subscriptions->sortBy(function($subscription)
	{
		return $subscription->CreatedDate;
	});
