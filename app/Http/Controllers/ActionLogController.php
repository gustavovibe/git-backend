<?php

namespace App\Http\Controllers;

use App\Filters\UsersFilters;
use App\Helpers\ApiResponse;
use App\Http\Resources\ActionLogResource;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{

    /**
     * Get all action logs.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function index(Request $request)
    {
        $action= (new UsersFilters)->ActionLogs($request);
        return ApiResponse::success($action);
        $perPage = 10;

        // Inicia la consulta base
        $query = ActionLog::with('user');

        // Filtrar por fechas si están presentes
        if ($request->input('startDate') && $request->input('endDate')) {
            $startDate = $request->input('startDate') . ' 00:00:00';
            $endDate = $request->input('endDate') . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtrar por búsqueda de nombre del usuario relacionado
        $q = $request->input('q');
        if ($q) {
            $query->whereHas('user', function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', $q . '%');
            });
        }

        // Filtrar por tipo si está presente
        $type = $request->input('type');
        if ($type) {
            if ($type != 'all') {
                $query->where('type', $type);
            }
        }

        // Paginar los resultados
        $paginatedData = $query->paginate($perPage);

        // Preparar los datos de respuesta
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
