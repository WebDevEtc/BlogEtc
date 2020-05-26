
# Webdevetc BlogEtc - Complete Laravel Blog Package

 - Quickly add a blog with admin panel to your existing Laravel project. It has everything included (routes, views, controllers, middleware, etc)
 - Works with latest version of Laravel.

<p align="center">
  <a href="https://travis-ci.org/WebDevEtc/BlogEtc">
    <img src="https://travis-ci.org/WebDevEtc/BlogEtc.svg?branch=master" alt="Build Status">
  </a>

  <a href="https://github.styleci.io/repos/144829997">
    <img src="https://github.styleci.io/repos/144829997/shield?branch=master" alt="StyleCI">
  </a>

   <a href="https://packagist.org/packages/WebDevEtc/BlogEtc">
      <img src="https://poser.pugx.org/WebDevEtc/BlogEtc/v/stable.png" alt="Latest Stable Version">
  </a>

  <a href="https://packagist.org/packages/WebDevEtc/BlogEtc">
      <img src="https://poser.pugx.org/WebDevEtc/BlogEtc/downloads.png" alt="Total Downloads">
  </a>

  <a href="https://packagist.org/packages/WebDevEtc/BlogEtc">
    <img src="https://poser.pugx.org/WebDevEtc/BlogEtc/license.png" alt="License">
  </a>
</p>


# Next version - coming soon

 - A rewrite of a lot of the code will be released soon. The work for this is now on the `blogetc-next-version` branch. Code there is liable to change. 
 - May 2020: I am importing some of the changes from next version (including tests). I am slowly introducing these new features. Tests currently have a few skipped tests just to make migration of test code later a bit easier. All changes will be fully backwards compatible during the next few releases.
                                                                                           
This is [WebDevEtc's](https://webdevetc.com/) BlogEtc package. It has everything you need to quickly and easily add a blog to your laravel app.

### For installation instructions please read [the Laravel blog install guide here](https://webdevetc.com/laravel/packages/blogetc-blog-system-for-your-laravel-app/help-documentation/laravel-blog-package-blogetc#install_guide)

[Install guide](https://webdevetc.com/laravel/packages/blogetc-blog-system-for-your-laravel-app/help-documentation/laravel-blog-package-blogetc#install_guide) • [Packagist](https://packagist.org/packages/webdevetc/blogetc) << They're simple, but must be followed.

## Features

- Includes all views, routes, models, controllers, events, etc
  - Public facing pages:
    - View all posts (paginated)
    - View all posts in category (paginated)
    - View single post
    - Add comment views / confirmation views
    - Search (full text search), search form, search results page.
  - Admin pages:
    - Posts **(CRUD Blog Posts, Upload Featured Images (auto resizes)**
      - View all posts,
      - Create new post,
      - Edit post,
      - Delete post
    - Categories **(CRUD Post Categories)**
      - View all categories,
      - Create new category,
      - Edit post,
      - Delete post
    - Comments **(including comment approvals)**
      - View all comments,
      - Approve/Moderate comment,
      - Delete comment
    - Upload images
      - as well as uploading featured images for each blog post (and auto resizing to multiple defined sizes), you can upload images separately.
      - view all uploaded images (in multiple sizes)
- **Includes admin panel**
  - Create / edit posts
  - Create / edit post categories
  - Manage (approve/delete) submitted comments
- Allows each blog post to have featured images uploaded (you can define the actual dimensions) - in large, medium, thumbnail sizes
- fully configurable via its `config/blogetc.php` config file.
- **Includes all required view files, works straight away with no additional setup.** All view files (Blade files) use Bootstrap 4, and very clean HTML (easy to get your head around). You can easily override any view file by putting files in your `/resources/views/vendor/blogetc/` directory
- **Built in comments (using the database)**, can auto approve or require admin approval (comment moderation).
  - Other options include using [Disqus](http://disqus.com/) comments or disabling comments.
- Includes unit/feature tests, run automatically on Travis CI.
- Fires events for any database changes, so you can easily add Event Listeners if you need to add additional logic.
- **< 5 minute install time** and your blog is up and working, ready for you to go to the admin panel and write a blog post - see full details below, but this is a summary of the required steps:
   - install with composer,
   - do the database migration, copy the config file over (done with `php artisan vendor:publish`)
   - chmod/chown the `public/blog_images/` directory so featured images can be uploaded for each blog post
   - and then add 1 method to your `\App\User` file (`canManageBlogEtcPosts()`) (this will change to using gates soon but will be backwards compatible)
   - __but please see the install instructions to get everything up and working__

## How to customise the blog views/templates

This is easy to do, and further details can be found in our  [BlogEtc Laravel Blog Package Documentation](https://webdevetc.com/laravel/packages/blogetc-blog-system-for-your-laravel-app/help-documentation/laravel-blog-package-blogetc#guide_to_views).

After running the `vendor:publish` command, all of the default template files will be found in `/resources/views/vendor/blogetc/` and are easy to edit to match your needs.

## Missing /auth/register?

If you are installing on a fresh install of Laravel (which no longer includes auth built in) then the following must be ran:
 
```
composer require laravel/ui;
php artisan ui vue --auth;
``` 

## Issues, support, bug reports, security issues

Please contact me on the contact from on [WebDev Etc](https://webdevetc.com/) or on [twitter](https://twitter.com/web_dev_etc/) and I'll get back to you asap.
