# ROUTES - HTTP Handler

Install the bundle using Composer:

```
composer require roy404/routes
```

# Route Feature Documentation

The route feature allows you to manage HTTP requests easily in your application. To set up and use this feature, follow the instructions below:

## Available Methods

**Http Methods**
- `static get(string $uri, array $action)`: Defines a GET route.
- `static post(string $uri, array $action)`: Defines a POST route.
- `static put(string $uri, array $action)`: Defines a PUT route.
- `static patch(string $uri, array $action)`: Defines a PATCH route.
- `static delete(string $uri, array $action)`: Defines a DELETE route.

**Route Configuration**
- `static group(array $attributes, \Closure $action)`: Groups routes with shared configurations and middleware.
- `static controller(string $className)`: Registers a controller to handle specific routes.
- `static middleware(string|array $action)`: Registers middleware for a specific route.
- `static prefix(string $prefix)`: Adds a URI prefix to a set of routes.
- `static name(string $name)`: Assigns a name to a route for easy referencing.
- `static domain(string|array $domain)`: Restricts a route to a specific domain (for multi-domain setups).

## Example Route Configuration Methods

1. **Group** `static group(array $attributes, \Closure $action)`.
   * **Description** Registers a group of routes that share common configurations or middleware. This method enhances route organization and reusability by allowing you to apply settings or middleware to multiple routes at once.
   * **Usage**:
     ```php
     Route::group(['middleware' => 'auth'], function () {
        Route::get('/dashboard', function () {
            echo 'Welcome to the Dashboard';
        });
   
        Route::get('/profile', function () {
            echo 'Your Profile';
        });
     });
     ```
   * **Parameters**
     * `$attributes`: An array of configuration options for the group (e.g., middleware, prefix, etc.).
     * `$action`: A closure that contains all routes to be grouped.


2. **Controller** `static controller(string $className)`
   * **Description** Registers a controller class that will handle requests for specific routes.
   * **Usage**
     ```php
     Route::controller(HomeController::class)->group(function() {
        Route::get('/home', 'index');
     }); 
     
     /**
      * Explanation:
      *
      * Controller Registration: The Route::controller(HomeController::class) method registers the HomeController
      * to handle specific routes within the group. This means that any route defined within the group will be
      * handled by the controller's methods.
      *
      * Defining Routes: Inside the group, the Route::get('/home', 'index') defines the `/home` route, which
      * will be handled by the `index` method of the HomeController.
      */
      ```
   * **Parameters**
     * `$className`: The class name of the controller to handle the route.


3. **Middleware**: `static middleware(string|array $action)`
   * **Description** Registers middleware for a specific route. Middleware can perform various tasks, such as authentication, logging, and security checks.
   * **Usage**
     ```php
     Route::middleware([auth::class, 'isAuthenticated'])->group(function() {
        Route::get('/profile', function() {
            echo "Your profile";
        });
     }); 
     ```
   * **Parameters**:
     * `$action`: The middleware action or array of middleware to apply to the route.


4. **Prefix** `static prefix(string $prefix)`
   * **Description** Adds a prefix to the URI of the route, which is useful for route grouping (e.g., adding /admin for admin routes).
   * **Usage**
     ```php
     Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            echo 'Admin Dashboard';
        });
     });
     
     /**
      * Explanation:
      *
      * Prefixing Routes: The Route::prefix('admin') method adds the 'admin' prefix to all the routes inside the group.
      * In this case, /dashboard will be accessible at /admin/dashboard.
      *
      * Defining Routes: Inside the group, we define the /dashboard route, which will display the message 'Admin Dashboard'.
      */
      ```
   * **Parameters**
     * `$prefix`: Append the prefix before the URI.
     
     

5. **Name** `static name(string $name)`
   * **Description** Assigns a name to a route. This makes it easier to refer to the route later in your code, especially when generating URLs.
   * **Usage**
     ```php
     Route::name('user')->group(function() {
        Route::get('home', function() {
            echo 'Your home';
        })->name('home');
     
        Route::get('profile', function() {
            echo 'Your profile';
        })->name('profile');
     });
     
     /**
      * Once your routes are set up, you can easily retrieve their URLs by calling the route name. 
      * This is especially useful when you need to generate links dynamically.
      *
      * Result:
      * "user.home" => '/home'
      * "user.profile" => '/profile'
      */
     ```
   * **Parameters**
     * `$name`: The name to assign to the route.
   

6. **Domain** `static domain(string $domain)`
   * **Description** Restricts a route to a specific domain, useful for multi-domain applications (e.g., admin.example.com or api.example.com).
   * **Usage**
     ```php
     Route::domain('admin.example.com')->group(function () {
        Route::get('/home', function() {
            echo 'Your home';
        });
     });  
     ```
   * **Parameters**
     * `$domain`: The domain to associate with the route.


## Getting Started

1. **Configuring Routes:** To configure and use the routing system, you need to call the Route::configure() function at the start of your application (usually in your main entry point file, like index.php or app.php).

   ```php
   Route::configure(__DIR__, [
        'routes/web.php' // Add all route files here, you can add more as needed.
   ])->routes(function (array $routes) {
        /**
         *  Retrieve all the registered routes here, 
         *  you will be able to see all the details of each routes registered.
         */
   })->captured(function (mixed $content, int $code, string $type) {
        // Handle the response here
        http_response_code($code);
        header('Content-Type: ' . $type); // Set the content type (e.g., 'text/html', 'application/json').
        echo $content; // Output the response content.
   });
   ```

2. **Using the Routes Feature** After configuring your routes as described in Step 1, you can now start using the route feature to register your routes and define actions that should be taken when those routes are accessed.

In the example below, we use the Route::get() method to register a route for the homepage (/), which will echo Hello World! when visited.
```php
  <?php
  
   use App\Routes\Route;
   
   Route::get('/', function () {
      echo 'Hello World!';
   });
```

## How to run a single file in application?

To set up routing in your application, ensure that your web server is configured to use URL rewriting. This allows your application to route requests properly. Below are the configurations for both Apache or Nginx servers.

### Apache

If you are using an Apache web server, add the following code to your `.htaccess` file located in your application's root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # Handle all other URLs
    RewriteRule ^(.*)$ web.php/$1 [L] # Recommended: index.php
</IfModule>
```

### Nginx
For Nginx, you will need to configure the server block in your Nginx configuration file (usually located in /etc/nginx/sites-available/default or a similar path). Add the following rules to handle URL rewriting:

```nginx
location / {
    try_files $uri $uri/ /web.php?$query_string; # Recommended: index.php
}
```