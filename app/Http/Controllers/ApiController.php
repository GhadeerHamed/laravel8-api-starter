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

    public function getUser() : User
    {
        return User::query()->find(auth('api')->id());
    }

}
