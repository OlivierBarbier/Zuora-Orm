# Zuora ORM

[![StyleCI](https://styleci.io/repos/47043649/shield)](https://styleci.io/repos/47043649)

- [Introduction](#introduction)
- [Basic Usage](#basic-usage)
- [Insert, Update, Delete](#insert-update-delete)
- [Relationships](#relationships)
- [Querying Relations](#querying-relations)
- [Collections](#collections)


<a name="introduction"></a>
## Introduction

The Zuora ORM provides a simple ActiveRecord implementation for working with Zuora objects. Each zuora object has a corresponding "Model" which is used to interact with that object.

<a name="basic-usage"></a>
## Basic Usage

#### Retrieving All Models

	$accounts = Account::all();

#### Retrieving A Record By Primary Key

	$account = account::find(1);

	var_dump($account->Name);

#### Querying Using Eloquent Models

	$accounts = Account::where('Status', '=', 'Active')->get();

	foreach ($accounts as $account)
	{
		var_dump($account->name);
	}

#### Zuora ORM Aggregates

Of course, you may also use the query builder aggregate functions.

	$count = Account::where('Status', '=', 100)->get()->count();

#### Specifying The Query Connection

You may also specify which database connection should be used when running an Eloquent query. Simply use the `on` method:

	$user = User::on('connection-name')->find(1);

<a name="insert-update-delete"></a>
## Insert, Update, Delete

To create a new object into Zuroa from a model, simply create a new model instance and call the `save` method.

#### Saving A New Model

	$account = new Account;

	$account->Name = 'John';

	$account->save();

#### Using The Model Create Method

	// Create a new user in the database...
	$user = User::create(array('name' => 'John'));

	// Retrieve the user by the attributes, or create it if it doesn't exist...
	$user = User::firstOrCreate(array('name' => 'John'));

	// Retrieve the user by the attributes, or instantiate a new instance...
	$user = User::firstOrNew(array('name' => 'John'));

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

Of course, your database tables are probably related to one another. For example, a blog post may have many comments, or an order could be related to the user who placed it. Eloquent makes managing and working with these relationships easy. Laravel supports many types of relationships:

- [One To One](#one-to-one)
- [One To Many](#one-to-many)

<a name="one-to-one"></a>
### One To One

#### Retrieve A One To One Relation

For example, a `Subscription` model might have one `Account`.

We may retrieve it using Zuora ORM's [dynamic properties](#dynamic-properties):

	$account = Subscriptions::find(1)->account;

The SQL performed by this statement will be as follows:

	select * from users where id = 1

	select * from phones where user_id = 1

<a name="one-to-many"></a>
### One To Many

An example of a one-to-many relation is an account that "has many" subscriptions.

We can access the account's subscriptionss through the [dynamic property](#dynamic-properties):

	$subscriptions = Account::find(1)->subscriptions;

If you need to add further constraints to which comments are retrieved, you may call the `comments` method and continue chaining conditions:

	$subscriptions = Account::find(1)->subscriptions()->where('Status', '=', 'Active')->get()->first();

<a name="querying-relations"></a>
## Querying Relations

#### Querying Relations When Selecting

When accessing the records for a model, you may wish to limit your results based on the existence of a relationship. For example, you wish to pull all blog posts that have at least one comment. To do so, you may use the `has` method:

	$accounts = Account::all()->filter(function($account) { return $account->subscription()->count() > 0; });

You may also specify an operator and a count:

	$accounts = Account::all()->filter(function($account) { 
		return $account->subscription()->count() >= 3; 
	});

If you need even more power, you may use the `whereHas` and `orWhereHas` methods to put "where" conditions on your `has` queries:

	$posts = Post::whereHas('comments', function($q)
	{
		$q->where('content', 'like', 'foo%');

	})->get();

<a name="dynamic-properties"></a>
### Dynamic Properties

Eloquent allows you to access your relations via dynamic properties. Eloquent will automatically load the relationship for you, and is even smart enough to know whether to call the `get` (for one-to-many relationships) or `first` (for one-to-one relationships) method.  It will then be accessible via a dynamic property by the same name as the relation. For example, with the following model `$phone`:

	$subscription = Subscription::find(1);

Instead of echoing the user's email like this:

	echo $subscription->account()->get()->first()->Name;

It may be shortened to simply:

	echo $subscription->account->Name;

> **Note:** Relationships that return many results will return an instance of the `Illuminate\Database\Eloquent\Collection` class.

<a name="collections"></a>
## Collections

All multi-result sets returned by Eloquent, either via the `get` method or a `relationship`, will return a collection object. This object implements the `IteratorAggregate` PHP interface so it can be iterated over like an array. However, this object also has a variety of other helpful methods for working with result sets.

#### Checking If A Collection Contains A Key

For example, we may determine if a result set contains a given primary key using the `contains` method:

	$roles = User::find(1)->roles;

	if ($roles->contains(2))
	{
		//
	}

Collections may also be converted to an array or JSON:

	$roles = User::find(1)->roles->toArray();

	$roles = User::find(1)->roles->toJson();

If a collection is cast to a string, it will be returned as JSON:

	$roles = (string) User::find(1)->roles;

#### Iterating Collections

Eloquent collections also contain a few helpful methods for looping and filtering the items they contain:

	$roles = $user->roles->each(function($role)
	{
		//
	});

#### Filtering Collections

When filtering collections, the callback provided will be used as callback for [array_filter](http://php.net/manual/en/function.array-filter.php).

	$users = $users->filter(function($user)
	{
		return $user->isAdmin();
	});

> **Note:** When filtering a collection and converting it to JSON, try calling the `values` function first to reset the array's keys.

#### Applying A Callback To Each Collection Object

	$roles = User::find(1)->roles;

	$roles->each(function($role)
	{
		//
	});

#### Sorting A Collection By A Value

	$roles = $roles->sortBy(function($role)
	{
		return $role->created_at;
	});

#### Sorting A Collection By A Value

	$roles = $roles->sortBy('created_at');
