<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translator;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;

/** This class will make sure that the domain is set correctly */
final class ForkTranslator extends Translator
{
    public function __construct(
        ContainerInterface $container,
        MessageFormatterInterface $formatter,
        string $defaultLocale,
        array $loaderIds = [],
        array $options = [],
        array $enabledLocales = [],
        private ?RequestStack $requestStack = null
    ) {
        parent::__construct($container, $formatter, $defaultLocale, $loaderIds, $options, $enabledLocales);
    }

    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        if (!$this->requestStack instanceof RequestStack) {
            throw new \LogicException('Not implemented yet');
        }

        return parent::trans($id, $parameters, $domain, $locale);
    }
}
