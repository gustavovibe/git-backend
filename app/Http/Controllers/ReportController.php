<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Mail\MailCertificaciones;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        $reports = Report::all();
        return response()->json(['data' => $reports]);
    }

    public function show(Report $report): JsonResponse
    {
        return response()->json(['data' => $report]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'area' => 'required|string',

        ]);





        $report = Report::create($request->all());
        if ($request->source == "certification") {
            $correo = new MailCertificaciones($request->name, $request->area);
            Mail::to($request->email)->send($correo);
        }
        return response()->json(['message' => 'Reporte creado exitosamente.', 'data' => $report], 200);
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'area' => 'required|string',
            'follow_up' => 'required|string'
        ]);

        $report->update($request->all());

        return response()->json(['message' => 'Reporte actualizado exitosamente.', 'data' => $report]);
    }

    public function destroy(Report $report): JsonResponse
    {
        $report->delete();
        return response()->json(['message' => 'Reporte eliminado exitosamente.']);
    }
}
