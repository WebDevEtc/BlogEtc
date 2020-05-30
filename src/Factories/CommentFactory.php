<?php

namespace WebDevEtc\BlogEtc\Factories;

/* @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

$factory->define(Comment::class, static function (Faker $faker) {
    return [
        'blog_etc_post_id' => function() {return factory(Post::class)->create()->id;},
        'user_id' => null,
        'ip' => $faker->ipv4,
        'author_name' => $faker->name,
        'comment'=>$faker->sentence,
        'approved'=> true,
        ];
});
