<? namespace WebDevEtc\BlogEtc\Captcha;

use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Interfaces\CaptchaInterface;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

abstract class CaptchaAbstract implements CaptchaInterface
{


    /**
     * executed when viewing single post
     *
     * @param Request $request
     * @param BlogEtcPost $blogEtcPost
     *
     * @return void
     */
    public function runCaptchaBeforeShowingPosts(Request $request, BlogEtcPost $blogEtcPost)
    {
        // no code here to run! Maybe in your subclass you can make use of this?
    }

    /**
     * executed when posting new comment
     *
     * @param Request $request
     * @param BlogEtcPost $blogEtcPost
     *
     * @return void
     */
    public function runCaptchaBeforeAddingComment(Request $request, BlogEtcPost $blogEtcPost)
    {
        // no code here to run! Maybe in your subclass you can make use of this?
    }

}