<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner; 

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']); // Only admins can create, update, or delete
    }

    // Create a new banner (Admin only)
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'banner_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif', // Ensure it's an image
            'status' => 'boolean', // Optional boolean for status
        ]);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Store the image in the "public/banners" directory
            $imagePath = $image->store('banners', 'public'); // Store image publicly
        }

        // Create the banner
        $banner = Banner::create([
            'banner_name' => $request->banner_name,
            'image_url' => $imagePath, // Store the image URL
            'status' => $request->status ?? true, // Default to true if not provided
        ]);

        return response()->json([
            'message' => 'Banner created successfully',
            'banner' => $banner
        ], 201);
    
    }
    
        
}
