<?php

namespace App\Services;

use App\Models\Post;

class Combinations
{
    /**
     * This algorithm uses the backtracking technique
     *
     * @param array $elements
     * @return array
     */
    private function combinations(array $elements): array
    {
        if (count($elements) === 0) {
            return [[]];
        }

        //it will reduce the input array by one each time it iterates recursively
        //when the array is going to become empty
        //the code will return an empty array
        $firstEl = $elements[0];
        $rest = array_slice($elements, 1);

        //the recursive call with the deduced array
        //in the first phase we are getting all the results
        //without a first element
        $combsWithoutFirst = $this->combinations($rest);
        $combsWithFirst = [];

        //in the second phase we are including the missing first element into
        //the result that we are going to return
        //If the result is an array we are going to append the element to an array
        //otherwise we are going to append to a string of words separated with empty spaces'
        //ex "PHP MySQL JS" and store that into an array
        foreach ($combsWithoutFirst as $comb) {
            if (is_array($comb)) {
                $arr = [...$comb, $firstEl];
            } else {
                $arr = explode(' ', $comb . " " . $firstEl);
            }
            $combsWithFirst[] = implode(" ", $arr);
        }
        //at the end we will return an array merged of all the arrays of results
        // that are missing the first element/word
        // and those that have included the first word/element
        return [...$combsWithoutFirst, ...$combsWithFirst];

        //I have a test written for this one inside tests/CreateCommentsTest.php
        //test_total_number_of_combinations
    }

    /**
     * This method generates the rest of the required values
     * that are going to be inserted into the comments table
     *
     * @param $chunks
     * @return mixed
     */
    public function generateRemainingValues($chunks): mixed
    {
        $postIds = Post::pluck('id');
        foreach ($chunks as $key => $chunk) {
            $chunks[$key] = $chunk->map(function ($item) use ($postIds) {
                $comArr = explode(' ', $item);
                $abrev = array_reduce($comArr, function ($carry, $item) {
                    $carry .= strlen($item) > 0 ? $item[0] : '';
                    return $carry;
                }, '');

                return [
                    'content' => $item,
                    'abbreviation' => $abrev,
                    'post_id' => $postIds->random(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            });
        };
        return $chunks;
    }

    /**
     * @param string $randomWords
     * @return array
     */
    public function getCombinations(string $randomWords): array
    {
        $combinations = $this->combinations(explode(',', $randomWords));
        //It removes the first element or the empty array that the
        // combinations() algorithm will return as a first result
        //as the task specifically required that we should not include empty string as results
        //just the unique combinations
        return array_splice($combinations, 1);
    }
}
