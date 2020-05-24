@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
<div id="disqus_thread"></div>

<script>
  var disqus_config = function() {
    this.page.url = "{{ $post->url() }}";
    this.page.identifier = "{{ $post->slug }}";
  };
  (function() {
    var d = document, s = d.createElement('script');
    s.src = '{{config('blogetc.comments.disqus.src_url','ENTER YOUR URL HERE!!!')}}';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
  })();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus</a>,
    and run by <a href="https://webdevetc.com/">WebDevEtc's BlogEtc Laravel Blog package</a></noscript>

