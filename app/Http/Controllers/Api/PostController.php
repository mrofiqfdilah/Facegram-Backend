<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Post_attachments;
use App\Models\Posts;

class PostController extends Controller
{
    public function create_post(Request $request)
    {
        $validator = Validator::make($request->all(),[
        'caption' => 'required',
        'attachments.*' => 'required|mimes:jpg,jpeg,webp,png,gif'
        ]);

        if($validator->fails()){
            return response()->json([
            'message' => 'Invalid field',
            'errors' => $validator->errors()
            ], 422);
        }

        $posts = Posts::create([
        'caption' => $request->caption,
        'user_id' => Auth::id()
        ]);

        foreach($request->attachments as $attachments){
            $file = $attachments->storeAs('posts', $attachments->getClientOriginalName(), 'public');

            $attach = new Post_attachments;
            $attach->post_id = $posts->id;
            $attach->storage_path = $file;
            $attach->save();
        }

        return response()->json([
        'message' => 'Create post success'
        ], 201);
    }

    public function delete_post(Request $request, $id)
    {
        $posts = Posts::where('id', $id)->first();

        if(!$posts)
        {
            return response()->json([
            'message' => 'Post not found'
            ], 404);
        }

        if($posts->user_id != Auth::id())
        {
            return response()->json([
                'message' => 'Forbidden access'
             ], 403); 
        }

        $posts->delete();

        return response()->json([], 204);
    }

    public function all_post(Request $request)
    {
        $validator = Validator::make($request->all(),[
        'page' => 'integer|min:0',
        'size' => 'integer|min:1'
        ]);

        if($validator->fails()){
            return response()->json([
            'message' => 'Invalid field',
            'errors' => $validator->errors()
            ], 422);
        }

        $page = $request->input('page', 0);
        $size = $request->input('size', 10);

        $posts = Posts::with('user','post_attachments')->paginate($size);

        $alldata = $posts->map(function ($post){
        return [
            'id' => $post->id,
            'caption' => $post->caption,
            'created_at' => $post->created_at,
            'deleted_at' => $post->deleted_at,
        'user' => [
            'id' => $post->user->id,
            'full_name' => $post->user->full_name,
            'username' => $post->user->username,
            'bio' => $post->user->bio,
            'is_private' => $post->user->is_private,
            'created_at' => $post->user->created_at
        ],
        'attachments' => $post->post_attachments->map(function ($attach){
            return [
                'id' => $attach->id,
                'storage_path' => $attach->storage_path
            ];
        }),
    ];
        });

    return response()->json([
    'page' => $page,
    'size' => $size,
    'posts' => $alldata
    ], 200);
    }
}
