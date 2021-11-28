<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class Posts
{
    /**
     * @param Post $post
     * @param array $params
     * @return array|void
     */
    #[ArrayShape(['data' => "array", 'code' => "int"])]
    public function getPosts(Post $post, array $params)
    {
        $paramNames = array_keys($params);
        $limit = $params['limit'] ?? config('general.limit');

        //The code placed inside the if block tries to find whether some existing
        //columns inside the model are set as filters in the request

        if (!empty(array_intersect($paramNames, $post->getColumns()))
            || isset($params['sort'])
            || isset($params['comment'])
        ) {

            //I'm using the query builder for this type of queries

            $qb = DB::table('posts');

            //I've added an inactive column (tinyint) because when deleting posts or comments
            //I'm executing soft deletes (by changing the value of the inactive column to 1, the default is 0)

            $qb->where('posts.inactive', '=', 0);

            if (!empty($params['id'])) {
                $qb->where('posts.id', '=', $params['id']);
            }

            if (!empty($params['topic'])) {
                $qb->where('posts.topic', '=', $params['topic']);
            }

            if (!empty($params['created_at'])) {
                $qb->where('posts.created_at', '=', $params['created_at']);
            }

            if (!empty($params['updated_at'])) {
                $qb->where('posts.updated_at', '=', $params['updated_at']);
            }

            if (!empty($params['sort'])) {
                $qb->orderBy('posts.' . $params['sort'], $params['direction'] ?? 'asc');
            }

            if (!empty($params['with'])) {
                $relation = $params['with'];

                //Inside the try catch block I have a code that just checks
                //whether the relationship send by the user in the request
                //really exists

                try {
                    $post->$relation();
                } catch (\Exception $e) {
                    return ['data' => ["result" => ['message' => 'Please provide a valid relationship name'], "count" => 0], 'code' => 400];
                }

                $posts = $qb->paginate((int) $limit);

                //Hydrate will transform a regular collection into an eloquent collection
                //in order for later to lazy load all the relationships

                $postModels = Post::hydrate($posts->items());

                $posts = $posts->toArray();

                //Here I lazy load all the relationships

                $postModels->load([$params['with'] => function($query) use ($params) {

                    //If the query string params with=comments and comment are being set
                    //it's going to query the DB by applying a query that uses
                    // full text search (it's a lot faster than using "like")

                    //ex. SELECT * FROM `posts`
                    // INNER JOIN `comments` ON `posts`.`id` = `comments`.`post_id`
                    // WHERE MATCH(comments.content) AGAINST('PHP') AND `comments`.`inactive` = 0

                    if ($params['with'] === 'comments' && !empty($params['comment'])) {
                        $query->whereRaw('MATCH(comments.content) AGAINST(?)', $params['comment']);
                        $query->where('comments.inactive', '=', 0);
                    }

                    //also, I'm executing a limitation of the loaded comment relationships to 50
                    //otherwise the query will be too slow aa it loads thousands of related records
                    //in this way it will get only the first 50 records, and it will distribute them between
                    //the post records retrieved by this query. If the first 50 comment records belong to just one
                    // post then it's going to assign them all to that post or if we are filtering
                    // by id we are going to get just one post and all the comments will belong to that post

                    $query->take(50);
                }]);
                $posts['data'] = $postModels->toArray();
            }
            else {
                $posts = $qb->paginate((int) $limit)->toArray();
            }
        }
        else {
            if (!empty($params['with'])) {
                $relation = $params['with'];
                try {
                    $post->$relation();
                } catch (\Exception $e) {
                    return ['data' => ["result" => ['message' => 'Please provide a valid relationship name'], "count" => 0], 'code' => 400];
                }
                $posts = Post::where('inactive', '=',0)->paginate((int)$limit);
                $posts->load([$params['with'] => function($query) use ($params) {
                    if (!empty($params['comment'])) {
                        $query->whereRaw('MATCH(comments.content) AGAINST(?)', $params['comment']);
                        $query->where('comments.inactive', '=', 0);
                    }
                    $query->take(50);
                }]);
                $posts = $posts->toArray();
            } else {
                $posts = Post::where('inactive', '=',0)->paginate((int)$limit)->toArray();
            }
        }

        $total = 0;

        if (isset($posts['total'])) {
            $total = $posts['total'];
            unset($posts['total']);
        }

        return ['data' => ["result" => $posts, "count" => $total], 'code' => 200];
    }

    /**
     * This method executes soft deletes instead of actual deletes from the database
     *
     * @param int $id
     * @return array
     */
    public function deactivatePosts(int $id): array
    {
        try {
            $post = Post::with('comments')->findOrFail($id);

            //I'm getting a collection of ids of all related comment records

            $commentIds = $post->comments->pluck('id');
        } catch (\Exception $e) {
            return['data' => ["result" => ["message" => $e->getMessage()], "count" => 0], 'code' => $e->getCode()];
        }
        DB::beginTransaction();
        try {

            //When you try to deactivate/delete a post
            // If the deactivation is successful all the
            //related comments are going to be deactivated/deleted as well
            Comment::whereIn('id', $commentIds)->update(['inactive' => true]);
            $post->inactive = true;
            $post->save();

        } catch (\Exception $e) {
            DB::rollBack();
            return [["result" => ["message" => $e->getMessage()], "count" => 0], $e->getCode()];
        }
        DB::commit();

        return ['data' => ["result" => ["message" => "The post with an id of $id was successfully deactivated"], "count" => 1], 'code' => 200];
    }
}
