@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
<div id="disqus_thread"></div>

<script>
    /**
     *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
     *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables*/
    var disqus_config = function () {
        this.page.url = "{{ $post->url() }}";  // Replace PAGE_URL with your page's canonical URL variable
        this.page.identifier = "{{ $post->slug }}"; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
    };
    (function () { // DON'T EDIT BELOW THIS LINE
        var d = document, s = d.createElement('script');
        s.src = '{{config('blogetc.comments.disqus.src_url','ENTER YOUR URL HERE!!!')}}';
        s.setAttribute('data-timestamp', + new Date());
        (d.head || d.body).appendChild(s);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus</a>,
    and run by <a href="https://webdevetc.com/">WebDevEtc's BlogEtc Laravel Blog package</a></noscript>

