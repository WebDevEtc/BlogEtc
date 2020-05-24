<?php

namespace WebDevEtc\BlogEtc\Captcha;

/**
 * Trait UsesCaptcha.
 *
 * For instantiating the config("blogetc.captcha.captcha_type") object.
 */
trait UsesCaptcha
{
    /**
     * Return either null (if captcha is not enabled), or the captcha object (which should implement CaptchaInterface interface / extend the CaptchaAbstract class).
     *
     * @return CaptchaAbstract|null
     */
    private function getCaptchaObject()
    {
        if (!config('blogetc.captcha.captcha_enabled')) {
            return;
        }

        /** @var string $captcha_class */
        $captcha_class = config('blogetc.captcha.captcha_type');

        return new $captcha_class();
    }
}
