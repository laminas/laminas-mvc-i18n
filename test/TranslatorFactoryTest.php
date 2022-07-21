<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n;

// phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

use ArrayAccess;
use ArrayObject;
use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\DummyTranslator;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\Mvc\I18n\TranslatorFactory;
use Laminas\ServiceManager\ServiceManager;
use Locale;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Traversable;

use function extension_loaded;

class TranslatorFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    protected $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ServiceManager::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryReturnsMvcTranslatorDecoratingTranslatorInterfaceServiceWhenPresent(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();
        $this->container->has(TranslatorInterface::class)->willReturn(true);
        $this->container->get(TranslatorInterface::class)->willReturn($translator);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertSame($translator, $test->getTranslator());
    }

    /** @return array<string, array{0: class-string}> */
    public function expectedTranslatorProvider(): array
    {
        return extension_loaded('intl')
            ? ['intl-loaded' => [I18nTranslator::class]]
            : ['no-intl-loaded' => [DummyTranslator::class]];
    }

    /**
     * @dataProvider expectedTranslatorProvider
     * @param class-string $expected
     */
    public function testFactoryReturnsMvcTranslatorDecoratingDefaultTranslatorWhenNoConfigPresent(
        string $expected
    ): void {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(false);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /**
     * @dataProvider expectedTranslatorProvider
     * @param class-string $expected
     */
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenNoTranslatorConfigPresent(
        string $expected
    ): void {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    public function testFactoryReturnsMvcDecoratorDecoratingDummyTranslatorWhenTranslatorConfigIsFalse(): void
    {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['translator' => false]);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf(DummyTranslator::class, $test->getTranslator());
    }

    /**
     * @param class-string $expected
     * @dataProvider expectedTranslatorProvider
     */
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenEmptyTranslatorConfigPresent(
        string $expected
    ): void {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['translator' => []]);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /** @return array<string, array{0: array<string, mixed>, 1: class-string}> */
    public function invalidTranslatorConfig(): array
    {
        $expectedTranslator = extension_loaded('intl')
            ? I18nTranslator::class
            : DummyTranslator::class;

        return [
            'null'    => [['translator' => null], $expectedTranslator],
            'true'    => [['translator' => true], $expectedTranslator],
            'zero'    => [['translator' => 0], $expectedTranslator],
            'int'     => [['translator' => 1], $expectedTranslator],
            'float-0' => [['translator' => 0.0], $expectedTranslator],
            'float'   => [['translator' => 1.1], $expectedTranslator],
            'string'  => [['translator' => 'invalid'], $expectedTranslator],
            'object'  => [['translator' => (object) ['translator' => 'invalid']], $expectedTranslator],
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @param class-string $expected
     * @dataProvider invalidTranslatorConfig
     */
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWithInvalidTranslatorConfig(
        $config,
        $expected
    ): void {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /**
     * @psalm-return array<non-empty-string,array{0:array<string,mixed>|ArrayAccess<string,mixed>}>
     */
    public function validTranslatorConfig(): array
    {
        $locale = Locale::getDefault() === 'en-US' ? 'de-DE' : Locale::getDefault();
        $config = [
            'locale'                => $locale,
            'event_manager_enabled' => true,
        ];

        return [
            'array'       => [$config],
            'traversable' => [new ArrayObject($config)],
        ];
    }

    /**
     * @requires extension intl
     * @dataProvider validTranslatorConfig
     * @param array|Traversable $config
     */
    public function testFactoryReturnsConfiguredTranslatorWhenValidConfigIsPresent($config): void
    {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['translator' => $config]);
        $this->container->has('TranslatorPluginManager')->willReturn(false);

        $this->container->setService(
            TranslatorInterface::class,
            Argument::type(I18nTranslator::class)
        )->shouldBeCalled();

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);

        $decorated = $test->getTranslator();
        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $this->assertEquals($config['locale'], $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
    }

    /**
     * @param array<string,mixed>|ArrayAccess<string,mixed> $config
     * @requires extension intl
     * @dataProvider validTranslatorConfig
     */
    public function testFactoryReturnsConfiguredTranslatorInjectedWithTranslatorPluginManagerWhenValidConfigIsPresent(
        $config
    ): void {
        $this->container->has(TranslatorInterface::class)->willReturn(false);
        $this->container->has('Zend\I18n\Translator\TranslatorInterface')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['translator' => $config]);
        $this->container->has('TranslatorPluginManager')->willReturn(true);

        $loaders = $this->prophesize(LoaderPluginManager::class)->reveal();
        $this->container->get('TranslatorPluginManager')->willReturn($loaders);

        $this->container->setService(
            TranslatorInterface::class,
            Argument::type(I18nTranslator::class)
        )->shouldBeCalled();

        $factory = new TranslatorFactory();
        $test    = $factory($this->container->reveal(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);

        $decorated = $test->getTranslator();
        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $this->assertEquals($config['locale'], $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
        $this->assertSame($loaders, $decorated->getPluginManager());
    }
}
