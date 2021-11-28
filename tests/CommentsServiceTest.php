<?php

use App\Models\Comment;
use App\Models\Post;
use App\Services\Combinations;
use App\Services\Comments;
use App\Services\Posts;
use Database\Seeders\CommentSeeder;
use Database\Seeders\PostSeeder;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CommentsServiceTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test the comments array returned from the getComments() method
     * @return void
     */
    public function test_get_comments()
    {
        (new PostSeeder())->run();
        (new CommentSeeder())->run('PHP,JS,C++,MySQL');
        $comments = (new Comments())->getComments((new Comment()), []);
        $this->assertNotEmpty($comments['data']['result']['data']);
        $this->assertTrue(isset($comments['data']['count']));
        $this->assertEquals(15, $comments['data']['count']);
        $this->assertCount(10, $comments['data']['result']['data']);
        $this->assertEquals(200, $comments['code']);
    }

    /**
     * Test passing of a content that contains words that are not unique
     * @return void
     */
    public function test_pass_non_unique_content_during_creation()
    {
        $result = (new Comments())->createComment(['content' => 'PHP,JS,C++,MySQL,PHP', 'abbreviation' => 'PJCMP']);
        $this->assertSame("Please provide comment content that contains only unique words", $result['data']['result']['message']);
        $this->assertEquals(400, $result['code']);
    }

    /**
     * Test passing of a content that contains words with first letters that are not unique
     * @return void
     */
    public function test_pass_non_unique_content_first_letters_during_creation()
    {
        $result = (new Comments())->createComment(['content' => 'PHP,JS,C++,MySQL,Python', 'abbreviation' => 'PJCMP']);
        $this->assertSame("Please provide comment content that contains only unique first letters of the words",
            $result['data']['result']['message']);
        $this->assertEquals(400, $result['code']);
    }

    /**
     * Test passing of a content which first letters don't match the abbreviation
     * @return void
     */
    public function test_pass_non_matching_content_and_abbr()
    {
        $result = (new Comments())->createComment(['content' => 'PHP,JS,C++,MySQL,Node.js', 'abbreviation' => 'PJCMA']);
        $this->assertSame("The abbreviation doesn't correspond with the concatenation of the first letters of the content",
            $result['data']['result']['message']);
        $this->assertEquals(400, $result['code']);
    }

    /**
     * Test creating a comment
     * @return void
     */
    public function test_creating_comment()
    {
        (new PostSeeder())->run();
        (new Comments())->createComment(['content' => 'PHP,JS,C++,MySQL,Node.js', 'abbreviation' => 'PJCMN', 'post_id' => 1]);
        $comment = Comment::findOrFail(1);

        $this->assertEquals(1, $comment->id);
        $this->assertSame('PHP JS C++ MySQL Node.js', $comment->content);
        $this->assertSame('PJCMN', $comment->abbreviation);
    }

    /**
     * Test deactivate a comment
     * @return void
     */
    public function test_deactivating_comment()
    {
        (new PostSeeder())->run();
        (new Comments())->createComment(['content' => 'PHP,JS,C++,MySQL,Node.js', 'abbreviation' => 'PJCMN', 'post_id' => 1]);
        $comment = Comment::findOrFail(1);
        (new Comments())->deactivateComment($comment->id);
        $comment = Comment::findOrFail(1);
        $this->assertEquals(1, $comment->inactive);
    }
}
