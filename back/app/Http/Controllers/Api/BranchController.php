<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    private const DEFAULT_RADIUS_METERS = 5000;

    public function nearest(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'lat'    => ['required', 'numeric', 'between:-90,90'],
            'lng'    => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:100', 'max:50000'],
        ]);

        $lat    = (float) $request->input('lat');
        $lng    = (float) $request->input('lng');
        $radius = $request->integer('radius', self::DEFAULT_RADIUS_METERS);

        $branches = DB::table('branches')
            ->select('branches.*')
            ->selectRaw(
                'ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) AS distance_meters',
                [$lng, $lat]
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw('distance_meters <= ?', [$radius])
            ->orderBy('distance_meters')
            ->get();

        return BranchResource::collection($branches);
    }
}
