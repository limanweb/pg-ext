# pg-ext
Extending the laravel to work with PostgreSQL tables

### Provides

* additional casting tipes for some native PostgreSQL types
* additional many-to-many relations with using of specific PostgreSQL array-type fields

### Anounced (in developing)

* extension of BluePrint to operate PostgreSQL array fields and GIN-indexes

## Installation
Run:
```bash
composer require "limanweb/pg-ext"
```
## Package contents

* **Models**\
  * **Concerns**\
    * **PgTypeCastable** - cast some PostgreSQL native types for model
    * **HasArrayRelationships** - additional relations for model
  * **Model** - The abstract model that used PgTypeCastable & HasArrayRelationships traits
* **Relations**\
  * **ArrayRelations** - base abstract class for array-field relations
  * **HasManyInArray** - HasManyInArray relation class
  * **BelongsToManyArrays** - BelongsToManyArrays relation class
* **Support**\
  * **PgHelper** - PostgreSQL native type convertion helper

## Using extended casting

Use trait Limanweb\PgExt\Models\Concerns\PgTypeCastable in your model to cast some native PostgreSQL types

1. Add trait ```Limanweb\PgExt\Models\Concerns\PgTypeCastable``` into your model
```php
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model 
{
	use Limanweb\PgExt\Models\Concerns\PgTypeCastable;
	
	// Your model implementation
}	
```
or inherite your model from ```Limanweb\PgExt\Models\Model``` to default using extended casting.
```php
use Limanweb\PgExt\Models\Model;

class YourModel extends Model 
{
	// Your model implementation
}
```
2. Describe your table array fields in $casts property with 'pg_array'
```php
protected $casts = [
	'your_array_field' => 'pg_array',
];
``` 

Available cast types:

* **pg_array** - use for one dimension array fields ("text[]", "varchar[]", "int[]" and other) 

Now you can operate with array-attributes of model like with PHP-array.



## Using extended relations

Add trait ```Limanweb\PgExt\Models\Concerns\HasArrayRelationships``` into your model
```php
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model 
{
	use Limanweb\PgExt\Models\Concerns\PgTypeCastable;
	use Limanweb\PgExt\Models\Concerns\HasArrayRelationships;
	
	// Your model implementation
}	
```
or inherite your model from ```Limanweb\PgExt\Models\Model``` to default using extended casting and relationships.

### What many-to-many relation trough array-field

For example, you have two tables posts and tags. Every post can have many tags and every tag can be associated with many posts. 
You can add a column 'tag_ids' of native PostgreSQL type 'INTEGER[]' (array of integer) into 'posts' table.
Now, you can use this field to specify ID-s of tagd associated with post.

Notes: Don't forget to create GIN-index on 'tag_ids' field. 

Using the hasManyInArray and the belongsToManyArrays relationships allows you to access related models.
See examples below.

### hasManyInArray relation

```php
use Limanweb\PgExt\Models\Model;

class Post extends Model {

	protected $casts = [
		'name' => 'string',
		'tag_ids' => 'pg_array', 	// this is array of integer PostgreSQL field
						// in SQL is "country_is INTEGER[],"
	];

	// Your model implementation

	/**
	 * @return Limanweb\PgExt\Relations\HasManyInArray
	 */
	public function tags() {
		return $this->hasManyInArray(Tag::class, 'tag_ids', 'id');
	}
}	
```

### belongsToManyArrays relation

```php
use Limanweb\PgExt\Models\Model;

class Tag extends Model {

	protected $casts = [
		'name' => 'string',
	];

	// Your model implementation

	/**
	 * @return Limanweb\PgExt\Relations\BelongsToManyArrays
	 */
	public function posts() {
		return $this->belongsToManyArrays(Post::class, 'tag_ids', 'id');
	}
}	
```


