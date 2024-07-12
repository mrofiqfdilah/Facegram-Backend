<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FollowingController extends Controller
{
    public function follow_user(Request $request, $username)
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
            return response()->json([
            'message' => 'You are not allowed to follow yourself'
            ], 422);
        }

        $alreadyfollow = Follow::where('following_id', $user->id)
                             ->where('follower_id', Auth::id())
                             ->first();

        $status = $user->is_private == 1 ? 'requested' : 'following';

        if($alreadyfollow)
        {
            return response()->json([
            'message' => 'You are already followed',
            'status' => $status
            ], 422);
        }

        $follow = new Follow;
        $follow->following_id = $user->id;
        $follow->follower_id = Auth::id();
        $follow->is_accepted = !$user->is_private;
        $follow->save();

        return response()->json([
        'message' => 'Follow success',
        'status' => $status
        ], 200);
    }

    public function unfollow_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $follow = Follow::where('following_id', $user->id)
        ->where('follower_id', Auth::id())
        ->first();

        if(!$follow)
        {
            return response()->json([
            'message' => 'You are not following the user',
            ], 422);
        }

        $follow->delete();

        return response()->json([], 204);
    }

    public function see_following(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }
        
        $check = Follow::where('follower_id', $user->id)->get();


        $data = $check->map(function ($check){
        $user = User::find($check->following_id);
        if($user->is_private === 1){
            $status = 'true';
        }else {
            $status = 'false';
        }
        return [
        'id' => $user->id,
        'full_name' => $user->full_name,
        'username' => $user->username,
        'bio' => $user->bio,
        'is_private' => $user->is_private,
        'created_at' => $user->created_at,
        'is_requested' => $status
        ];
    });

    return response()->json([
    'following' => $data
    ]);
        
    }
}

