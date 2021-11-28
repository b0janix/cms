<?php

use App\Models\Post;
use Database\Seeders\PostSeeder;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreatePostsTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Check whether the factory inserts the posts
     * @return void
     */
    public function test_run_posts_factory()
    {
        (new PostSeeder)->run();

        $posts = Post::all()->toArray();
        $this->assertEquals(5, count($posts));
    }

}
