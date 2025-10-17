# ELOQUENT CLASS

Install the bundle using Composer:

```
composer require roy404/eloquent
```

# DOCUMENTATION

The Eloquent class is a database query builder that provides a fluent interface to create SQL queries.

## Methods
- `table()`: Sets the table for the query.
- `select()`: Adds a column to the select clause.
- `where()`: Adds a where clause to the query.
- `orWhere()`: Adds an OR where clause to the query.
- `orderBy()`: Adds an order by clause to the query.
- `offset()`: Adds an offset clause to the query.
- `limit()`: Adds a limit clause to the query.
- `create()`: Creates a new record in the database.
- `replace()`: Replaces a record in the database.

## Model

The Model class extends Eloquent and provides additional methods for interacting with database tables that correspond to models.

- `all(): array`: Retrieves all records from the database table.
- `create(array $binds): int`: Creates a new record in the database table.
- `replace(array $binds): int`: Replaces a record in the database table.
- `find(int $id): array`: Retrieves a record by its primary key.
- `select(...$columns): Eloquent`: Selects specific columns from the database table.
- `where(string $column, mixed $operator_or_value, mixed $value = self::DEFAULT_VALUE): Eloquent`: Adds a where clause to the query.

## Return Data 

However, these functions are not yet fully implemented; you will need to complete them yourself.

- `lastID()`: int: Returns the last inserted ID from the database.
- `fetch()`: array: Fetches all rows from the result set as an array of arrays.
- `col()`: array: Fetches the first column of all rows from the result set as an array.
- `field()`: mixed: Fetches a single field value from the first row of the result set.
- `row()`: array: Fetches the first row from the result set as an associative array.
- `count()`: int: Returns the number of rows affected by the last SQL statement.

## Example Usage

```php
// Define the User class
class User extends Model
{
    protected string $primary_key = 'id';
    protected array $fillable = ['name'];
}

// Create a new user
$userId = User::create([
    'name' => 'Robroy'
]);

// Retrieve a user by ID
$user = User::find($userId);

// Update a user's record
User::where( 'name', 'Robroy' )->update(['name' => 'Robert']);

// Delete a user's record
User::where( 'name', 'Robert' )->delete();

// Another Example
$user = User::select( 'name', 'email', 'contact' )
    ->where( 'name', '<>', 'robot' )
    ->where( 'email', 'canales.robroy123@gmail.com' )
    ->where( function( \Illuminate\Databases\Eloquent $group ) {
        $group->where( 'contact', '+63 917 130 4494' )
              ->orWhere( 'contact', '216-2944' )
    })
    ->limit( 1 )
    ->row();

// Another Example [2]
DB::table( 'user' )->select( 'name' )->where( 'id', $userId )->field();    
```

### YOU WILL NEED TO CHANGE THE LOGIC OF THE FUNCTION BELOW:

`Illuminate\Databases\DB::run()` - We suggest you to create your own class that runs the query with the action provided in the `Return Data list`.