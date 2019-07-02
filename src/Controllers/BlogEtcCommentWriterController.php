<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Requests\AddNewCommentRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcCommentsService;
use WebDevEtc\BlogEtc\Services\BlogEtcPostsService;
use WebDevEtc\BlogEtc\Services\CaptchaService;

/**
 * Class BlogEtcCommentWriterController
 *
 * Let public write comments
 *
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCommentWriterController extends Controller
{
    /** @var BlogEtcPostsService */
    private $postsService;
    /** @var BlogEtcCommentsService */
    private $commentsService;
    /** @var CaptchaService */
    private $captchaService;

    /**
     * BlogEtcCommentWriterController constructor.
     * @param BlogEtcPostsService $postsService
     * @param BlogEtcCommentsService $commentsService
     * @param CaptchaService $captchaService
     */
    public function __construct(
        BlogEtcPostsService $postsService,
        BlogEtcCommentsService $commentsService,
        CaptchaService $captchaService
    ) {
        $this->postsService = $postsService;
        $this->commentsService = $commentsService;
        $this->captchaService = $captchaService;
    }

    /**
     * Let a guest (or logged in user) submit a new comment for a blog post
     *
     * @param AddNewCommentRequest $request
     * @param $slug
     * @return Factory|View
     * @throws Exception
     */
    public function addNewComment(AddNewCommentRequest $request, string $slug)
    {
        if (config('blogetc.comments.type_of_comments_to_show', 'built_in') !== 'built_in') {
            throw new RuntimeException('Built in comments are disabled');
        }
        $blogPost = $this->postsService->repository()->findBySlug($slug);

        $captcha = $this->captchaService->getCaptchaObject();

        if ($captcha) {
            $captcha->runCaptchaBeforeAddingComment($request, $blogPost);
        }

        $comment = $this->commentsService->create(
            $blogPost,
            $request->validated(),
            $request->ip(),
            Auth::id()
        );

        return view('blogetc::saved_comment', [
            'captcha' => $captcha,
            'blog_post' => $blogPost,
            'new_comment' => $comment,
        ]);
    }
}
