<?php

namespace Laminas\Mvc\I18n;

class Module
{
    /**
     * Provide configuration for an application integrating i18n.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
