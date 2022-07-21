<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    /** @var Translator */
    protected $translator;

    /** @var TranslatorInterface|MockObject */
    protected $i18nTranslator;

    public function setUp(): void
    {
        $this->i18nTranslator = $this->createMock(I18nTranslator::class);
        $this->translator     = new Translator($this->i18nTranslator);
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
