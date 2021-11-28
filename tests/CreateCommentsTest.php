<?php

use App\Models\Comment;
use App\Models\Post;
use App\Services\Combinations;
use Database\Seeders\CommentSeeder;
use Database\Seeders\PostSeeder;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateCommentsTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Check whether we are getting the words from the config file
     * @return void
     */
    public function test_get_random_words()
    {
        $randomWords = config('general.random_words');
        $this->assertTrue(!empty($randomWords));
    }

    /**
     * Check the number of the random words
     * @return void
     */
    public function test_number_of_random_words()
    {
        $randomWords = explode(',', config('general.random_words'));
        $this->assertEquals(13, count($randomWords));
    }

    /**
     * Check whether the random words are unique
     * @return void
     */
    public function test_random_words_uniqueness()
    {
        $randomWords = array_unique(explode(',', config('general.random_words')));
        $this->assertEquals(13, count($randomWords));
    }

    /**
     * Test the number of unique combinations
     * @return void
     */
    public function test_total_number_of_combinations()
    {
        $comboArr = (new Combinations())->getCombinations(config('general.random_words'));
        $this->assertEquals(8191, count($comboArr));
    }

    /**
     * Count the number of chunks
     * @return void
     */
    public function test_number_of_combination_chunks()
    {
        (new PostSeeder())->run();
        $combinations = new Combinations();
        $collection = collect($combinations->getCombinations(config('general.random_words')));
        $chunks = $combinations->generateRemainingValues($collection->chunk(3000));
        $this->assertEquals(3, $chunks->count());
    }

    /**
     * Test a helper function that sets the remaining values for the need of the response
     * @return void
     */
    public function test_generate_remaining_values()
    {
        (new PostSeeder())->run();
        $combinations = new Combinations();
        $collection = collect($combinations->getCombinations('PHP,JS,C++'));
        $chunks = $combinations->generateRemainingValues($collection->chunk(7));
        foreach ($chunks as $chunk) {
            $this->assertNotEmpty($chunk[0]['content']);
            $this->assertNotEmpty($chunk[0]['abbreviation']);
            $this->assertNotEmpty($chunk[0]['post_id']);
        }
    }

    /**
     * Test the comments seeder
     * @return void
     */
    public function test_comments_seeding()
    {
        (new PostSeeder())->run();
        (new CommentSeeder())->run('PHP,JS,C++');
        $comments = DB::table('comments')->orderByRaw('LENGTH(content) DESC')->get();
        $this->assertEquals(7, $comments->count());
        $longestContentArr = explode(' ', $comments->first()->content);
        $this->assertSame(['C++', 'JS', 'PHP'], $longestContentArr);
    }

}
