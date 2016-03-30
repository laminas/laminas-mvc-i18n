<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\I18n\Router;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\I18n\Router\HttpRouterDelegatorFactory;
use Zend\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Zend\ServiceManager\ServiceManager;

class HttpRouterDelegatorFactoryTest extends TestCase
{
    public function setUp()
    {
        // Required, due to odd autoloading issues with prophecy that were
        // leading to an errant invalid class alias being reported.
        $this->container = $this->prophesize(ServiceManager::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryReturnsRouterUntouchedIfNotATranslatorAwareTreeRouteStack()
    {
        $router = (object) [];
        $callback = function () use ($router) {
            return $router;
        };

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryReturnsTranslatorAwareRouterWithTranslationsDisabledWhenNoTranslatorInContainer()
    {
        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldBeCalled();
        $callback = function () use ($router) {
            return $router->reveal();
        };

        $this->container->has('MvcTranslator')->willReturn(false);
        $this->container->has(TranslatorInterface::class)->willReturn(false);

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router->reveal(), $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryInjectsMvcTranslatorIntoRouterWhenPresentInContainer()
    {
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldNotBeCalled();
        $router->setTranslator($translator)->shouldBeCalled();

        $callback = function () use ($router) {
            return $router->reveal();
        };

        $this->container->has('MvcTranslator')->willReturn(true);
        $this->container->get('MvcTranslator')->willReturn($translator);
        $this->container->has(TranslatorInterface::class)->shouldNotBeCalled();

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router->reveal(), $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryInjectsTranslatorInterfaceIntoRouterWhenPresentInContainer()
    {
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldNotBeCalled();
        $router->setTranslator($translator)->shouldBeCalled();

        $callback = function () use ($router) {
            return $router->reveal();
        };

        $this->container->has('MvcTranslator')->willReturn(false);
        $this->container->has(TranslatorInterface::class)->willReturn(true);
        $this->container->get(TranslatorInterface::class)->willReturn($translator);

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router->reveal(), $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }
}
