<?php

namespace Tests\Feature;

use App\Support\Deployment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostDeployTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(Deployment::markerFile());
        @unlink(dirname(Deployment::markerFile()).'/deploy-failed-at');
        parent::tearDown();
    }

    public function test_does_nothing_outside_production(): void
    {
        $this->assertFalse(Deployment::isPending());
    }

    public function test_runs_once_per_release_in_production(): void
    {
        $this->app['env'] = 'production';
        @unlink(Deployment::markerFile());

        $this->assertTrue(Deployment::isPending());
        $this->assertTrue(Deployment::run());
        $this->assertFalse(Deployment::isPending());
        $this->assertSame(Deployment::fingerprint(), trim(file_get_contents(Deployment::markerFile())));

        // A deploy that adds a migration changes the fingerprint.
        file_put_contents(Deployment::markerFile(), 'older-release');
        $this->assertTrue(Deployment::isPending());
    }
}
