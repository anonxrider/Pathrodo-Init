<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemType;
class ItemTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']); // Only admins can create, update, or delete
    }

    // Get all Item Types (Public access)
    public function index()
    {
        $item_types = ItemType::all();
        return response()->json($item_types);
    }

    // Get a single item type
    public function show($id)
    {
        $item_type = ItemType::find($id);

        if (!$item_type) {
            return response()->json(['message' => 'Item Type not found'], 404);
        }

        return response()->json($item_type);
    }

    // Create a new Item Type (Admin only)
    public function store(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string|max:255|unique:item_types,item_type',
            'code' => 'required|string|max:255|unique:item_types,code'
        ]);

        $item_type = ItemType::create([
            'item_type' => $request->item_type,
            'code' => $request->code,
            
        ]);

        return response()->json([
            'message' => 'Item Type created successfully',
            'item type' => $item_type
        ], 201);
    }
}
