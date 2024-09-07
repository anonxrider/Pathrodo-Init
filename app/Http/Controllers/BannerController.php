<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner; 
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['index','store', 'update', 'destroy']); // Only authenticated users can create, update, or delete
        $this->middleware('role:admin')->only(['index','store', 'update', 'destroy']); // Only admins can create, update, or delete
    }

    // Create a new banner (Admin only)
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'banner_name' => 'required|string|max:255|unique:banners,banner_name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif', // Ensure it's an image
            'status' => 'boolean', // Optional boolean for status
        ]);

        // Capitalize the banner name
        $bannerName = ucwords($request->banner_name);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Store the image in the "public/banners" directory
            $imagePath = $image->store('banners', 'public'); // Store image publicly
        }

        // Create the banner
        $banner = Banner::create([
            'banner_name' => $bannerName, // Store capitalized banner name
            'image_url' => $imagePath, // Store the image URL
            'status' => $request->status ?? true, // Default to true if not provided
        ]);

        return response()->json([
            'message' => 'Banner created successfully',
            'banner' => $banner
        ], 201);
    
    }
    
    // Get all active banners (Public access)
    public function homeActiveBanners()
    {
        // Get banners where status is 1 and select only the image_url
        $banners = Banner::where('status', 1)->pluck('image_url');

        // Append full image URL to each banner
        $banners = $banners->map(function($image_url) {
            return Storage::url($image_url); // Generate full image URL
        });

        return response()->json($banners);
    }
    
    //Get all banners active and non active
    public function index()
    {
        $banners = Banner::all();
        return response()->json($banners);
    }
        
}
