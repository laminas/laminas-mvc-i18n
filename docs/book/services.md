# Services Defined

laminas-mvc-i18n defines the following services and related factories.

## Translator

`Laminas\Mvc\I18n\Translator` implements each of
`Laminas\I18n\Translator\TranslatorInterface` (from
laminas-i18n) and implements `Laminas\Validator\TranslatorInterface` (from
laminas-validator), by decorating an `Laminas\I18n\Translator\TranslatorInterface`
instance (typically a `Laminas\I18n\Translator\Translator` instance).

It exists to allow bridging multiple translation interfaces, allowing it to be
used in multiple contexts.

## DummyTranslator

`Laminas\Mvc\I18n\DummyTranslator` is an implementation of
`Laminas\I18n\Translator\TranslatorInterface` that essentially returns the
arguments provided to it unchanged.

As an example, calling:

```php
$translator->translate($message);
```

will return `$message`, and

```php
$translator->translatePlural($singular, $plural, $number);
```

will return `$singular` when `$number` is `1`, and `$plural` otherwise.

## MvcTranslator and TranslatorFactory

The component defines the `MvcTranslator` service, which is aliased to the
`Laminas\Mvc\I18n\Translator` class, and uses `Laminas\Mvc\I18n\TranslatorFactory` to
create and return the instance.

The point of the service is to ensure that a `Laminas\Mvc\I18n\Translator` instance
is returned, which enables usage across multiple contexts (see the [Translator
section](#translator), above).

As such, you should typically use the `MvcTranslator` service when injecting
your own classes with a translator instance:

```php
function ($container) {
    return new YourServiceClass($container->get('MvcTranslator'));
}
```

The `TranslatorFactory` will do the following:

- If a `Laminas\I18n\Translator\TranslatorInterface` service is registered, it will
  be retrieved and decorated with a `Laminas\Mvc\I18n\Translator` instance.
- If the "config" service is defined in the container, has a "translator" key,
  but the value of that key is boolean false, it returns a
  `Laminas\Mvc\I18n\Translator` instance wrapping a `DummyTranslator` instance.
- If the "config" service is defined in the container, has a "translator" key,
  and value is an array or `Traversable` set of configuration, it passes that to
  `Laminas\I18n\Translator\Translator::factory()` to create and return an instance.
  That instance is then decorated with a `Laminas\Mvc\I18n\Translator`.

## HttpRouterDelegatorFactory

The component registers a delegator factory on each of the `HttpRouter` and
`Laminas\Router\Http\TreeRouteStack` services. The delegator factory checks to see
if the instance created is a `Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack`,
and, if so:

- if the `MvcTranslator` service is present, it will inject it as the translator
  before returning it.
- if the `Laminas\I18n\Translator\TranslatorInterface` service is present, it will
  inject it as the translator before returning it.
- otherwise, it disables translation in the returned instance.
