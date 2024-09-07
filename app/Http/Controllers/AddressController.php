<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address; 


class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // Only authenticated users can access these routes
    }

    // Create a new address
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
        ]);

        // Check for duplicate address
        $existingAddress = Address::where('user_id', $request->user()->id)
            ->where('street', $request->street)
            ->where('city', $request->city)
            ->where('state', $request->state)
            ->where('country', $request->country)
            ->where('postal_code', $request->postal_code)
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'This address already exists.'
            ], 409); // HTTP status code 409 for conflict
        }

        // Create the new address if it's not a duplicate
        $address = Address::create([
            'user_id' => $request->user()->id,
            'street' => $request->street,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_billing' => $request->is_billing ?? false,
            'is_shipping' => $request->is_shipping ?? false,
        ]);

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address
        ], 201);
    }


    // Get addresses for the authenticated user
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses;

        // Check if the user has no addresses
        if ($addresses->isEmpty()) {
            return response()->json([
                'status_message' => 'failed',
                'message' => 'No addresses found for this user.'
            ], 404); // HTTP status code 404 for not found
        }

        return response()->json($addresses, 200);
    }


    // Update an existing address
    public function update(Request $request, Address $address)
    {
        // Validate the request
        $request->validate([
            'street' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'sometimes|required|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'postal_code' => 'sometimes|required|string|max:10',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
        ]);

        // Check for duplicate address, but exclude the current address being updated
        $existingAddress = Address::where('user_id', $request->user()->id)
            ->where('street', $request->street ?? $address->street)
            ->where('city', $request->city ?? $address->city)
            ->where('state', $request->state ?? $address->state)
            ->where('country', $request->country ?? $address->country)
            ->where('postal_code', $request->postal_code ?? $address->postal_code)
            ->where('id', '!=', $address->id) // Exclude the current address being updated
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'This address already exists.'
            ], 409); // HTTP status code 409 for conflict
        }

        // Update the address
        $address->update($request->all());

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address
        ]);
    }

    public function destroy($id)
    {
        try {
            // Find the address by ID or throw a ModelNotFoundException
            $address = Address::findOrFail($id);
    
            // Delete the address
            $address->delete();
    
            return response()->json([
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Address not found.'
            ], 404); // HTTP status code 404 for not found
        }
    }
    
}