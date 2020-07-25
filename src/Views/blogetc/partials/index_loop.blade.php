@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
{{--Used on the index page (so shows a small summary--}}
{{--See the guide on webdevetc.com for how to copy these files to your /resources/views/ directory--}}
{{--https://webdevetc.com/laravel/packages/blogetc-blog-system-for-your-laravel-app/help-documentation/laravel-blog-package-blogetc#guide_to_views--}}

<div style="max-width:600px; margin: 50px auto; background: #fffbea;border-radius:3px;padding:0;">

    <div class="text-center">
        {!! $post->imageTag('medium', true, '') !!}
    </div>
    <div style="padding:10px;">
        <h3><a href="{{$post->url()}}">{{$post->title}}</a></h3>
        <h5>{{$post->subtitle}}</h5>

        @if(config('blogetc.show_full_post_on_index'))
            {!! $post->renderBody() !!}
        @else
            <p>{!! $post->generateIntroduction(400) !!}</p>
        @endif

        <div class="text-center">
            <a href="{{ $post->url() }}" class="btn btn-primary">View Post</a>
        </div>
    </div>
</div>

