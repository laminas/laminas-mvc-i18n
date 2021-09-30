<?php

namespace Laminas\Mvc\I18n;

use Laminas\Router\Http\TreeRouteStack;

class ConfigProvider
{
    /**
     * Provide dependency configuration for an application integrating i18n.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide dependency configuration for an application integrating i18n.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'MvcTranslator' => Translator::class,

                // Legacy Zend Framework aliases
                \Zend\Mvc\I18n\Translator::class => Translator::class,
            ],
            'delegators' => [
                'HttpRouter' => [ Router\HttpRouterDelegatorFactory::class ],
                TreeRouteStack::class => [ Router\HttpRouterDelegatorFactory::class ],
            ],
            'factories' => [
                Translator::class => TranslatorFactory::class,
            ],
        ];
    }
}
