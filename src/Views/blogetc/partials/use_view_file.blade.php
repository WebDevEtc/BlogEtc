@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
@if(View::exists($post->bladeViewFile()))
    {{--view file existed, so include it.--}}
    @include("custom_blog_posts." . $post->use_view_file, ['post' =>$post])
@else
    {{-- the view file wasn't there. Show a detailed error if user is logged in and can manage the blog, otherwise show generic error.--}}

    @can(\WebDevEtc\BlogEtc\Gates\GateTypes::MANAGE_BLOG_ADMIN)
        <div class="alert alert-danger">
            Custom blog post blade view file
            (<code>{{$post->bladeViewFile()}}</code>) not found.
            <a
                href="https://webdevetc.com/blogetc"
                target="_blank">
                See Laravel Blog Package help here.
            </a>
        </div>
    @else
        <div class="alert alert-danger">
            Sorry, but there is an error showing that blog post. Please come back later.
        </div>
    @endcan
@endif

