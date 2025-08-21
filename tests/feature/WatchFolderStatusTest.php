<?php

namespace Tests\feature;

class WatchFolderStatusTest extends TestCase
{

    /**
     * Ensure unauthenticated API requests return JSON 401.
     */
    public function test_unauthenticated_api_returns_json_401()
    {
        $response = $this->getJson('/api/v1/watch-folders/status');

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_authenticated_api_returns_status_200()
    {
        // Use the project's TestCase helper to create a user if available
        if (method_exists($this, 'createUser')) {
            $user = $this->createUser();
            $this->actingAs($user, 'api');
        }

        $response = $this->getJson('/api/v1/watch-folders/status');

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'timestamp', 'data']);
        $json = $response->json();
        $this->assertEquals('success', $json['status']);
    }
}
