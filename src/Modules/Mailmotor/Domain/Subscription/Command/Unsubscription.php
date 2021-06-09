<?php

namespace ForkCMS\Modules\Mailmotor\Domain\Subscription\Command;

use ForkCMS\Modules\Internationalisation\Frontend\Domain\Locale\Locale;
use Symfony\Component\Validator\Constraints as Assert;
use ForkCMS\Modules\Mailmotor\Domain\Subscription\Validator\Constraints as MailingListAssert;

final class Unsubscription
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(message="err.EmailIsInvalid")
     * @MailingListAssert\EmailUnsubscription
     */
    public $email;

    /**
     * @var Locale
     */
    public $locale;

    public function __construct(Locale $locale, string $email = null)
    {
        $this->locale = $locale;
        $this->email = $email;
    }
}
