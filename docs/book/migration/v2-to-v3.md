# Migration: zend-mvc v2 to zend-mvc-i18n

zend-mvc-i18n ports all i18n integration functionality from the zend-mvc v2
release to a single component. As such, a number of classes were renamed that
could potentially impact end-users.

## TranslatorAwareTreeRouteStack

`Zend\Mvc\Router\Http\TranslatorAwareTreeRouteStack` was renamed to
`Zend\Mvc\I18n\Router\TranslatorAwareTreeRouteStack`. It functions exactly as
the original, however, including accepting the same configuration.

## TranslatorServiceFactory

`Zend\Mvc\Service\TranslatorServiceFactory` was renamed to
`Zend\Mvc\I18n\TranslatorFactory`. Behavior remains the same.

## Exceptions thrown by the MVC translator

In v2 releases, `Zend\Mvc\I18n\Translator` would throw exceptions from the
`Zend\Mvc\Exception` namespace. It now throws exceptions from the new
`Zend\Mvc\I18n\Exception` namespace.
