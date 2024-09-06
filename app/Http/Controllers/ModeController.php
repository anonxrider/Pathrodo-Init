<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mode;
class ModeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['index','show','store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['index','show','store', 'update', 'destroy']); // Only admins can create, update, or delete
    }
    // List all modes
    public function index()
    {
        $modes = Mode::all();
        $count_modes = $modes->count();
        
        if ($count_modes == 0) {
            return response()->json(['message' => 'No modes found'], 404);
        }

        return response()->json([
            'total_count' => $count_modes,
            'modes' => $modes
        ]);
    }
    // Show a single mode
    public function show($id)
    {
        $mode = Mode::find($id);
        if (!$mode) {
            return response()->json(['message' => 'Mode not found'], 404);
        }
        return response()->json($unit);
    }

    // Create a new mode
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:modes',
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
