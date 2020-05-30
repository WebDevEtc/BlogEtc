<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Gate;
use Illuminate\Http\Response;
use RuntimeException;
use WebDevEtc\BlogEtc\Gates\GateTypes;
use WebDevEtc\BlogEtc\Requests\AddNewCommentRequest;
use WebDevEtc\BlogEtc\Services\CaptchaService;
use WebDevEtc\BlogEtc\Services\CommentsService;
use WebDevEtc\BlogEtc\Services\PostsService;

/**
 * Class BlogEtcCommentWriterController.
 */
class CommentsController extends Controller
{
    /** @var PostsService */
    private $postsService;
    /** @var CommentsService */
    private $commentsService;
    /** @var CaptchaService */
    private $captchaService;

    /**
     * BlogEtcCommentWriterController constructor.
     *
     * Note: The comment itself and the form are not in this controller but are sent as part of the
     * main PostsController::show() response.
     *
     * This class deals only with the storing of new comments.
     */
    public function __construct(
        PostsService $postsService,
        CommentsService $commentsService,
        CaptchaService $captchaService
    ) {
        $this->postsService = $postsService;
        $this->commentsService = $commentsService;
        $this->captchaService = $captchaService;
    }

    /**
     * @deprecated - use store instead
     */
    public function addNewComment(AddNewCommentRequest $request, $slug)
    {
        return $this->store($request, $slug);
    }

    /**
     * Let a guest (or logged in user) submit a new comment for a blog post.
     */
    public function store(AddNewCommentRequest $request, $slug)
    {
        if (CommentsService::COMMENT_TYPE_BUILT_IN !== config('blogetc.comments.type_of_comments_to_show')) {
            throw new RuntimeException('Built in comments are disabled');
        }

        if (Gate::denies(GateTypes::ADD_COMMENTS)) {
            abort(Response::HTTP_FORBIDDEN, 'Unable to add comments');
        }

        $blogPost = $this->postsService->repository()->findBySlug($slug);

        $captcha = $this->captchaService->getCaptchaObject();

        if ($captcha && method_exists($captcha, 'runCaptchaBeforeAddingComment')) {
            $captcha->runCaptchaBeforeAddingComment($request, $blogPost);
        }

        $comment = $this->commentsService->create(
            $blogPost,
            $request->validated(),
            $request->ip(),
            (int) Auth::id()
        );

        return response()->view('blogetc::saved_comment', [
            'captcha'     => $captcha,
            'blog_post'   => $blogPost,
            'new_comment' => $comment,
        ])->setStatusCode(Response::HTTP_CREATED);
    }
}
