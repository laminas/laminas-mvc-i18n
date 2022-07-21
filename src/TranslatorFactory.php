<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n;

// phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Traversable;

use function array_key_exists;
use function extension_loaded;
use function is_array;

/**
 * Overrides the translator factory from the i18n component in order to
 * replace it with the bridge class from this namespace.
 */
class TranslatorFactory implements FactoryInterface
{
    /**
     * @param  string $requestedName
     * @return MvcTranslator
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        // Assume that if a user has registered a service for the
        // TranslatorInterface, it must be valid
        if ($container->has(TranslatorInterface::class)) {
            return new MvcTranslator($container->get(TranslatorInterface::class));
        }

        return $this->marshalTranslator($container);
    }

    /**
     * Create and return MvcTranslator instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @return MvcTranslator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, MvcTranslator::class);
    }

    /**
     * Marshal an MvcTranslator.
     *
     * If configuration exists, will pass it to the I18nTranslator::factory,
     * decorating the returned instance in an MvcTranslator.
     *
     * Otherwise:
     *
     * - returns an MvcTranslator decorating a DummyTranslator instance if
     *   ext/intl is not loaded.
     * - returns an MvcTranslator decorating an empty I18nTranslator instance.
     *
     * @return MvcTranslator
     */
    private function marshalTranslator(ContainerInterface $container)
    {
        // Load a translator from configuration, if possible
        $translator = $this->marshalTranslatorFromConfig($container);
        if ($translator) {
            return $translator;
        }

        // If ext/intl is not loaded, return a dummy translator
        if (! extension_loaded('intl')) {
            return new MvcTranslator(new DummyTranslator());
        }

        return new MvcTranslator(new I18nTranslator());
    }

    /**
     * Attempt to marshal a translator from configuration.
     *
     * Returns:
     * - an MvcTranslator seeded with a DummyTranslator if "translator"
     *   configuration is available, and evaluates to boolean false.
     * - an MvcTranslator seed with an I18nTranslator if "translator"
     *   configuration is available, and is a non-empty array or a Traversable
     *   instance.
     * - null in all other cases, including absence of a configuration service.
     *
     * @return void|MvcTranslator
     */
    private function marshalTranslatorFromConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return;
        }

        $config = $container->get('config');

        if (! array_key_exists('translator', $config)) {
            return;
        }

        // 'translator' => false
        if ($config['translator'] === false) {
            return new MvcTranslator(new DummyTranslator());
        }

        // Empty translator configuration
        if (is_array($config['translator']) && empty($config['translator'])) {
            return;
        }

        // Unusable translator configuration
        if (! is_array($config['translator']) && ! $config['translator'] instanceof Traversable) {
            return;
        }

        // Create translator from configuration
        $i18nTranslator = I18nTranslator::factory($config['translator']);

        // Inject plugins, if present
        if ($container->has('TranslatorPluginManager')) {
            $i18nTranslator->setPluginManager($container->get('TranslatorPluginManager'));
        }

        // Inject into service manager instances
        if ($container instanceof ServiceManager) {
            $container->setService(TranslatorInterface::class, $i18nTranslator);
        }

        return new MvcTranslator($i18nTranslator);
    }
}
