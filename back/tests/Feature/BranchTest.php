<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_nearest_returns_422_without_required_params(): void
    {
        $response = $this->getJson('/api/branches/nearest');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_lat_out_of_range(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=91&lng=0');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_lng_out_of_range(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=0&lng=181');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_radius_too_small(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=50&lng=30&radius=50');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_radius_too_large(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=50&lng=30&radius=100000');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_lat_negative_out_of_range(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=-91&lng=0');

        $response->assertStatus(422);
    }

    public function test_nearest_returns_422_when_lng_negative_out_of_range(): void
    {
        $response = $this->getJson('/api/branches/nearest?lat=0&lng=-181');

        $response->assertStatus(422);
    }
}
