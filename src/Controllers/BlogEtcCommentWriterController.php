<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Captcha\CaptchaAbstract;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Events\CommentAdded;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Requests\AddNewCommentRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcPostsService;

/**
 * Class BlogEtcCommentWriterController
 *
 * Let public write comments
 *
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCommentWriterController extends Controller
{
    use UsesCaptcha;

    /**
     * @var BlogEtcPostsService
     */
    private $service;

    public function __construct(BlogEtcPostsService $service)
    {
        $this->service = $service;
    }

    /**
     * Let a guest (or logged in user) submit a new comment for a blog post
     *
     * @param AddNewCommentRequest $request
     * @param $slug
     * @return Factory|View
     * @throws Exception
     */
    public function addNewComment(AddNewCommentRequest $request,string $slug)
    {
        if (config('blogetc.comments.type_of_comments_to_show', 'built_in') !== 'built_in') {
            throw new RuntimeException('Built in comments are disabled');
        }

        $blogPost = $this->service->repository()->findBySlug($slug);

        /** @var CaptchaAbstract $captcha */
        $captcha = $this->getCaptchaObject();
        if ($captcha) {
            $captcha->runCaptchaBeforeAddingComment($request, $blogPost);
        }

        $new_comment = $this->createNewComment($request, $blogPost);

        return view('blogetc::saved_comment', [
            'captcha' => $captcha,
            'blog_post' => $blogPost,
            'new_comment' => $new_comment,
        ]);
    }

    /**
     * @param AddNewCommentRequest $request
     * @param $blog_post
     * @return BlogEtcComment
     */
    protected function createNewComment(AddNewCommentRequest $request, $blog_post):BlogEtcComment
    {
        $newComment = new BlogEtcComment($request->validated());

        if (config('blogetc.comments.save_ip_address')) {
            $newComment->ip = $request->ip();
        }
        if (config('blogetc.comments.ask_for_author_website')) {
            $newComment->author_website = $request->get('author_website');
        }
        if (config('blogetc.comments.ask_for_author_website')) {
            $newComment->author_email = $request->get('author_email');
        }
        if (config('blogetc.comments.save_user_id_if_logged_in', true) && Auth::check()) {
            $newComment->user_id = Auth::user()->id;
        }

        $newComment->approved = config('blogetc.comments.auto_approve_comments', true) ? true : false;

        $blog_post->comments()->save($newComment);

        event(new CommentAdded($blog_post, $newComment));

        return $newComment;
    }

}
