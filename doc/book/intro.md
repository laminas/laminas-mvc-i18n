# Introduction

Zend Framework 2 offered functionality for integrating internationalization
capabilities provided by [zend-i18n](https://zendframework.github.io/zend-i18n/)
into MVC applications. This support includes:

- registration of an `MvcTranslator` service by default.
- a `DummyTranslator`, for use when `ext/intl` is unavailable, or configuration
  dictates that translations are disabled.
- an `MvcTranslator`, which implements both `Zend\I18n\Translator\TranslatorInterface`
  and `Zend\Validator\TranslatorInterface`, which decorates a
  `Zend\I18n\Translator\TranslatorInterface` instance.
- a `TranslatorAwareTreeRouteStack`, for enabling internationalized routing
  segments.

Since this functionality is entirely opt-in, we have decided that for version 3
of zend-mvc, we will offer it as a standalone package, to install when required
for your applications.

Additionally, because it bridges multiple `TranslatorInterface` implementations,
and provides i18n-capable routing, it can be useful with non-zend-mvc
applications such as [zend-expressive](https://zendframework.github.io/zend-expressive).

## Installation

Basic installation is via composer:

```bash
$ composer require zendframework/zend-mvc-i18n
```

Assuming you are using the [component installer](https://zendframework.github.io/zend-component-installer),
doing so will automatically enable the component in your application.

If you are not using the component installer, you will need to add the entry:

```php
'Zend\Mvc\I18n'
```

to your list of active modules. This is usually provided in one of the following
files:

- `config/application.config.php` (vanilla ZF skeleton application)
- `config/modules.config.php` (Apigility application)

> ### Manually enabling with zend-expressive
>
> If you are not using the component-installer with zend-expressive, you will
> need to add the entry:
>
> ```php
> \Zend\Mvc\I18n\ConfigProvider::class
> ```
>
> to your `config/config.php` class, assuming you are already using
> [expressive-config-manager](https://github.com/mtymek/expressive-config-manager).
> 
> If you are not, add a new global `config/autoload/` file with the following contents:
>
> ```php
> <?php
> use Zend\Mvc\I18n\ConfigProvider;
>
> $provider = new ConfigProvider();
> return $provider();
> ```

## Migration

In order to separate the i18n integration features from zend-mvc, we made a few
minor changes. While most of these are under-the-hood implementation details,
please read the [migration guide](migration/v2-to-v3.md) to verify your
application will continue to work.
