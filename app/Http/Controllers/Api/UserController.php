<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\User;
use App\Models\Posts;
use App\Models\Post_attachments;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function all_users()
    {
        $user = Auth::user();

        $data = User::whereNotIn('id', function($query) use ($user){
        $query->select('following_id')
            ->from('follow')
            ->where('follower_id', $user->id);
        })
        ->where('id','!=',$user->id)
        ->get();

        return response()->json([
        'users' => $data
        ], 200);
    }

    public function detail_users(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        if($user->id === Auth::id())
        {
            $isyouraccount = true;
        }else{
            $isyouraccount = false;
        }

        $checkfollowstatus = Follow::where('follower_id', Auth::id())->where('following_id', $user->id)->first();

        if($checkfollowstatus){
            $status = $checkfollowstatus->is_accepted ? 'following' : 'requested';
        }else{
            $status = 'not-following';
        }

        $postcount = Posts::where('user_id', $user->id)->count();

        $followercount = Follow::where('following_id', $user->id)->count();

        $followingcount = Follow::where('follower_id', $user->id)->count();

        $posts = Posts::where('user_id', $user->id)->get();

        $storage = $posts->map(function ($post){
            $attachments = Post_attachments::where('post_id', $post->id)->get(['id','storage_path']);
        return [
            'id' => $post->id,
            'caption' => $post->caption,
            'created_at' => $post->created_at,
            'deleted_at' => $post->deleted_at,
            'attachments' => $attachments
        ];
        });

        $bigdata = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
            'is_private' => $user->is_private,
            'created_at' => $user->created_at,
            'is_your_account' => $isyouraccount,
            'following_status' => $status,
            'posts_count' => $postcount,
            'followers_count' =>  $followercount,
            'following_count' => $followingcount,
            'posts' =>  $storage
        ];

        return $bigdata;
    }
}
