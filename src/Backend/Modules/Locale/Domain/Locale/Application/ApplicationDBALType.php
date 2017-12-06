<?php

namespace Backend\Modules\Locale\Domain\Locale\Application;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class ApplicationDBALType extends TextType
{
    public function getName(): string
    {
        return 'locale_application';
    }

    /**
     * @param string $application
     * @param AbstractPlatform $platform
     *
     * @return Application
     */
    public function convertToPHPValue($application, AbstractPlatform $platform): Application
    {
        return new Application($application);
    }

    /**
     * @param Application $application
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($application, AbstractPlatform $platform): string
    {
        return $application->getValue();
    }
}
