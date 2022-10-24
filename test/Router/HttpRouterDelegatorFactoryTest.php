<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n\Router;

// phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Router\HttpRouterDelegatorFactory;
use Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Laminas\Router\RouteInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class HttpRouterDelegatorFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    protected $container;

    public function setUp(): void
    {
        // Required, due to odd autoloading issues with prophecy that were
        // leading to an errant invalid class alias being reported.
        $this->container = $this->prophesize(ServiceManager::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryReturnsRouterUntouchedIfNotATranslatorAwareTreeRouteStack(): void
    {
        $router   = (object) [];
        $callback = static fn(): object => $router;

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryReturnsTranslatorAwareRouterWithTranslationsDisabledWhenNoTranslatorInContainer(): void
    {
        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldBeCalled();
        $callback = static fn(): RouteInterface => $router->reveal();

        $this->container->has('MvcTranslator')->willReturn(false);
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router->reveal(), $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryInjectsMvcTranslatorIntoRouterWhenPresentInContainer(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldNotBeCalled();
        $router->setTranslator($translator)->shouldBeCalled();

        $callback = static fn(): RouteInterface => $router->reveal();

        $this->container->has('MvcTranslator')->willReturn(true);
        $this->container->get('MvcTranslator')->willReturn($translator);
        $this->container->has(TranslatorInterface::class)->shouldNotBeCalled();
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->shouldNotBeCalled();

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router->reveal(), $factory(
            $this->container->reveal(),
            'HttpRouter',
            $callback
        ));
    }

    public function testFactoryInjectsTranslatorInterfaceIntoRouterWhenPresentInContainer(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $router = $this->prophesize(TranslatorAwareTreeRouteStack::class);
        $router->setTranslatorEnabled(false)->shouldNotBeCalled();
        $router->setTranslator($translator)->shouldBeCalled();

        $callback = static fn(): RouteInterface => $router->reveal();

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
