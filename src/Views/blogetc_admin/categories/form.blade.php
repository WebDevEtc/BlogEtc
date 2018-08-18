<div class="form-group">
    <label for="category_category_name">Category Name</label>
    <input type="text" class="form-control" id="category_category_name" aria-describedby="category_category_name_help" name='category_name'
           value="{{old("category_name",$category->category_name)}}">
    <small id="category_category_name_help" class="form-text text-muted">The name of the category</small>
</div>


<div class="form-group">
    <label for="category_slug">Category slug</label>
    <input type="text" class="form-control" id="category_slug" aria-describedby="category_slug_help" name='slug'
           value="{{old("slug",$category->slug)}}">
    <small id="category_slug_help" class="form-text text-muted">The slug
        i.e. {{route("blogetc.view_category","")}}/<u><em>this_part</em></u>. This must be unique to this category.</small>
</div>



