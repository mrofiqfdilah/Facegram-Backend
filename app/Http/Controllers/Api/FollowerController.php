<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FollowerController extends Controller
{
    public function accept_request(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $accept = Follow::where('following_id', Auth::id())
        ->where('follower_id', $user->id)
        ->first();

        if(!$accept)
        {
            return response()->json([
            'message' => 'The user is not following you',
            ], 422);
        }

        if($accept->is_accepted)
        {
            return response()->json([
            'message' => 'Follow request is already accepted'
            ], 422);
        }

        $accept->is_accepted = true;
        $accept->save();

        return response()->json([
        'message' => 'Follow request accepted'
        ], 200);
    }

    public function see_follower(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $check = Follow::where('following_id', $user->id)->get();

        $data = $check->map(function($check){
            $user = User::find($check->follower_id);
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'bio' => $user->bio,
                'is_private' => $user->is_private,
                'created_at' => $user->created_at,
                'is_requested' => $check->is_accepted == 1 ? false : true
            ];
        });
       
        return response()->json([
        'followers' => $data
        ], 200);
    }
}
