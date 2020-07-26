
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
  
  <a href="https://scrutinizer-ci.com/g/WebDevEtc/BlogEtc/?branch=master">
      <img src="https://scrutinizer-ci.com/g/WebDevEtc/BlogEtc/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality" />
  </a>
  
  <a href="https://scrutinizer-ci.com/g/WebDevEtc/BlogEtc/?branch=master">
      <img src="https://scrutinizer-ci.com/g/WebDevEtc/BlogEtc/badges/coverage.png?b=master" alt="Code Coverage" />
  </a>
</p>

# Recent changes (May/June 2020) including recent installation instructions:

 - This package no longer uses `\App\User::canManageBlogEtcPosts()` to check if a user can access the admin panel. 
 - Instead it now uses a Laravel gate. This is currently backwards compatible without any edits. 
 - For new installations please add the following to `App\Providers\AuthServiceProvider`:
 
 ```php
    Gate::define(GateTypes::MANAGE_BLOG_ADMIN, static function (?Model $user) {
        // Implement your logic here, for example:
        return $user && $user->email === 'your-admin-user@your-site.com';
        // Or something like `$user->is_admin === true`
    });
```

 - The old way (using the `canManageBlogEtcPosts()` method on User.php) will still work but it is not recommended. At some point in the future it will be removed.
 
 (Readme on webdevetc.com will be updated soon)
                                                                                           
## Blog Package for Laravel                                                                                           

This is [WebDevEtc's](https://webdevetc.com/) [BlogEtc Blog package for Laravel](https://webdevetc.com/blogetc). It has everything you need to quickly and easily add a blog to your laravel app.

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
   - and then add a gate to AuthServiceProvider (see note above)
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
