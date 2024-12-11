<?php

namespace App\Http\Controllers;

use App\Mail\MailCertificaciones;
use App\Models\Course;
use App\Models\CourseForUser;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class CourseForUserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response     
     */
    public function index()
    {
        $coursesForUsers = CourseForUser::all();
        return response()->json(['coursesForUsers' => $coursesForUsers], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(['message' => 'Crear nuevo curso.'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_user = $request->input('id_user');
        $id_course = $request->input('id_course');

        // Verificar si ya existe un registro con la misma combinación de id_user e id_course
        $existingRecord = CourseForUser::where('id_user', $id_user)
            ->where('id_course', $id_course)
            ->first();

        if ($existingRecord) {
            return response()->json(['message' => 'Ya te encuentras inscrito.'], 422);
        }

        if ($id_course == 10 || $id_course == 11 || $id_course == 12) {
            $user = User::find($id_user);

            $course = Course::find($id_course);

            $correo = new MailCertificaciones($user->full_name, $course->title);

            Mail::to($user->email)->send($correo);
        }

        // Si no existe, crear el nuevo registro
        $data = $request->all();
        $data['progress'] = json_encode(['video' => 1, 'complete' => 0, 'progress' => 0]);

        $courseForUser = CourseForUser::create($data);
        return response()->json(['message' => 'Inscripcion  exitosa.'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */ 
    public function show($id)
    {
        // Obtén el id del usuario para el que quieres obtener los cursos
        $userId = $id;

        // Busca los cursos relacionados con el usuario a través de la columna id_user
        $coursesForUser = CourseForUser::with('course.video')->where('id_user', $userId)->get();

        // Verifica si se encontraron cursos para el usuario
        if ($coursesForUser->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cursos para el usuario.'], 404);
        }

        return response()->json($coursesForUser, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courseForUser = CourseForUser::findOrFail($id);
        return response()->json(['courseForUser' => $courseForUser], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $courseForUser = CourseForUser::findOrFail($id);
        $courseForUser->update($request->all());
        return response()->json(['message' => 'CourseForUser updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $courseForUser = CourseForUser::findOrFail($id);
        $courseForUser->delete();
        return response()->json(['message' => 'CourseForUser deleted successfully'], 200);
    }
}
