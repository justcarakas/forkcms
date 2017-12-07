<?php

namespace Backend\Modules\Locale\Domain\Translation;

use Backend\Modules\Extensions\Domain\Module\Name\Name as Module;
use Backend\Modules\Locale\Domain\Translation\Application\Application;
use Backend\Modules\Locale\Domain\Translation\Name\Name;
use Backend\Modules\Locale\Domain\Translation\Type\Type;
use Common\Locale;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Backend\Modules\Locale\Domain\Translation\TranslationRepository")
 * @ORM\Table(name="LocaleTranslation")
 * @ORM\HasLifecycleCallbacks
 */
class Translation
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @TODO update this when we have a user entity
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @var Locale
     *
     * @ORM\Column(type="locale")
     */
    private $locale;

    /**
     * @var Application
     *
     * @ORM\Column(type="locale_translation_application")
     */
    private $application;

    /**
     * @var Module
     *
     * @ORM\Column(type="extensions_module_name")
     */
    private $module;

    /**
     * @var Type
     *
     * @ORM\Column(type="locale_translation_type")
     */
    private $type;

    /**
     * @var Name
     *
     * @ORM\Column(type="locale_translation_name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $editedOn;

    public function __construct(
        int $userId,
        Locale $locale,
        Application $application,
        Module $module,
        Type $type,
        Name $name,
        string $value
    ) {
        $this->userId = $userId;
        $this->locale = $locale;
        $this->application = $application;
        $this->module = $module;
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    public function update(
        int $userId,
        Locale $locale,
        Application $application,
        Module $module,
        Type $type,
        Name $name,
        string $value
    ): void {
        $this->userId = $userId;
        $this->locale = $locale;
        $this->application = $application;
        $this->module = $module;
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateEditedOn(): void
    {
        $this->editedOn = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'language' => $this->locale->getLocale(),
            'application' => $this->application->getValue(),
            'module' => $this->module->getValue(),
            'type' => $this->type->getValue(),
            'name' => $this->name->getValue(),
            'value' => $this->value,
            'edited_on' => $this->editedOn->format('Y-m-d H:m:i')
        ];
    }
}
