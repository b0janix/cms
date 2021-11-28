<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\DB;

class Comments
{
    public function getComments(Comment $comment, array $params): array
    {
        $paramNames = array_keys($params);
        $limit = $params['limit'] ?? config('general.limit');

        //The code placed inside the if block tries to find whether some existing
        //columns inside the model are set as filters in the request

        if (!empty(array_intersect($paramNames, $comment->getColumns())) || isset($params['sort'])) {

            //I'm using the query builder for this type of queries

            $qb = DB::table('comments');

            //I've added an inactive column (tinyint) because when deleting posts or comments
            //I'm executing soft deletes (by changing the value of the inactive column to 1, the default is 0)

            $qb->where('comments.inactive', '=', 0);

            if (!empty($params['id'])) {
                $qb->where('comments.id', '=', $params['id']);
            }
            if (!empty($params['content'])) {
                $qb->where('comments.content', '=', $params['content']);
            }
            if (!empty($params['abbreviation'])) {
                $qb->where('comments.abbreviation', '=', $params['abbreviation']);
            }
            if (!empty($params['created_at'])) {
                $qb->where('comments.created_at', '=', $params['created_at']);
            }
            if (!empty($params['updated_at'])) {
                $qb->where('comments.updated_at', '=', $params['updated_at']);
            }

            if (!empty($params['sort'])) {
                $qb->orderBy('comments.' . $params['sort'], $params['direction'] ?? 'asc');
            }

            //Inside the try catch block I have a code that just checks
            //whether the relationship send by the user in the request
            //really exists

            if (!empty($params['with'])) {
                $relation = $params['with'];
                try {
                    $comment->$relation();
                } catch (\Exception $e) {
                    return ['data' => ["result" => ['message' => 'Please provide a valid relationship name'], "count" => 0], 'code' => 400];
                }
                $comments = $qb->paginate((int) $limit);

                //Hydrate will transform a regular collection into an eloquent collection
                //in order for later to lazy load all the relationships

                $commentModels = Comment::hydrate($comments->items());

                $comments = $comments->toArray();

                //Here I lazy load all the relationships (posts)

                $commentModels->load($params['with']);
                $comments['data'] = $commentModels->toArray();
            }
            else {
                $comments = $qb->paginate((int) $limit)->toArray();
            }
        }
        else {
            if (!empty($params['with'])) {
                $relation = $params['with'];
                try {
                    $comment->$relation();
                } catch (\Exception $e) {
                    return ['data' => ["result" => ['message' => 'Please provide a valid relationship name'], "count" => 0], 'code' => 400];
                }
                $comments = Comment::with($params['with'])->where('comments.inactive', '=', 0)->paginate((int)$limit)->toArray();
            } else {
                $comments = Comment::where('comments.inactive', '=', 0)->paginate((int)$limit)->toArray();
            }
        }

        $total = 0;

        if (isset($comments['total'])) {
            $total = $comments['total'];
            unset($comments['total']);
        }

        return ['data' => ["result" => $comments, "count" => $total], 'code' => 200];
    }

    /**
     * When you are creating comments the string input should be in exactly this format
     * "Cool,Strange,Funny,Laughing,Nice,Awesome,Great,Horrible,Beautiful,PHP,Vegeta,Italy,Joost"
     * in order the insert to succeed, and I'm not handling all the edge cases
     * where the user may send a string with empty spaces or some others format that may contain some special characters
     * (I didn't have the time to implement all of that)
     * @param array $params
     * @return array
     */
    public function createComment(array $params): array
    {
        //These are some custom validation checks

        //This one checks whether the words from the input are unique
        if (!$this->contentContainsUniqueWords($params['content'])) {
            return ['data' => ["result" => [
                "message" => "Please provide comment content that contains only unique words"],
                "count" => 0], 'code' => 400];
        }

        //This one checks whether the first letters of the words from the input are unique
        //I have to check this because we are inserting abbreviations in the database
        //and the abbreviations in the database need to be unique as it was the request inside the task
        if (!$this->contentContainsUniqueFirstLetters($params['content'])) {
            return ['data' => ["result" => [
                "message" => "Please provide comment content that contains only unique first letters of the words"],
                "count" => 0], 'code' => 400];
        }

        //This one checks whether the first concatenated letters of the content input and the abbreviation input
        // actually match
        if (!$this->contentMatchesAbbreviation($params['content'], $params['abbreviation'])) {
            return ['data' => ["result" => [
                "message" => "The abbreviation doesn't correspond with the concatenation of the first letters of the content"],
                "count" => 0], 'code' => 400];
        }

        $params['content'] = implode(" ", explode(',', $params['content']));

        $comment = new Comment();

        try {
            $comment->create($params);
        } catch (MassAssignmentException | \Exception $e) {
            return ['data' => ["result" => ["message" => $e->getMessage()], "count" => 0], 'code' => $e->getCode()];
        }

        return ['data' => ["result" => ["message" => "A comment was successfully created"], "count" => 1], 'code' => 201];
    }

    /**
     * This method executes soft deletes instead of actual deletes from the database
     *
     * @param int $id
     * @return array
     */
    public function deactivateComment(int $id): array
    {
        try {
            $comment = Comment::findOrFail($id);
        } catch (\Exception $e) {
            return ['data' => ["result" => ["message" => $e->getMessage()], "count" => 0], 'code' => $e->getCode()];
        }
        try {
            $comment->inactive = true;
            $comment->save();
        } catch (\Exception $e) {
            return ['data' => ["result" => ["message" => $e->getMessage()], "count" => 0], 'code' => $e->getCode()];
        }

        return ['data' =>["result" => ["message" => "The comment with an id of $id was successfully deactivated"], "count" => 1], 'code' => 201];
    }

    /**
     * @param string $content
     * @param string $abbreviation
     * @return bool
     */
    private function contentMatchesAbbreviation(string $content, string $abbreviation): bool
    {
        $contentParts = explode(",", $content);
        $abbr = array_reduce($contentParts, function ($initial, $part) {
            $initial .= $part[0];
            return $initial;
        }, '');
        return $abbr === $abbreviation;
    }

    /**
     * @param string $content
     * @return bool
     */
    private function contentContainsUniqueWords(string $content): bool
    {
        $contentParts = explode(",", $content);
        return count(array_unique(array_count_values($contentParts))) === 1;
    }

    /**
     * @param string $content
     * @return bool
     */
    private function contentContainsUniqueFirstLetters(string $content): bool
    {
        $contentParts = explode(",", $content);
        $firstLetters = [];
        foreach ($contentParts as $contentPart) {
            $firstLetters[] = $contentPart[0];
        }
        return count(array_unique(array_count_values($firstLetters))) === 1;
    }
}
