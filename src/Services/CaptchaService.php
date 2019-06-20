<?php

namespace WebDevEtc\BlogEtc\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use WebDevEtc\BlogEtc\Captcha\CaptchaAbstract;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Repositories\BlogEtcCategoriesRepository;

class CaptchaService
{
    /**
     * Return either null (if captcha is not enabled), or the captcha object (which should implement CaptchaInterface interface / extend the CaptchaAbstract class)
     *
     *
     * @return null|CaptchaAbstract
     */
    public function getCaptchaObject(): ?CaptchaAbstract
    {
        if (!config('blogetc.captcha.captcha_enabled')) {
            return null;
        }

        // else: captcha is enabled
        /** @var string $captcha_class */
        $captcha_class = config('blogetc.captcha.captcha_type');

        return new $captcha_class;
    }
}

