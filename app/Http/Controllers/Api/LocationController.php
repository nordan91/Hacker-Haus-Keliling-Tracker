<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    /**
     * GET /api/locations
     *
     * List all known selling locations.
     */
    public function index(): AnonymousResourceCollection
    {
        $locations = Location::orderBy('name')->get();

        return LocationResource::collection($locations);
    }

    /**
     * POST /api/locations (Admin only)
     *
     * Create a new location.
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::create($request->validated());

        return response()->json([
            'message' => 'Lokasi berhasil ditambahkan.',
            'data'    => new LocationResource($location),
        ], 201);
    }

    /**
     * GET /api/locations/{location}
     *
     * Show a single location.
     */
    public function show(Location $location): LocationResource
    {
        return new LocationResource($location);
    }

    /**
     * PUT /api/locations/{location} (Admin only)
     *
     * Update a location.
     */
    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $location->update($request->validated());

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui.',
            'data'    => new LocationResource($location),
        ]);
    }

    /**
     * DELETE /api/locations/{location} (Admin only)
     *
     * Delete a location.
     */
    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus.',
        ]);
    }
}

