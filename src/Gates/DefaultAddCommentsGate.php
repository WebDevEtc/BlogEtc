<?php

use Illuminate\Database\Eloquent\Model;
use WebDevEtc\BlogEtc\Services\CommentsService;

return function (/* @scrutinizer ignore-unused */ ?Model $user) {
    return CommentsService::COMMENT_TYPE_BUILT_IN === config('blogetc.comments.type_of_comments_to_show');
};
