# Routing

[laminas-router](https://docs.laminas.dev/laminas-router/) provides routing
capabilities for [laminas-mvc](https://docs.laminas.dev/laminas-mvc/). In
version 2, these capabilities also included an opt-in feature of translatable
route segments.

`Laminas\Router\Http\Segment` has built-in facilities for translatable route
segments, but this functionality is disabled by default. To enable it, a
translator must be present in the options provided when matching; such options
are typically passed by the route stack invoking the segment.

laminas-mvc-i18n provides `Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack` to
do exactly that. If it is injected with a translator, it will pass the
translator on to each segment when invoking `match()`.

## Enabling TranslatorAwareTreeRouteStack

To enable the `TranslatorAwareTreeRouteStack` in your application, you will need
to add configuration that tells laminas-mvc to use it instead of the default
router. Additionally, you may want to indicate the translation locale and/or
text domain to use for translatable route segments.

The following is a configuration example that could be specified in a module or
at the application level:

```php
use Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;

return [
    'router' => [
        'router_class'           => TranslatorAwareTreeRouteStack::class,
        'translator_text_domain' => 'routing',
    ],
];
```

The above would configure and return a `TranslatorAwareTreeRouteStack` instance
when the router is requested. The instance would be configured to use the
`routing` text domain, and injected with either the `MvcTranslator` or
`Laminas\I18n\Translator\TranslatorInterface` service (whichever is present, with
the former having precedence).

The `translator_text_domain`, when not present, defaults to `default`.

## Translatable route segments

As a refresher, [segment routes](https://docs.laminas.dev/laminas-router/routing/#laminas92router92http92segment)
allow you to define a combination of literal characters and placeholders;
placeholders are denoted by `:name` notation within the definition.

To create a translatable segment, you use an alternate notation,
`{translation-key}`.

When matching, the translator uses its locale and the text domain configured to
translate translation keys in the route definition prior to attempting a match.

As an example, consider the following route definition:

```text
/{shopping_cart}/{products}/:productId
```

The above defines two translatable segments, `shopping_cart` and `products`.
When attempting to match, these keys are passed to the translator. If, for
example, the locale evaluates to `de-DE`, these might become `einkaufswagen` and
'produkte`, respectively, evaluating to:

```text
/einkaufswagen/produkte/:productId
```

This will then be what the router attempts to match against.
