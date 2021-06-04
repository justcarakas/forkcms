<?php

namespace ForkCMS\Core\Installer\Domain\Authentication;

use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStepConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use Symfony\Component\Validator\Constraints as Assert;

final class AuthenticationStepConfiguration implements InstallerStepConfiguration
{
    /**
     * The backend login email for the GOD user
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public ?string $email;

    /**
     * The backend password for the GOD user
     *
     * @Assert\NotBlank()
     */
    public ?string $password;

    /**
     * Do we use a different debug emailaddress
     */
    public bool $differentDebugEmail;

    /**
     * The custom debug emailaddress, if applicable
     * @Assert\Email()
     */
    public ?string $debugEmail;

    public function __construct(
        ?string $email = null,
        ?string $password = null,
        bool $differentDebugEmail = false,
        ?string $debugEmail = null
    ) {
        $this->email = $email ?? $this->getDefaultEmail();
        $this->password = $password;
        $this->differentDebugEmail = $differentDebugEmail;
        $this->debugEmail = $debugEmail;
    }

    private
    function getDefaultEmail(): string
    {
        $host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        if (str_starts_with($host, '127.0.0.1') || str_starts_with($host, 'localhost')) {
            return 'info@localhost';
        }

        return 'info@' . $host;
    }

    public
    static function fromInstallerConfiguration(
        InstallerConfiguration $installerConfiguration
    ): static {
        if (!$installerConfiguration->hasStep(self::getStep())) {
            return new self();
        }

        return new self(
            $installerConfiguration->getAdminEmail(),
            $installerConfiguration->getAdminPasswordEmail(),
            $installerConfiguration->hasDifferentDebugEmail(),
            $installerConfiguration->getDebugEmail()
        );
    }

    public static function getStep(): InstallerStep
    {
        return InstallerStep::authentication();
    }

    public function normalise(): void
    {
        if (!$this->differentDebugEmail) {
            $this->debugEmail = null;
        }
    }
}
