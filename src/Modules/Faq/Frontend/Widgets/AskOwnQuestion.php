<?php

namespace ForkCMS\Modules\Faq\Frontend\Widgets;

use ForkCMS\Core\Common\Mailer\Message;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FL;
use ForkCMS\Core\Frontend\Helper\Model as FrontendModel;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;

/**
 * This is a widget with the form to ask a question
 */
class AskOwnQuestion extends FrontendBaseWidget
{
    /**
     * Form instance
     *
     * @var FrontendForm
     */
    private $form;

    /**
     * The form status
     *
     * @var string
     */
    private $status;

    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();

        if (!$this->get(ModuleSettingRepository::class)->get($this->getModule(), 'allow_own_question', false)) {
            return;
        }

        $this->buildForm();
        $this->handleForm();
        $this->parse();
    }

    private function buildForm(): void
    {
        $this->form = new FrontendForm('own_question', '#' . FL::getAction('OwnQuestion'));
        $this->form->addText('name')->setAttributes(['required' => null]);
        $this->form->addText('email')->setAttributes(['required' => null, 'type' => 'email']);
        $this->form->addTextarea('message')->setAttributes(['required' => null]);
    }

    private function parse(): void
    {
        // parse an option so the stuff can be shown
        $this->template->assign('widgetFaqOwnQuestion', true);

        // parse the form or a status
        if (empty($this->status)) {
            $this->form->parse($this->template);

            return;
        }

        $this->template->assign($this->status, true);
    }

    private function validateForm(): bool
    {
        $this->form->cleanupFields();

        $this->form->getField('name')->isFilled(FL::err('NameIsRequired'));
        $this->form->getField('email')->isEmail(FL::err('EmailIsInvalid'));
        $this->form->getField('message')->isFilled(FL::err('QuestionIsRequired'));

        return $this->form->isCorrect();
    }

    private function isSpamFilterEnabled(): bool
    {
        return $this->get(ModuleSettingRepository::class)->get($this->getModule(), 'spamfilter', false);
    }

    private function getSubmittedQuestion(): array
    {
        return [
            'sentOn' => time(),
            'name' => $this->form->getField('name')->getValue(),
            'email' => $this->form->getField('email')->getValue(),
            'message' => $this->form->getField('message')->getValue(),
        ];
    }

    private function isQuestionSpam(array $question): bool
    {
        return FrontendModel::isSpam(
            $question['message'],
            SITE_URL . FrontendNavigation::getUrlForBlock($this->getModule()),
            $question['name'],
            $question['email']
        );
    }

    private function handleForm(): void
    {
        if (!$this->form->isSubmitted() || !$this->validateForm()) {
            return;
        }

        $question = $this->getSubmittedQuestion();

        if ($this->isSpamFilterEnabled() && $this->isQuestionSpam($question)) {
            $this->status = 'errorSpam';

            return;
        }

        $this->sendNewQuestionNotification($question);
        $this->status = 'success';
    }

    private function sendNewQuestionNotification(array $question): void
    {
        $from = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_from');
        $to = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_to');
        $replyTo = $this->get(ModuleSettingRepository::class)->get('Core', 'mailer_reply_to');
        $message = Message::newInstance(sprintf(FL::getMessage('FaqOwnQuestionSubject'), $question['name']))
            ->setFrom([$from['email'] => $from['name']])
            ->setTo([$to['email'] => $to['name']])
            ->setReplyTo([$replyTo['email'] => $replyTo['name']])
            ->parseHtml(
                '/Faq/Layout/Templates/Mails/OwnQuestion.html.twig',
                $question,
                true
            );
        $this->get('mailer')->send($message);
    }
}
