<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

/** @ORM\Embeddable */
class TranslationDomain
{
    /**
     * @ORM\Column(type="core-application-application")
     */
    private Application $application;

    /**
     * @ORM\Column(type="modules-extensions-module-module_name")
     */
    private ModuleName $moduleName;

    public function __construct(Application $application, ModuleName $moduleName)
    {
        $this->application = $application;
        $this->moduleName = $moduleName;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getModuleName(): ModuleName
    {
        return $this->moduleName;
    }

    public function __toString(): string
    {
        return $this->application . '.' . $this->moduleName;
    }
}
