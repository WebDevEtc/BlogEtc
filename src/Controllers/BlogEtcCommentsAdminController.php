<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Events\CommentApproved;
use WebDevEtc\BlogEtc\Events\CommentWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

/**
 * Class BlogEtcCommentsAdminController.
 */
class BlogEtcCommentsAdminController extends Controller
{
    /**
     * BlogEtcCommentsAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
    }

    /**
     * Show all comments (and show buttons with approve/delete).
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $comments = BlogEtcComment::withoutGlobalScopes()->orderBy('created_at', 'desc')
            ->with('post');

        if ($request->get('waiting_for_approval')) {
            $comments->where('approved', false);
        }

        $comments = $comments->paginate(100);

        return view('blogetc_admin::comments.index')
            ->withComments($comments
            );
    }

    /**
     * Approve a comment.
     *
     * @param $blogCommentId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($blogCommentId)
    {
        $comment = BlogEtcComment::withoutGlobalScopes()->findOrFail($blogCommentId);
        $comment->approved = true;
        $comment->save();

        Helpers::flashMessage('Approved!');
        event(new CommentApproved($comment));

        return back();
    }

    /**
     * Delete a submitted comment.
     *
     * @param $blogCommentId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($blogCommentId)
    {
        $comment = BlogEtcComment::withoutGlobalScopes()->findOrFail($blogCommentId);
        event(new CommentWillBeDeleted($comment));

        $comment->delete();

        Helpers::flashMessage('Deleted!');

        return back();
    }
}
