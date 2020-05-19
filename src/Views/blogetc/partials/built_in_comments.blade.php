@php
    /** @var \WebDevEtc\BlogEtc\Models\Comment[] $comments */
@endphp
@forelse($comments as $comment)
    <div class="card bg-light mb-3">
        <div class="card-header">
            {{ $comment->author() }}
            @if(config('blogetc.comments.ask_for_author_website') && $comment->author_website)
                (<a href="{{ $comment->author_websitae }}" target="_blank" rel="noopener">website</a>)
            @endif
            <span class="float-right" title="{{ $comment->created_at}}">
                <small>{{ $comment->created_at->diffForHumans() }}</small>
            </span>
        </div>
        <div class="card-body bg-white">
            <p class="card-text">{!! nl2br(e($comment->comment)) !!}</p>
        </div>
    </div>
@empty
    <div class="alert alert-info">
        No comments yet! Why don't you be the first?
    </div>
@endforelse

@if(count($comments) >= config('blogetc.comments.max_num_of_comments_to_show', 500))
    <p>
        <em>Only the first {{ config('blogetc.comments.max_num_of_comments_to_show', 500) }} comments are
            shown.</em>
    </p>
@endif


