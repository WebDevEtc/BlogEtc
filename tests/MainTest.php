<?php

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use WebDevEtc\BlogEtc\Captcha\Basic;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

class MainTest extends TestCase
{

//    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /*
     *
     *
     *

    This is one great big huge test file that checks most of the features (not 100% yet) of this package.


    List of all main routes, and if they are covered by any tests:
    There might be some additional tests still to be written (For example we create a new post, but don't assign any categories to it at the moment)

    UNTESTED - todo:
    Testing the author_email, author_website.

    /blog/...
        blogetc.index                       YES
        blogetc.feed                        YES
        blogetc.view_category               no - but this is basically blogetc.index
        blogetc.single                      YES
        blogetc.comments.add_new_comment    YES - tested multiple times with/without basic captcha on/off/correct/incorrect.
                                                Also tested with diff configs for comment form:
                                                    disabled
                                                    built_in
                                                    disqus
                                                    custom

    /blog_admin/...
        blogetc.admin.index                 YES
        blogetc.admin.create_post           no - but is just a form
        blogetc.admin.store_post            YES
        blogetc.admin.edit_post             YES - but no extra checks
        blogetc.admin.update_post           YES
        blogetc.admin.destroy_post          YES

     /blog_admin/comments/...

            blogetc.admin.comments.index    YES
            blogetc.admin.comments.approve  YES
            blogetc.admin.comments.delete   YES

     /blog_admin/categories/...

            blogetc.admin.categories.index
            blogetc.admin.categories.create_category    no - but is just a form
            blogetc.admin.categories.store_category     YES
            blogetc.admin.categories.edit_category      no - but is just a form
            blogetc.admin.categories.update_category
            blogetc.admin.categories.destroy_category   YES



     *
     *
     *
     */

    public function testFilesArePresent()
    {
        $this->assertFileExists(config_path('blogetc.php'),
            '/config/blogetc.php should exist - currently no file with that filename is found');
        $this->assertTrue(is_array(include config_path('blogetc.php')),
            '/config/blogetc.php should exist - currently no file with that filename is found');
    }

    public function testImageSizesAreSane()
    {

        $this->assertTrue(count(config('blogetc.image_sizes')) >= 3);

        foreach (config('blogetc.image_sizes') as $image_key => $image_info) {

            $this->assertArrayHasKey('w', $image_info);
            $this->assertArrayHasKey('h', $image_info);
            $this->assertArrayHasKey('name', $image_info);
            $this->assertArrayHasKey('enabled', $image_info);
            $this->assertArrayHasKey('basic_key', $image_info);

            $this->assertTrue(is_bool($image_info['enabled']));
            $this->assertTrue(is_int($image_info['w']));
            $this->assertTrue(is_int($image_info['h']));
            $this->assertTrue(is_string($image_info['name']));
            $this->assertTrue(is_string($image_info['basic_key']));
            $this->assertTrue(is_string($image_key));
        }
    }

    public function testUserHasNanManageBlogEtcPostsMethod()
    {

        $this->assertTrue(method_exists(User::class, 'canManageBlogEtcPosts'),
            'Your User model must have the canManageBlogEtcPosts method');

        $user = new User();
        $this->assertTrue(is_bool($user->canManageBlogEtcPosts()));
    }

    // more tests coming soon

    public function testCanSeeAdminPanel()
    {

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        Auth::logout();
        // without a logged in user, should give error
        $response = $this->get($admin_panel_url);
        $response->assertStatus(401);

//        $user = new \App\User();

        $user = $this->create_admin_user();

        $response = $this->get($admin_panel_url);
        $response->assertStatus(200);

        // check user can see admin area:
        $this->assertTrue($user->canManageBlogEtcPosts());

        $response = $this->get($admin_panel_url);
        // check if we can see the admin panel correctly
        $response->assertStatus(200);
        $response->assertSee('All Posts');
        $response->assertSee('Add Post');
        $response->assertSee('All Comments');
        $response->assertSee('All Categories');
        $response->assertSee('Add Category');

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
//        $user=$this->create_admin_user();

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);

        $response->assertSessionHasNoErrors();
//        dump($response);

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);
    }

    /**
     * @return mixed
     */
    protected function create_admin_user()
    {

        $user = $this->getMockBuilder(User::class)
            ->getMock();
        // make sure the user can see admin panel
        $user->method('canManageBlogEtcPosts')
            ->will($this->returnCallback(function () {
                return true;
            }));

        // set up some dummy info
        $user->name = str_random() . 'testuser';
        $user->password = str_random();
        $user->email = str_random() . '@example.com';

        $this->actingAs($user);

        //get a page (for session to be set/ for csrf) - do not delete this line! We don't need to do anything with the response though
        $this->get('/');

        return $user;
    }

    /**
     * @return array
     */
    protected function generate_basic_blog_post_with_random_data()
    {
        $newObjectAttributes = [];

        foreach ([
                     'title',
                     'subtitle',
                     'slug',
                     'post_body',
                     'meta_desc',
                 ] as $field) {
            $newObjectAttributes[$field] = str_random();
        }
        return $newObjectAttributes;
    }

    public function testCanCreatePost()
    {

        $user = $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);
    }

    public function testCanCreatePostThenEditIt()
    {

        $user = $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        $justCreatedRow = BlogEtcPost::where('slug', $newObjectAttributes['slug'])
            ->firstOrFail();

        $newObjectAttributes['title'] = 'New title ' . str_random();
        $this->assertDatabaseMissing('blog_etc_posts', ['title' => $newObjectAttributes['title']]);
        $response = $this->patch($admin_panel_url . '/edit_post/' . $justCreatedRow->id, $newObjectAttributes);
        $response->assertStatus(302);
        $this->assertDatabaseHas('blog_etc_posts', ['title' => $newObjectAttributes['title']]);
    }

    public function testCreatePostThenCheckIsViewableToPublic()
    {

        $user = $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        $newObjectAttributes['slug'] = 'slug123' . str_random();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);

        // check we don't see it at moment
        $response = $this->get(config('blogetc.blog_prefix', 'blog'));
        $response->assertDontSee($newObjectAttributes['slug']);

        // must clear the cache, as the /feed is cached
        Artisan::call('cache:clear');

        $response = $this->get(config('blogetc.blog_prefix', 'blog') . '/feed');
        $response->assertDontSee($newObjectAttributes['slug']);

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        // logout - so we are guest user
        Auth::logout();
        $response = $this->get(config('blogetc.blog_prefix', 'blog'));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertSee($newObjectAttributes['slug']);

        // must clear the cache, as the /feed is cached
        Artisan::call('cache:clear');

        $response = $this->get(config('blogetc.blog_prefix', 'blog') . '/feed');
        $response->assertSee($newObjectAttributes['slug']);
        $response->assertSee($newObjectAttributes['title']);

        // now check single post is viewable

        $response = $this->get(route('blogetc.single', $newObjectAttributes['slug']));
        $response->assertStatus(200);
        $response->assertSee($newObjectAttributes['slug']);
        $response->assertSee($newObjectAttributes['title']);
    }

    public function testCreatePostWithNotPublishedThenCheckIsNotViewableToPublic()
    {

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        list($newObjectAttributes, $search_for_obj) = $this->prepare_post_creation();

        $newObjectAttributes['is_published'] = false;

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        Auth::logout();

        $response = $this->get(config('blogetc.blog_prefix', 'blog'));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($newObjectAttributes['slug']);

        // now check single post is viewable

        $response = $this->get(config('blogetc.blog_prefix', 'blog') . '/' . $newObjectAttributes['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($newObjectAttributes['slug']);
        $response->assertDontSee($newObjectAttributes['title']);
    }

    /**
     * @return array
     */
    protected function prepare_post_creation()
    {
        $user = $this->create_admin_user();

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);

        // check we don't see it at moment
        $response = $this->get(config('blogetc.blog_prefix', 'blog'));
        $response->assertDontSee($newObjectAttributes['slug']);
        return [$newObjectAttributes, $search_for_obj];
    }

    public function testCreatePostWithFuturePostedAtThenCheckIsNotViewableToPublic()
    {

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        list($newObjectAttributes, $search_for_obj) = $this->prepare_post_creation();

        $newObjectAttributes['posted_at'] = Carbon::now()->addMonths(12);

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        Auth::logout();

        $response = $this->get(config('blogetc.blog_prefix', 'blog'));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($newObjectAttributes['slug']);

        // now check single post is viewable

        $response = $this->get(config('blogetc.blog_prefix', 'blog') . '/' . $newObjectAttributes['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($newObjectAttributes['slug']);
        $response->assertDontSee($newObjectAttributes['title']);
    }

    public function testCreatePostThenCheckCanCreateCommentThenApproveCommentWithBasicCaptchaEnabledAndWrongAnswer()
    {

        Config::set('blogetc.comments.auto_approve_comments', false);
        Config::set('blogetc.captcha.captcha_enabled', true);
        Config::set('blogetc.captcha.captcha_type', Basic::class);
        $captcha = new Basic();
        Config::set('blogetc.captcha.basic_question', 'a test question');
        Config::set('blogetc.captcha.basic_answers', 'answer1,answer2');

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        Config::set('blogetc.comments.type_of_comments_to_show', 'built_in');

        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            $captcha->captcha_field_name() => 'wronganswer1', // << WRONG CAPTCHA
        ];
        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config('blogetc.blog_prefix', 'blog') . '/save_comment/' . $newObjectAttributes['slug'],
            $comment_detail);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);

        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            // << NO CAPTCHA FIELD
        ];
        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config('blogetc.blog_prefix', 'blog') . '/save_comment/' . $newObjectAttributes['slug'],
            $comment_detail);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
    }

    public function testCreatePostThenSetCommentsToDisabledAndCheckNoneShow()
    {

        Config::set('blogetc.comments.type_of_comments_to_show', 'disabled');

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new BlogEtcPost;

        $newblogpost->title = __METHOD__ . ' ' . time();

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
        foreach ($newObjectAttributes as $k => $v) {
            $newblogpost->$k = $v;
        }
        $newblogpost->save();

        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertDontSee('var disqus_config');
        $response->assertDontSee('Add a comment');
        $response->assertDontSee('Captcha');
        $response->assertDontSee('maincommentscontainer');
        $response->assertDontSee('Comments');
        $response->assertDontSee('You must customise this by creating a file');
    }

    public function testCreatePostThenSetCommentsToDisqusAndCheckDisqusJSIsShown()
    {
        Config::set('blogetc.comments.type_of_comments_to_show', 'disqus');

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new BlogEtcPost;

        $newblogpost->title = __METHOD__ . ' ' . time();

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
        foreach ($newObjectAttributes as $k => $v) {
            $newblogpost->$k = $v;
        }
        $newblogpost->save();

        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertSee('var disqus_config');
    }

    public function testCreatePostThenSetCommentsToCustomAndCheckCustomErrorShows()
    {

        Config::set('blogetc.comments.type_of_comments_to_show', 'custom');

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new BlogEtcPost;

        $newblogpost->title = __METHOD__ . ' ' . time();

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
        foreach ($newObjectAttributes as $k => $v) {
            $newblogpost->$k = $v;
        }
        $newblogpost->save();

        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertSee('You must customise this by creating a file'); //config type of comments is set to show 'custom', which should (by default) show an error messaging telling user to customise the custom view file. If this is run on a site with its own file defined this will show an error (but it will not be a real error).;
    }

    public function testCreatePostThenCheckCanCreateCommentThenApproveCommentWithBasicCaptchaEnabled()
    {

        Config::set('blogetc.comments.auto_approve_comments', false);
        Config::set('blogetc.captcha.captcha_enabled', true);
        Config::set('blogetc.captcha.captcha_type', Basic::class);
        $captcha = new Basic();
        Config::set('blogetc.captcha.basic_question', 'a test question');
        Config::set('blogetc.captcha.basic_answers', 'answer1,answer2');

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        Config::set('blogetc.comments.type_of_comments_to_show', 'built_in');

        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            $captcha->captcha_field_name() => 'AnsWer2',
        ];
        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config('blogetc.blog_prefix', 'blog') . '/save_comment/' . $newObjectAttributes['slug'],
            $comment_detail);
        $response->assertStatus(200);

        Config::set('blogetc.captcha.auto_approve_comments', false);

        $this->assertDatabaseHas('blog_etc_comments',
            ['approved' => false, 'author_name' => $comment_detail['author_name']]);

        $justAddedRow = BlogEtcComment::withoutGlobalScopes()
            ->where('author_name', $comment_detail['author_name'])->firstOrFail();

        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertSee($justAddedRow->author_name);

        // approve it:
        $response = $this->patch(route('blogetc.admin.comments.approve', $justAddedRow->id), [
            '_token' => csrf_token(),
        ]);
        // check it was approved
        $response->assertStatus(302);
        $this->assertDatabaseHas('blog_etc_comments', ['approved' => 1, 'author_name' => $justAddedRow->author_name]);
    }

    public function testCreatePostThenCheckCanCreateCommentThenApproveComment()
    {
        Config::set('blogetc.comments.auto_approve_comments', false);
        Config::set('blogetc.captcha.captcha_enabled', false);

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        Config::set('blogetc.comments.type_of_comments_to_show', 'built_in');
        Config::set('blogetc.captcha.captcha_enabled', false);

        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
        ];
        $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config('blogetc.blog_prefix', 'blog') . '/save_comment/' . $newObjectAttributes['slug'],
            $comment_detail);

        $response->assertStatus(200);

        Config::set('blogetc.captcha.auto_approve_comments', false);

        $this->assertDatabaseHas('blog_etc_comments',
            ['approved' => false, 'author_name' => $comment_detail['author_name']]);

        $justAddedRow = BlogEtcComment::withoutGlobalScopes()
            ->where('author_name', $comment_detail['author_name'])->firstOrFail();

        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertSee($justAddedRow->author_name);

        // approve it:
        $response = $this->patch(route('blogetc.admin.comments.approve', $justAddedRow->id), [
            '_token' => csrf_token(),
        ]);
        // check it was approved
        $response->assertStatus(302);
        $this->assertDatabaseHas('blog_etc_comments', ['approved' => 1, 'author_name' => $justAddedRow->author_name]);
    }

    public function testCreatePostThenCheckCanCreateCommentThenDeleteComment()
    {
        Config::set('blogetc.comments.auto_approve_comments', false);
        Config::set('blogetc.captcha.captcha_enabled', false);

        $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        if (config('blogetc.comments.type_of_comments_to_show') === 'built_in') {
            $comment_detail = [
                '_token' => csrf_token(),
                'author_name' => str_random(),
                'comment' => str_random(),
            ];
            $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
            $response = $this->post(config('blogetc.blog_prefix',
                    'blog') . '/save_comment/' . $newObjectAttributes['slug'], $comment_detail);
            $response->assertSessionHasNoErrors();
            $response->assertStatus(200);

            $this->assertDatabaseHas('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);

            $justAddedRow = BlogEtcComment::withoutGlobalScopes()
                ->where('author_name', $comment_detail['author_name'])->firstOrFail();

            // check the just added row exists...
            $response = $this->get(route('blogetc.admin.comments.index'));
            $response->assertSee($justAddedRow->author_name);

            // delete it:
            $response = $this->delete(route('blogetc.admin.comments.delete', $justAddedRow->id), [
                '_token' => csrf_token(),
            ]);
            // check it was deleted (it will deleted if approved)
            $response->assertStatus(302);

            //check it doesnt exist in database
            $this->assertDatabaseMissing('blog_etc_comments', ['id' => $justAddedRow->id,]);
        } else {
            dump("NOT TESTING COMMENT FEATURE, as config(\"blogetc.comments.type_of_comments_to_show\") is not set to 'built_in')");
        }
    }

    public function testCanCreateThenDeletePost()
    {

        $user = $this->create_admin_user();

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        $this->assertTrue($user->canManageBlogEtcPosts());

        $newObjectAttributes = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $newObjectAttributes;

        $newObjectAttributes['is_published'] = 1;
        $newObjectAttributes['posted_at'] = Carbon::now();
//        $newObjectAttributes['use_view_file'] = "";

        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . '/add_post', $newObjectAttributes);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        $justCreatedRow = BlogEtcPost::where('slug', $newObjectAttributes['slug'])
            ->firstOrFail();
        $id = $justCreatedRow->id;
        $delete_url = $admin_panel_url . '/delete_post/' . $id;

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
    }

    public function testCanCreateCategory()
    {
        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . '/categories/add_category', $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);
    }

    public function testCanCreateCategoryThenEditIt()
    {

        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');

        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];

        // create a post so we can edit it later
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . '/categories/add_category', $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);

        // get the just inserted row
        $justCreatedRow = BlogEtcCategory::where('slug', $new_cat_vals['slug'])
            ->firstOrFail();

        // get the edit page (form)
        $response = $this->get(
            $admin_panel_url . '/categories/edit_category/' . $justCreatedRow->id
        );
        $response->assertStatus(200);

        // create some edits...
        $newObjectAttributes['category_name'] = 'New category name ' . str_random();
        $newObjectAttributes['slug'] = $justCreatedRow->slug;
        $newObjectAttributes['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_categories', ['category_name' => $newObjectAttributes['category_name']]);

        // send the request to save the changes
        $response = $this->patch(
            route('blogetc.admin.categories.update_category', $justCreatedRow->id),
            $newObjectAttributes
        );

        $response->assertStatus(302); // check it was a redirect

        // check that the edited category name is in the database.
        $this->assertDatabaseHas('blog_etc_categories',
            ['slug' => $newObjectAttributes['slug'], 'category_name' => $newObjectAttributes['category_name']]);
    }

    public function testCanDeleteCategory()
    {
        $admin_panel_url = config('blogetc.admin_prefix', 'blog_admin');
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . '/categories/add_category', $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);

        $justCreatedRow = BlogEtcCategory::where('slug', $new_cat_vals['slug'])
            ->firstOrFail();
        $id = $justCreatedRow->id;

        $delete_url = $admin_panel_url . "/categories/delete_category/$id";

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
    }

    public function testUserModelHasCanManageBlogEtcPostsMethod()
    {
        $u = new User();
        $this->assertTrue(method_exists($u, 'canManageBlogEtcPosts'),
            'canManageBlogEtcPosts() must be added to User model. Please see WebDevEtc BlogEtc docs for details. It should return true ONLY for the admin users');
    }

    public function testUserModelCanManageBlogEtcPostsMethodDoesntAlwaysReturnTrue()
    {
        $u = new User();

        $u->id = 9999999; // in case the logic on canManageBlogEtcPosts() checks for a low ID
        $u->email = str_random(); // in case the logic looks for a certain email or something.

        $this->assertTrue(method_exists($u, 'canManageBlogEtcPosts'));

        // because this user is just a randomly made one, it probably should not be allowed to edit blog posts.
        $this->assertFalse($u->canManageBlogEtcPosts(),
            "User::canManageBlogEtcPosts() returns true, but it PROBABLY should return false! Otherwise every single user on your site has access to the blog admin panel! This might not be an error though, if your system doesn't allow public registration. But you should look into this. I know this isn't a good way to handle this test, but it seems to make sense.");
    }

}

