<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n\Router;

// phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HttpRouterDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Decorate the HttpRouter factory.
     *
     * If the HttpRouter factory returns a TranslatorAwareTreeRouteStack, we
     * should inject it with a translator.
     *
     * If the MvcTranslator service is available, that translator is used.
     * If the TranslatorInterface service is available, that translator is used.
     *
     * Otherwise, we disable translation in the instance before returning it.
     *
     * @param string $name
     * @param null|array $options
     * @return RouteStackInterface|TranslatorAwareTreeRouteStack
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null)
    {
        $router = $callback();

        if (! $router instanceof TranslatorAwareTreeRouteStack) {
            return $router;
        }

        if ($container->has('MvcTranslator')) {
            $router->setTranslator($container->get('MvcTranslator'));
            return $router;
        }

        if ($container->has(TranslatorInterface::class)) {
            $router->setTranslator($container->get(TranslatorInterface::class));
            return $router;
        }

        $router->setTranslatorEnabled(false);

        return $router;
    }

    /**
     * laminas-servicemanager v2 compabibility
     *
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return RouteStackInterface|TranslatorAwareTreeRouteStack
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
