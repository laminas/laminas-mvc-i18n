<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\I18n;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\I18n\Translator\Translator as I18nTranslator;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\I18n\Translator;

class TranslatorTest extends TestCase
{
    public function setUp()
    {
        $this->i18nTranslator = $this->getMock(I18nTranslator::class);
        $this->translator = new Translator($this->i18nTranslator);
    }

    public function testIsAnI18nTranslator()
    {
        $this->assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    public function testIsAValidatorTranslator()
    {
        $this->assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    public function testCanRetrieveComposedTranslator()
    {
        $this->assertSame($this->i18nTranslator, $this->translator->getTranslator());
    }

    public function testCanProxyToComposedTranslatorMethods()
    {
        $this->i18nTranslator->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en_US'));
        $this->translator->setLocale('en_US');
    }
}
