<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Services\BlogEtcCommentsService;

/**
 * Class BlogEtcCommentsAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCommentsAdminController extends Controller
{
    /**
     * @var BlogEtcCommentsService
     */
    private $service;

    /**
     * BlogEtcCommentsAdminController constructor.
     * @param BlogEtcCommentsService $service
     */
    public function __construct(BlogEtcCommentsService $service)
    {
        $this->service = $service;

        $this->middleware(UserCanManageBlogPosts::class);
    }

    /**
     * Show all comments (and show buttons with approve/delete)
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //TODO - use service
        $comments = BlogEtcComment::withoutGlobalScopes()->orderBy('created_at', 'desc')
            ->with('post');

        if ($request->get('waiting_for_approval')) {
            $comments->where('approved', false);
        }

        $comments = $comments->paginate(100);

        return view('blogetc_admin::comments.index', ['comments'=>$comments]);
    }

    /**
     * Approve a comment
     *
     * @param $blogCommentID
     * @return RedirectResponse
     */
    public function approve($blogCommentID): RedirectResponse
    {
        $this->service->approve($blogCommentID);

        Helpers::flashMessage('Approved!');

        return back();
    }

    /**
     * Delete a submitted comment
     *
     * @param $blogCommentID
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy($blogCommentID): RedirectResponse
    {
        $this->service->delete($blogCommentID);
        Helpers::flashMessage('Deleted!');

        return back();
    }
}
