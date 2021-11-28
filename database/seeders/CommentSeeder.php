<?php

namespace Database\Seeders;

use App\Services\Combinations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(string $words='')
    {
        //gets the random words from a file with a path config/general.php
        $randomWords = $words ?: config('general.random_words');

        //this is the service that will generate all the unique combinations for a valid string input
        //1891 unique combinations for this input
        //"Cool,Strange,Funny,Laughing,Nice,Awesome,Great,Horrible,Beautiful,PHP,Vegeta,Italy,Joost"
        //of 13 unique words with unique first letters
        $combinations = new Combinations();

        // The algorithm that gets all the unique combinations
        // It would have been great if I wrote the algorithm using generators
        // instead of building the array in memory :)
        // at the end I turn the array into collection
        // in order to be able to create chunks in order not to insert 8191 records at once
        // also laravel collections are using generators
        $collection = collect($combinations->getCombinations($randomWords));

        // this method generates the rest of the required values that are going to be inserted into the comments table
        $chunks = $combinations->generateRemainingValues($collection->chunk(3000));

        //also, I'm using transactions for safety and speed
        DB::beginTransaction();
        try {
            foreach ($chunks as $chunk) {
                DB::table('comments')->insert($chunk->toArray());
                sleep(0.5);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage() . '\n';
            echo $exception->getCode() . '\n';
            echo $exception->getTraceAsString() . '\n';
        }
        DB::commit();
    }
}
