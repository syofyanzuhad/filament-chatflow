# This is my package filament-chatflow

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syofyanzuhad/filament-chatflow.svg?style=flat-square)](https://packagist.org/packages/syofyanzuhad/filament-chatflow)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/syofyanzuhad/filament-chatflow/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/syofyanzuhad/filament-chatflow/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/syofyanzuhad/filament-chatflow/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/syofyanzuhad/filament-chatflow/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/syofyanzuhad/filament-chatflow.svg?style=flat-square)](https://packagist.org/packages/syofyanzuhad/filament-chatflow)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require syofyanzuhad/filament-chatflow
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-chatflow-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-chatflow-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-chatflow-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentChatflow = new Syofyanzuhad\FilamentChatflow();
echo $filamentChatflow->echoPhrase('Hello, Syofyanzuhad!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Syofyan Zuhad](https://github.com/syofyanzuhad)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
