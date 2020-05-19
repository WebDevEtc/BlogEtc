<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use WebDevEtc\BlogEtc\Scopes\BlogCommentApprovedAndDefaultOrderScope;

/**
 * Legacy class for backwards compatibility.
 * @deprecated
 */
class BlogEtcComment extends Comment
{

}
