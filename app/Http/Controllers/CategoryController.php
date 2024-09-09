<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category; 

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']); // Only admins can create, update, or delete
    }

    // Get all categories (Public access)
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Get a single category
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    // Create a new category (Admin only)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    // Update a category (Admin only)
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string|max:65535',
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->name = $request->name;
        $category->description = $request->description; // Update the description
        $category->save();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    // Delete a category (Admin only)
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
