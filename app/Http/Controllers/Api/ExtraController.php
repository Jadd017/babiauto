<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    public function index()
    {
        return response()->json(Extra::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_day' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $extra = Extra::create($validated);

        return response()->json([
            'message' => 'Extra created successfully',
            'data' => $extra,
        ], 201);
    }

    public function show(string $id)
    {
        $extra = Extra::findOrFail($id);

        return response()->json($extra);
    }

    public function update(Request $request, string $id)
    {
        $extra = Extra::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price_per_day' => 'sometimes|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $extra->update($validated);

        return response()->json([
            'message' => 'Extra updated successfully',
            'data' => $extra,
        ]);
    }

    public function destroy(string $id)
    {
        $extra = Extra::findOrFail($id);
        $extra->delete();

        return response()->json([
            'message' => 'Extra deleted successfully',
        ]);
    }
}