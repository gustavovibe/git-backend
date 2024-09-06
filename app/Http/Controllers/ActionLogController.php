<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ActionLogResource;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
