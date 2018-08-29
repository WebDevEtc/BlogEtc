<?php namespace WebDevEtc\BlogEtc\Captcha;
use WebDevEtc\BlogEtc\Interfaces\CaptchaInterface;

/**
 * Class Basic
 * @package WebDevEtc\BlogEtc\Captcha
 */
class Basic implements CaptchaInterface
{

    /**
     * What should the field name be (in the <input type='text' name='????'>)
     *
     * @return string
     */
    public function captcha_field_name()
    {
        return 'captcha';
    }

    /**
     * What view file should we use for the captcha field?
     *
     * @return string
     */
    public function view()
    {
        return 'blogetc::captcha.basic';
    }

    /**
     * What rules should we use for the validation for this field?
     *
     * Enter the rules here, along with captcha validation.
     *
     * @return array
     */
    public function rules()
    {

        return [

            'required',
            'string',

            function ($attribute, $value, $fail) {

                $answers = config("blogetc.captcha.basic_answers");
                // strtolower everything
                $value = strtolower(trim($value));
                $answers = strtolower($answers);

                $answers_array = array_map("trim", explode(",", $answers));
                if (!in_array($value, $answers_array)) {
                    return $fail('The captcha field is incorrect.');
                }
            },

        ];

    }
}