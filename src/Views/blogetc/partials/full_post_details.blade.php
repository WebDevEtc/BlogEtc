<div class="card my-5 col-md-6 mx-auto">
    <div class="card-body">


        @if(\Auth::check() && \Auth::user()->canManageBlogEtcPosts())
            <a href="{{$post->edit_url()}}" class="card-link btn btn-outline-secondary btn-sm float-right">Edit
                Post</a>
        @endif

        <h5 class='card-title'><a href='{{$post->url()}}'>{{$post->title}}</a></h5>
        <h5 class='card-subtitle mb-2 text-muted'>{{$post->subtitle}}</h5>


        <?=$post->image_tag("medium", 'd-block mx-auto'); ?>

        <p class="card-text">

            @if($post->use_view_file)
                @include("blogetc::partials.use_view_file")
            @else
                {{--echos out the post_body as HTML, unescaped! --}}
                {!! $post->post_body !!}
            @endif

        </p>

        <hr/>

        Posted <strong>{{$post->posted_at->diffForHumans()}}</strong>

        @includeWhen($post->author,"blogetc::partials.author",['post'=>$post])
        @includeWhen($post->categories,"blogetc::partials.categories",['post'=>$post])
    </div>
</div>