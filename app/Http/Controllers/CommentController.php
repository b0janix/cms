<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\Comments;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private Comments $comments)
    {
        //
    }

    //

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function comments(Request $request): JsonResponse
    {
        $comment = new Comment();
        $params = $request->all();

        //It should get all the comments with all the filters applied

        $response = $this->comments->getComments($comment, $params);

        return response()->json($response['data'], $response['code']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $params = $request->all();
        try {
            $this->validate($request, [
                'post_id' => 'required|integer',
                'content' => 'required|string',
                'abbreviation' => 'required|string|unique:comments'
            ]);
        } catch (ValidationException $e) {
            return response()->json(["result" => ["message" => $e->getMessage()], "count" => 0], $e->getCode());
        }

        //It should create a new comment using the payload from the request

        $response = $this->comments->createComment($params);

        return response()->json($response['data'], $response['code']);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        //It should deactivate the comment with the id sent

        $response = $this->comments->deactivateComment($id);
        return response()->json($response['data'], $response['code']);
    }
}
