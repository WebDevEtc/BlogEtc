<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Services\CommentsService;

/**
 * Class BlogEtcCommentsAdminController.
 */
class ManageCommentsController extends Controller
{
    /** @var CommentsService */
    private $service;

    /**
     * BlogEtcCommentsAdminController constructor.
     */
    public function __construct(CommentsService $service)
    {
        $this->middleware(UserCanManageBlogPosts::class);
        $this->service = $service;
    }

    /**
     * Show all comments (and show buttons with approve/delete).
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        //TODO - use service
        $comments = Comment::withoutGlobalScopes()
            ->orderBy('created_at', 'desc')
            ->with('post');

        if ($request->get('waiting_for_approval')) {
            $comments->where('approved', false);
        }

        $comments = $comments->paginate(100);

        return view('blogetc_admin::comments.index', ['comments' => $comments]);
    }

    /**
     * Approve a comment.
     *
     * @param $blogCommentID
     */
    public function approve(int $blogCommentID): RedirectResponse
    {
        $this->service->approve($blogCommentID);

        Helpers::flashMessage('Approved comment!');

        return back();
    }

    /**
     * Delete a submitted comment.
     *
     * @param $blogCommentID
     *
     * @throws Exception
     */
    public function destroy(int $blogCommentID): RedirectResponse
    {
        $this->service->delete($blogCommentID);

        Helpers::flashMessage('Deleted comment!');

        return back();
    }
}
