<div class="card my-5 col-md-6 mx-auto">
    <div class="card-body">


        @if(\Auth::check() && \Auth::user()->canManageBlogEtcPosts())
            <a href="{{$post->edit_url()}}" class="card-link btn btn-outline-secondary btn-sm float-right">Edit
                Post</a>
        @endif

        <h2 class='card-title'>{{$post->title}}</h2>
        <h5 class='card-subtitle mb-2 text-muted'>{{$post->subtitle}}</h5>


        <?=$post->image_tag("medium", false, 'd-block mx-auto'); ?>

        <p class="card-text">
            {!! $post->post_body_output() !!}

            {{--@if(config("blogetc.use_custom_view_files")  && $post->use_view_file)--}}
            {{--                                // use a custom blade file for the output of those blog post--}}
            {{--   @include("blogetc::partials.use_view_file")--}}
            {{--@else--}}
            {{--   {!! $post->post_body !!}        // unsafe, echoing the plain html/js--}}
            {{--   {{ $post->post_body }}          // for safe escaping --}}
            {{--@endif--}}
        </p>

        <hr/>

        Posted <strong>{{$post->posted_at->diffForHumans()}}</strong>

        @includeWhen($post->author,"blogetc::partials.author",['post'=>$post])
        @includeWhen($post->categories,"blogetc::partials.categories",['post'=>$post])
    </div>
</div>