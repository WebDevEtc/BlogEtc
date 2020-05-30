@switch(config("blogetc.comments.type_of_comments_to_show","built_in"))

    @case("built_in")
    @include("blogetc::partials.built_in_comments")
    @include("blogetc::partials.add_comment_form")
    @break

    @case("disqus")
    @include("blogetc::partials.disqus_comments")
    @break


    @case("custom")
    @include("blogetc::partials.custom_comments")
    @break

    @case("disabled")
    <?php
    return;  // not required, as we already filter for this
    ?>
    @break

    @default
    <div class="alert alert-danger">
        Invalid comment <code>type_of_comments_to_show</code> config option
    </div>
@endswitch
