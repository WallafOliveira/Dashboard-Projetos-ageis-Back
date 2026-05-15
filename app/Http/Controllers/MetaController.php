<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function index()
    {
        $metas = \App\Models\Meta::orderBy('created_at', 'desc')->get();
        return response()->json($metas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string',
            'categoria' => 'required|string',
            'valor_alvo' => 'required|string',
            'data_limite' => 'required|date',
            'valor_atual' => 'nullable|string',
            'status' => 'nullable|in:on-track,at-risk,completed',
            'progresso' => 'nullable|integer',
        ]);

        $meta = \App\Models\Meta::create($validated);

        return response()->json($meta, 201);
    }
}
