<?php

namespace App\Http\Controllers;

use App\Filters\UsersFilters;
use App\Helpers\ApiResponse;
use App\Http\Resources\ActionLogResource;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    public function index(Request $request)
    {
        $action= (new UsersFilters)->ActionLogs($request);
        return ApiResponse::success($action);
        return $action;
        $perPage = 10;

        $q = $request->input('q');

        if ($q) {
            $paginatedData = ActionLog::with('user')->where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = ActionLog::paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = ActionLogResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
