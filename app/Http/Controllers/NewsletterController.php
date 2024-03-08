<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;


class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::all();
        return response()->json(['data' => $newsletters], 200);
    }

    public function show($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        return response()->json(['data' => $newsletter], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'required|string',
            'url_link' => 'nullable|string',
            'url_img' => 'required|string',
            'our_news' => 'required|string'
        ]);

        $newsletter = Newsletter::create($data);
        return response()->json(['data' => $newsletter], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'required|string',
            'url_link' => 'nullable|string',
            'url_img' => 'required|string',
            'our_news' => 'required|string'
        ]);

        $newsletter = Newsletter::findOrFail($id);
        $newsletter->update($data);

        return response()->json(['data' => $newsletter], 200);
    }

    public function destroy($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        return response()->json(['message' => 'Newsletter eliminado correctamente'], 200);
    }
}
