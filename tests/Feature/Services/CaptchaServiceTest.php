<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Config;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Captcha\Basic;
use WebDevEtc\BlogEtc\Services\CaptchaService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class CaptchaServiceTest extends TestCase
{
    use WithFaker;

    /** @var CaptchaService */
    private $captchaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();

        $this->captchaService = resolve(CaptchaService::class);
    }

    public function testGetCaptchaObjectDisabled(): void
    {
        Config::set('blogetc.captcha.captcha_enabled', false);

        $result = $this->captchaService->getCaptchaObject();

        $this->assertNull($result);
    }

    public function testGetCaptchaObjectEnabled(): void
    {
        Config::set('blogetc.captcha.captcha_enabled', true);
        Config::set('blogetc.captcha.captcha_type', Basic::class);

        $result = $this->captchaService->getCaptchaObject();

        $this->assertInstanceOf(Basic::class, $result);
    }
}
