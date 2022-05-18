# permission-middleware
 PHP permission middleware for Slim 4. Requires PHP >= 8.0.

 When defining your routes, you may want to verify if the visitor is permitted
 to access each resource. But don't want to waste time writing unmaintainable
 per controller method guards. Then, this permission middleware has been written
 for you.

# Usage

Your permission list must be a class extending `PermissionList`. Each permission
level must be a bitwise number constant (1, 2, 4, 8...).

```php
// src/Permission.php
namespace App;

use Codevia\PermissionMiddleware\PermissionList;

class Permission extends PermissionList
{
    public const GUEST = 1;
    public const USER = 2;
    public const ADMIN = 4;
}
```

You may store your visitor permission level in a session like so:

```php
use App\Permission;
use Codevia\PermissionMiddleware\PermissionMiddleware;

$_SESSION[PermissionMiddleware::SESSION_PERMISSION] = Permission::USER;
```

When you add the permission middleware to your Slim app, you must give it your
permission list with the default value (in case it is not included in `$_SESSION`).

```php
// public/index.php
require_once __DIR__ . '../bootstrap.php';

use App\Permission;
use Codevia\PermissionMiddleware\PermissionMiddleware;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addMiddleware(new PermissionMiddleware(
    new Permission(Permission::GUEST) // Permission::GUEST is your default
));
```

Now you can set the permission level on each route you define. The unique way to
do so is using the array
[container resolution](https://www.slimframework.com/docs/v4/objects/routing.html#container-resolution).
That means your app must implement controllers and methods.

```php
use App\Permission as P;

$app->get('/test', [TestController::class, 'testMethod', P::USER | P::ADMIN]);
$app->get('/public', [TestController::class, 'publicMethod', P::GUEST]);
```

If the visitor permission is rejected, a `Slim\Exception\HttpForbiddenException`
is thrown.
