# laravel-identity
## Installation
### Package installation
The Laravel package can be installed using composer
```
composer require zploited/laravel-identity
```
After that you will need to configure your client information in your .env file, with these settings:
```dotenv
IDENTITY_IDENTIFIER=<tenant identifying domain name>
IDENTITY_CLIENT_ID=<identifier of the app>
IDENTITY_CLIENT_SECRET=<secret of the app>
IDENTITY_REDIRECT_URI=<redirect url, that will handle incoming token>
IDENTITY_SCOPE=<scopes to use>
IDENTITY_PROTOCOL=<http or https - default is https>
```

### Guards
The package comes with two different guards, that served each of their own purposes.
- identity:session
- identity:bearer

You will need to add the guard you want to use in your config/auth.php file.
This is done by adding the driver you want to use to the 'guards' section of the file, f.ex.
```php
'guards' => [
    'web' => [
        'driver' => 'identity:session'
    ],
],
```
Noticed that the provider has been removed since the guard doesn't use a provider.

## Usage
### Identity class
From now on, everything is handled through the Zploited\Identity\Client\Identity class.
It can be either be instanced through dependency injection, or through:
```php
$identity = app()->make(Zploited\Identity\Client\Identity::class);
```
This will get an instance with the configurations from config/identity-client.php.