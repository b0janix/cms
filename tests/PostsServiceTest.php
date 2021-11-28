<?php

use App\Models\Comment;
use App\Models\Post;
use App\Services\Combinations;
use App\Services\Posts;
use Database\Seeders\CommentSeeder;
use Database\Seeders\PostSeeder;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PostsServiceTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test the posts array returned from the getPosts() method
     * @return void
     */
    public function test_get_posts()
    {
        (new PostSeeder())->run();
        $posts = (new Posts())->getPosts((new Post()), []);
        $this->assertNotEmpty($posts['data']['result']);
        $this->assertTrue(isset($posts['data']['count']));
        $this->assertEquals(5, $posts['data']['count']);
        $this->assertEquals(200, $posts['code']);
    }

    /**
     * Test the posts array returned from the getPosts() method with params
     * @return void
     */
    public function test_get_posts_with_params()
    {
        (new PostSeeder())->run();
        $posts = (new Posts())->getPosts((new Post()), ['limit' => 3, 'sort' => 'id', 'direction' => 'desc']);
        $this->assertCount(3, $posts['data']['result']['data']);
        $this->assertEquals(5, $posts['data']['result']['data'][0]->id);
        $this->assertEquals(0, $posts['data']['result']['data'][0]->inactive);
    }

    /**
     * Test the posts array returned from the getPosts() method with id param
     * @return void
     */
    public function test_get_posts_with_id_param()
    {
        (new PostSeeder())->run();
        $posts = (new Posts())->getPosts((new Post()), ['id' => 2]);
        $this->assertCount(1, $posts['data']['result']['data']);
        $this->assertEquals(2, $posts['data']['result']['data'][0]->id);
    }

    /**
     * Test the posts array returned from the getPosts() method with {with} param
     * @return void
     */
    public function test_get_posts_with_with_param()
    {
        (new PostSeeder())->run();
        (new CommentSeeder())->run('PHP,C++,Java,MySQL');
        $posts = (new Posts())->getPosts((new Post()), ['with' => 'comments']);
        foreach ($posts['data']['result']['data'] as $post) {
            $this->assertTrue(isset($post['comments']));
        }
    }

    /**
     * Test the posts array returned from the getPosts() method with {with} and comment params
     * @return void
     */
    public function test_get_posts_with_with_and_comment_param()
    {
        (new PostSeeder())->run();
        (new CommentSeeder())->run('PHP,C++,Java,MySQL');
        $posts = (new Posts())->getPosts((new Post()), ['with' => 'comments', 'comment' => 'PHP']);
        foreach ($posts['data']['result']['data'] as $post) {
            foreach ($post['comments'] as $comment) {
                $this->assertTrue(str_contains($comment['content'], 'PHP'));
            }
        }
    }

    /**
     * Test deactivate post
     * @return void
     */
    public function test_deactivate_post()
    {
        (new PostSeeder())->run();
        (new Posts())->deactivatePosts(5);
        $post = Post::findOrFail(5);
        $this->assertEquals(1, $post->inactive);
    }

    /**
     * Test deactivate a post with comments
     * @return void
     */
    public function test_deactivate_post_with_comments()
    {
        (new PostSeeder())->run();
        (new CommentSeeder())->run('PHP,C++,Java,MySQL');
        (new Posts())->deactivatePosts(5);
        $comments = Comment::where('post_id', 5)->get();
        foreach ($comments as $comment) {
            $this->assertEquals(1, $comment->inactive);
        }
    }
}
