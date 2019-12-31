# Introduction

Laminas offered functionality for integrating internationalization
capabilities provided by [laminas-i18n](https://docs.laminas.dev/laminas-i18n/)
into MVC applications. This support includes:

- registration of an `MvcTranslator` service by default.
- a `DummyTranslator`, for use when `ext/intl` is unavailable, or configuration
  dictates that translations are disabled.
- an `MvcTranslator`, which implements both `Laminas\I18n\Translator\TranslatorInterface`
  and `Laminas\Validator\TranslatorInterface`, which decorates a
  `Laminas\I18n\Translator\TranslatorInterface` instance.
- a `TranslatorAwareTreeRouteStack`, for enabling internationalized routing
  segments.

Since this functionality is entirely opt-in, we have decided that for version 3
of laminas-mvc, we will offer it as a standalone package, to install when required
for your applications.

Additionally, because it bridges multiple `TranslatorInterface` implementations,
and provides i18n-capable routing, it can be useful with non-laminas-mvc
applications such as [mezzio](https://docs.mezzio.dev/mezzio).

## Installation

Basic installation is via composer:

```bash
$ composer require laminas/laminas-mvc-i18n
```

Assuming you are using the [component installer](https://docs.laminas.dev/laminas-component-installer),
doing so will automatically enable the component in your application.

If you are not using the component installer, you will need to add the entry:

```php
'Laminas\Mvc\I18n'
```

to your list of active modules. This is usually provided in one of the following
files:

- `config/application.config.php` (vanilla Laminas skeleton application)
- `config/modules.config.php` (Laminas API Tools application)

> ### Manually enabling with mezzio
>
> If you are not using the component-installer with mezzio, you will
> need to add the entry:
>
> ```php
> \Laminas\Mvc\I18n\ConfigProvider::class
> ```
>
> to your `config/config.php` class, assuming you are already using
> [mezzio-config-manager](https://github.com/mtymek/mezzio-config-manager).
>
> If you are not, add a new global `config/autoload/` file with the following contents:
>
> ```php
> <?php
> use Laminas\Mvc\I18n\ConfigProvider;
>
> $provider = new ConfigProvider();
> return $provider();
> ```

## Migration

In order to separate the i18n integration features from laminas-mvc, we made a few
minor changes. While most of these are under-the-hood implementation details,
please read the [migration guide](migration/v2-to-v3.md) to verify your
application will continue to work.
