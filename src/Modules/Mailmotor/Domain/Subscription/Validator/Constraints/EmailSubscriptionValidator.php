<?php

namespace ForkCMS\Modules\Mailmotor\Domain\Subscription\Validator\Constraints;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use MailMotor\Bundle\MailMotorBundle\Exception\NotImplementedException;
use ForkCMS\Core\Frontend\Helper\Model;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Locale\Locale;
use MailMotor\Bundle\MailMotorBundle\Helper\Subscriber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class EmailSubscriptionValidator extends ConstraintValidator
{
    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function setSubscriber(Subscriber $subscriber): void
    {
        $this->subscriber = $subscriber;
    }

    public function validate($value, Constraint $constraint): void
    {
        // There are already violations thrown, so we return immediately
        if (count($this->context->getViolations()) > 0) {
            return;
        }

        try {
            // The email is already in our mailing list
            if ($this->subscriber->isSubscribed(
                $value,
                Model::get(ModuleSettingRepository::class)->get('Mailmotor', 'list_id_' . Locale::frontendLanguage())
            )) {
                $this->context->buildViolation($constraint->alreadySubscribedMessage)->addViolation();
            }
            // fallback for when no mail-engine is chosen in the Backend
        } catch (NotImplementedException $e) {
            // do nothing
        }
    }
}
