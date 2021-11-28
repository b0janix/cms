<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Posts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private Posts $posts)
    {
        //
    }

    //

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function posts(Request $request): JsonResponse
    {
        $post = new Post();
        $params = $request->all();

        //It should get all the posts with all the filters applied

        $posts = $this->posts->getPosts($post, $params);

        return response()->json($posts['data'], $posts['code']);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        //It should deactivate the post with the id sent together with all the
        // comments children

        $response = $this->posts->deactivatePosts($id);

        return response()->json($response['data'], $response['code']);
    }
}
