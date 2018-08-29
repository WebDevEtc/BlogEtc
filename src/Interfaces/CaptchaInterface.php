<? namespace WebDevEtc\BlogEtc\Interfaces;

interface CaptchaInterface
{

    /**
     * What should the field name be (in the <input type='text' name='????'>)
     *
     * @return string
     */
    public function captcha_field_name();

    /**
     * What view file should we use for the captcha field?
     *
     * @return string
     */
    public function view();

    /**
     * What rules should we use for the validation for this field?
     *
     * @return array
     */
    public function rules();
}