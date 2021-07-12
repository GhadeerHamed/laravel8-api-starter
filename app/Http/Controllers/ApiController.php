<?php

namespace App\Http\Controllers;

use App\Http\Traits\Responsable;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Responsable;

    public function createApiPaginator($data): array
    {
        return [
            'total_count' => $data->total(),
            'limit' => $data->perPage(),
            'total_page' => ceil($data->total() / $data->perPage()),
            'current_page' => $data->currentPage(),
        ];
    }

    public function getUser(Request $request)
    {
        if ($userId = $request->get('user_id')){
            return User::find($userId);
        }
        elseif (auth('client')->check())
            return User::find(auth('client')->id());
        else
            return null;
    }

    public function likedByUser($items, $user){
        return $items->map(function($item) use ($user){
            $item->liked_by = $item->isLikedBy($user);
            return $item;
        });
    }

    public function likedReviewByUser($items, $user){
        return $items->map(function($cartItem) use ($user){
            $item = $cartItem->product;
            $item->liked_by = $item->isLikedBy($user);
            $item->review_by = $item->isReviewBy($user);
            return $cartItem;
        });
    }
}
