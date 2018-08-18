<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Events\CommentApproved;
use WebDevEtc\BlogEtc\Events\CommentWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

class BlogEtcCommentsAdminController extends Controller
{


    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
    }

    public function index(Request $request)
    {
        return view("blogetc_admin::comments.index")->withComments(BlogEtcComment::orderBy("created_at", "desc")->with("post")->paginate(100));
    }


    public function approve($blogCommentId)
    {
        $comment = BlogEtcComment::findOrFail($blogCommentId);
        $comment->approved = true;
        $comment->save();
        Helpers::flash_message("Approved!");
        event(new CommentApproved($comment));
        return back();
    }

    public function destroy($blogCommentId)
    {
        $comment = BlogEtcComment::findOrFail($blogCommentId);
        event(new CommentWillBeDeleted($comment));
        $comment->delete();
        Helpers::flash_message("Deleted!");
        return back();
    }


}
