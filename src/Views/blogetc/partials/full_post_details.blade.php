@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
@if(Auth::check() && Auth::user()->canManageBlogEtcPosts())
    <a href="{{$post->editUrl()}}" class="btn btn-outline-secondary btn-sm pull-right float-right">
        Edit Post
    </a>
@endif

<h2 class='blog_title'>{{$post->title}}</h2>
<h5 class='blog_subtitle'>{{$post->subtitle}}</h5>


<?=$post->imageTag('medium', false, 'd-block mx-auto'); ?>

<p class="blog_body_content">
    {!! $post->renderBody() !!}

    {{--@if(config("blogetc.use_custom_view_files")  && $post->use_view_file)--}}
    {{--                                // use a custom blade file for the output of those blog post--}}
    {{--   @include("blogetc::partials.use_view_file")--}}
    {{--@else--}}
    {{--   {!! $post->post_body !!}        // unsafe, echoing the plain html/js--}}
    {{--   {{ $post->post_body }}          // for safe escaping --}}
    {{--@endif--}}
</p>

<hr/>

@if($post->posted_at)
    Posted <strong>{{ $post->posted_at->diffForHumans() }}</strong>
@endif

@includeWhen($post->author, 'blogetc::partials.author', ['post'=>$post])
@includeWhen($post->categories, 'blogetc::partials.categories', ['post'=>$post])

