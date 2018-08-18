{{--Used on the index page (so shows a small summary--}}

<div class="card my-5 mx-auto col-md-4">
    <div class="card-body">
        <?=$post->image_tag("thumbnail", 'float-right m-1'); ?>
        <h5 class='card-title'><a href='{{$post->url()}}'>{{$post->title}}</a></h5>
        <h5 class='card-subtitle mb-2 text-muted'>{{$post->subtitle}}</h5>
        <p class="card-text">{{$post->html}}</p>



        <a href="{{$post->url()}}" class="card-link btn btn-outline-secondary">View Post</a>
    </div>
</div>
