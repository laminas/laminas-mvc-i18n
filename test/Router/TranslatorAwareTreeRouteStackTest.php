<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\I18n\Router;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\I18n\Translator\Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\Http\Request as Request;
use Zend\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Zend\Router\Http\RouteInterface;
use Zend\Uri\Http as HttpUri;

class TranslatorAwareTreeRouteStackTest extends TestCase
{
    /**
     * @var string
     */
    protected $testFilesDir;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $fooRoute;

    public function setUp()
    {
        $this->testFilesDir = __DIR__ . '/../_files';

        $this->translator = new Translator();
        $this->translator->addTranslationFile('phparray', $this->testFilesDir . '/tokens.en.php', 'route', 'en');
        $this->translator->addTranslationFile('phparray', $this->testFilesDir . '/tokens.de.php', 'route', 'de');

        $this->fooRoute = [
            'type' => 'Segment',
            'options' => [
                'route' => '/:locale',
            ],
            'child_routes' => [
                'index' => [
                    'type' => 'Segment',
                    'options' => [
                        'route' => '/{homepage}',
                    ],
                ],
            ],
        ];
    }

    public function testTranslatorAwareInterfaceImplementation()
    {
        $stack = new TranslatorAwareTreeRouteStack();
        $this->assertInstanceOf(TranslatorAwareInterface::class, $stack);

        // Defaults
        $this->assertNull($stack->getTranslator());
        $this->assertFalse($stack->hasTranslator());
        $this->assertEquals('default', $stack->getTranslatorTextDomain());
        $this->assertTrue($stack->isTranslatorEnabled());

        // Inject translator without text domain
        $translator = new Translator();
        $stack->setTranslator($translator);
        $this->assertSame($translator, $stack->getTranslator());
        $this->assertEquals('default', $stack->getTranslatorTextDomain());
        $this->assertTrue($stack->hasTranslator());

        // Reset translator
        $stack->setTranslator(null);
        $this->assertNull($stack->getTranslator());
        $this->assertFalse($stack->hasTranslator());

        // Inject translator with text domain
        $stack->setTranslator($translator, 'alternative');
        $this->assertSame($translator, $stack->getTranslator());
        $this->assertEquals('alternative', $stack->getTranslatorTextDomain());

        // Set text domain
        $stack->setTranslatorTextDomain('default');
        $this->assertEquals('default', $stack->getTranslatorTextDomain());

        // Disable translator
        $stack->setTranslatorEnabled(false);
        $this->assertFalse($stack->isTranslatorEnabled());
    }

    public function testTranslatorIsPassedThroughMatchMethod()
    {
        $translator = new Translator();
        $request    = new Request();

        $route = $this->getMock(RouteInterface::class);
        $route->expects($this->once())
              ->method('match')
              ->with(
                  $this->equalTo($request),
                  $this->isNull(),
                  $this->equalTo(['translator' => $translator, 'text_domain' => 'default'])
              );

        $stack = new TranslatorAwareTreeRouteStack();
        $stack->addRoute('test', $route);

        $stack->match($request, null, ['translator' => $translator]);
    }

    public function testTranslatorIsPassedThroughAssembleMethod()
    {
        $translator = new Translator();
        $uri        = new HttpUri();

        $route = $this->getMock(RouteInterface::class);
        $route->expects($this->once())
              ->method('assemble')
              ->with(
                  $this->equalTo([]),
                  $this->equalTo(['translator' => $translator, 'text_domain' => 'default', 'uri' => $uri])
              );

        $stack = new TranslatorAwareTreeRouteStack();
        $stack->addRoute('test', $route);

        $stack->assemble([], ['name' => 'test', 'translator' => $translator, 'uri' => $uri]);
    }

    public function testAssembleRouteWithParameterLocale()
    {
        $stack = new TranslatorAwareTreeRouteStack();
        $stack->setTranslator($this->translator, 'route');
        $stack->addRoute(
            'foo',
            $this->fooRoute
        );

        $this->assertEquals('/de/hauptseite', $stack->assemble(['locale' => 'de'], ['name' => 'foo/index']));
        $this->assertEquals('/en/homepage', $stack->assemble(['locale' => 'en'], ['name' => 'foo/index']));
    }

    public function testMatchRouteWithParameterLocale()
    {
        $stack = new TranslatorAwareTreeRouteStack();
        $stack->setTranslator($this->translator, 'route');
        $stack->addRoute(
            'foo',
            $this->fooRoute
        );

        $request = new Request();
        $request->setUri('http://example.com/de/hauptseite');

        $match = $stack->match($request);
        $this->assertNotNull($match);
        $this->assertEquals('foo/index', $match->getMatchedRouteName());
    }

    public function testFactoryWillInjectTextDomainIfPresent()
    {
        $config = ['translator_text_domain' => 'routing'];

        $stack = TranslatorAwareTreeRouteStack::factory($config);
        $this->assertEquals($config['translator_text_domain'], $stack->getTranslatorTextDomain());
    }
}
