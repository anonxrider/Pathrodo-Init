<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
class UnitController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['index','show','store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['index','show','store', 'update', 'destroy']); // Only admins can create, update, or delete
    }
    // List all units
    public function index()
    {
        $units = Unit::all();
        $count_units = $units->count();
        
        if ($count_units == 0) {
            return response()->json(['message' => 'No units found'], 404);
        }

        return response()->json([
            'total_count' => $count_units,
            'units' => $units
        ]);
    }
    // Show a single unit
    public function show($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }
        return response()->json($unit);
    }

    // Create a new unit
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'description' => 'nullable|string',
        ]);

        $unit = Unit::create($request->all());
        return response()->json($unit, 201);
    }

    // Update an existing unit
    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $unit->update($request->all());
        return response()->json($unit);
    }

    // Delete a unit
    public function destroy($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete();
        return response()->json(['message' => 'Unit deleted successfully']);
    }
}
