<?php

namespace WebDevEtc\BlogEtc\Factories;

/* @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

$factory->define(Comment::class, static function (Faker $faker) {
    return [
        'blog_etc_post_id' => static function () {
            return factory(Post::class)->create()->id;
        },
        'user_id'     => null,
        'ip'          => $faker->ipv4,
        'author_name' => $faker->name,
        'comment'     => $faker->sentence,
        'approved'    => true,
    ];
});
