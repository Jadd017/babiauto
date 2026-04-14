<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::with(['brand', 'category', 'branch', 'images'])->paginate(10);

        return response()->json($cars);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer',
            'plate_number' => 'required|string|unique:cars,plate_number',
            'color' => 'required|string|max:100',
            'transmission' => 'required|in:automatic,manual',
            'fuel_type' => 'required|in:gasoline,diesel,hybrid,electric',
            'seats' => 'required|integer',
            'doors' => 'required|integer',
            'bags_capacity' => 'nullable|integer',
            'daily_rate' => 'required|numeric',
            'weekly_rate' => 'nullable|numeric',
            'monthly_rate' => 'nullable|numeric',
            'mileage_limit_per_day' => 'nullable|integer',
            'description' => 'nullable|string',
            'main_image' => 'nullable|string',
            'is_available' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:active,maintenance,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name'] . '-' . $validated['model'] . '-' . uniqid());

        $car = Car::create($validated);

        return response()->json([
            'message' => 'Car created successfully',
            'data' => $car,
        ], 201);
    }

    public function show(string $id)
    {
        $car = Car::with(['brand', 'category', 'branch', 'images', 'reviews.user'])->findOrFail($id);

        return response()->json($car);
    }

    public function update(Request $request, string $id)
    {
        $car = Car::findOrFail($id);

        $validated = $request->validate([
            'brand_id' => 'sometimes|exists:brands,id',
            'category_id' => 'sometimes|exists:categories,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'name' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer',
            'plate_number' => 'sometimes|string|unique:cars,plate_number,' . $car->id,
            'color' => 'sometimes|string|max:100',
            'transmission' => 'sometimes|in:automatic,manual',
            'fuel_type' => 'sometimes|in:gasoline,diesel,hybrid,electric',
            'seats' => 'sometimes|integer',
            'doors' => 'sometimes|integer',
            'bags_capacity' => 'nullable|integer',
            'daily_rate' => 'sometimes|numeric',
            'weekly_rate' => 'nullable|numeric',
            'monthly_rate' => 'nullable|numeric',
            'mileage_limit_per_day' => 'nullable|integer',
            'description' => 'nullable|string',
            'main_image' => 'nullable|string',
            'is_available' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'status' => 'sometimes|in:active,maintenance,inactive',
        ]);

        if (isset($validated['name']) || isset($validated['model'])) {
            $validated['slug'] = Str::slug(
                ($validated['name'] ?? $car->name) . '-' .
                ($validated['model'] ?? $car->model) . '-' .
                $car->id
            );
        }

        $car->update($validated);

        return response()->json([
            'message' => 'Car updated successfully',
            'data' => $car,
        ]);
    }

    public function destroy(string $id)
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return response()->json([
            'message' => 'Car deleted successfully',
        ]);
    }
    public function available()
{
    $cars = Car::with(['brand', 'category', 'branch'])
        ->where('status', 'active')
        ->where('is_available', true)
        ->get();

    return response()->json($cars);
}
}