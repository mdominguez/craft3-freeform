<?php

namespace Solspace\Freeform\Services\Pro;

use craft\base\Component;
use GuzzleHttp\Client;
use Solspace\Freeform\Events\Fields\ValidateEvent;
use Solspace\Freeform\Events\Forms\FormRenderEvent;
use Solspace\Freeform\Events\Forms\FormValidateEvent;
use Solspace\Freeform\Fields\RecaptchaField;
use Solspace\Freeform\Freeform;
use Solspace\Freeform\Library\Composer\Components\Form;
use Solspace\Freeform\Library\DataObjects\SpamReason;
use Solspace\Freeform\Models\Settings;

class RecaptchaService extends Component
{
    /**
     * @param ValidateEvent $event
     */
    public function validateRecaptchaV2Checkbox(ValidateEvent $event)
    {
        if ($this->isRecaptchaTypeSkipped(Settings::RECAPTCHA_TYPE_V2_CHECKBOX)) {
            return;
        }

        $field = $event->getField();
        if (($field instanceof RecaptchaField) && !$this->validateResponse()) {
            $message = $this->getSettings()->recaptchaErrorMessage;
            $field->addError(Freeform::t($message ?: 'Please verify that you are not a robot.'));
        }
    }

    /**
     * @param FormValidateEvent $event
     */
    public function validateRecaptchaV2Invisible(FormValidateEvent $event)
    {
        $recaptchaDisabled = !$event->getForm()->isRecaptchaEnabled();
        if ($recaptchaDisabled || $this->isRecaptchaTypeSkipped(Settings::RECAPTCHA_TYPE_V2_INVISIBLE)) {
            return;
        }

        if (!$this->validateResponse()) {
            if ($this->behaviourDisplayError()) {
                $message = $this->getSettings()->recaptchaErrorMessage;
                $event->getForm()->addError(Freeform::t($message ?: 'Please verify that you are not a robot.'));
            } else {
                $event->getForm()->markAsSpam(SpamReason::TYPE_RECAPTCHA, 'ReCAPTCHA invisible validation failed');
            }
        }
    }

    /**
     * @param FormValidateEvent $event
     */
    public function validateRecaptchaV3(FormValidateEvent $event)
    {
        $recaptchaDisabled = !$event->getForm()->isRecaptchaEnabled();
        if ($recaptchaDisabled || $this->isRecaptchaTypeSkipped(Settings::RECAPTCHA_TYPE_V3)) {
            return;
        }

        if (!$this->validateResponse()) {
            if ($this->behaviourDisplayError()) {
                $message = $this->getSettings()->recaptchaErrorMessage;
                $event->getForm()->addError(Freeform::t($message ?: 'Your submission could not be processed.'));
            } else {
                $event->getForm()->markAsSpam(SpamReason::TYPE_RECAPTCHA, 'ReCAPTCHA score check failed');
            }
        }
    }

    /**
     * @param FormRenderEvent $event
     */
    public function addRecaptchaJavascriptToForm(FormRenderEvent $event)
    {
        $form  = $event->getForm();
        $model = $this->getSettings();
        if (!$model->recaptchaEnabled) {
            return;
        }

        if ($model->isInvisibleRecaptchaSetUp() && !$form->isRecaptchaEnabled()) {
            return;
        }

        $alias = \Yii::getAlias('@freeform');

        $recaptchaKey = \Craft::parseEnv($model->recaptchaKey);

        switch ($model->getRecaptchaType()) {
            case Settings::RECAPTCHA_TYPE_V3:
                $scriptJs = file_get_contents($alias . '/Resources/js/other/pro/form/recaptcha-v3.js');

                $event->appendJsToOutput(
                    $scriptJs,
                    [
                        'formAnchor' => $event->getForm()->getAnchor(),
                        'siteKey'    => $recaptchaKey,
                        'action'     => $event->getForm()->getCustomAttributes()->getRecaptchaAction() ?? 'homepage',
                    ]
                );

                $event->appendToOutput(
                    '<input type="hidden" name="g-recaptcha-response" value="" />'
                );
                break;

            case Settings::RECAPTCHA_TYPE_V2_INVISIBLE:
                $scriptJs = file_get_contents($alias . '/Resources/js/other/pro/form/recaptcha-v2-invisible.js');
                $event->appendJsToOutput(
                    $scriptJs,
                    [
                        'formAnchor' => $event->getForm()->getAnchor(),
                        'siteKey'    => $recaptchaKey,
                    ]
                );

                $event->appendToOutput(
                    sprintf(
                        '<div data-recaptcha-invisible 
                            class="g-recaptcha"
                            data-sitekey="%s" 
                            data-callback="updateRecaptchaToken" 
                            data-size="invisible"
                            ></div>',
                        $recaptchaKey
                    )
                );
                break;

            case Settings::RECAPTCHA_TYPE_V2_CHECKBOX:
            default:
                if ($event->getForm()->getLayout()->hasRecaptchaFields()) {
                    $scriptJs = file_get_contents($alias . '/Resources/js/other/pro/form/recaptcha-v2-checkbox.js');
                    $event->appendJsToOutput($scriptJs, ['formAnchor' => $event->getForm()->getAnchor()]);
                }

                break;
        }
    }

    /**
     * @return Settings
     */
    private function getSettings(): Settings
    {
        return Freeform::getInstance()->settings->getSettingsModel();
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isRecaptchaTypeSkipped(string $type): bool
    {
        return !$this->getSettings()->recaptchaEnabled || $this->getSettings()->getRecaptchaType() !== $type;
    }

    /**
     * @return bool
     */
    private function behaviourDisplayError(): bool
    {
        return $this->getSettings()->recaptchaBehaviour === Settings::RECAPTCHA_BEHAVIOUR_DISPLAY_ERROR || !$this->getSettings()->spamFolderEnabled;
    }

    /**
     * @return bool
     */
    private function validateResponse(): bool
    {
        $response = \Craft::$app->request->post('g-recaptcha-response');
        if (!$response) {
            return false;
        }

        $secret = \Craft::parseEnv($this->getSettings()->recaptchaSecret);

        $client   = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => \Craft::$app->request->getRemoteIP(),
            ],
        ]);

        $result = \GuzzleHttp\json_decode((string) $response->getBody(), true);

        if (isset($result['score'])) {
            $minScore = $this->getSettings()->recaptchaMinScore;
            $minScore = min(1, $minScore);
            $minScore = max(0, $minScore);

            return $result['score'] >= $minScore;
        }

        return $result['success'];
    }
}
