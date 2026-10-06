<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_files_exist_with_expected_sizes(): void
    {
        foreach ([
            'images/brand/kustore-logo-light.png' => [320, 64],
            'images/brand/kustore-logo-dark.png' => [320, 64],
            'images/brand/kustore-og.png' => [1200, 630],
            'apple-touch-icon.png' => [180, 180],
            'icon-192.png' => [192, 192],
            'icon-512.png' => [512, 512],
            'favicon-32x32.png' => [32, 32],
        ] as $path => [$w, $h]) {
            $size = getimagesize(public_path($path));
            $this->assertNotFalse($size, $path);
            $this->assertSame([$w, $h], [$size[0], $size[1]], $path);
        }
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
        $this->assertJson(file_get_contents(public_path('site.webmanifest')));
    }

    public function test_home_shows_light_and_dark_logo_icons_and_og_image(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('images/brand/kustore-logo-light.png', false)
            ->assertSee('images/brand/kustore-logo-dark.png', false)
            ->assertSee('alt="Kustore" width="140" height="28"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('favicon.ico', false)
            ->assertSee('<meta property="og:image" content="http://localhost/images/brand/kustore-og.png">', false)
            ->assertDontSee('KuStore');
    }

    public function test_auth_page_shows_logo(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('images/brand/kustore-logo-light.png', false)
            ->assertSee('images/brand/kustore-logo-dark.png', false);
    }

    public function test_storefront_without_avatar_falls_back_to_kustore_og_image(): void
    {
        Store::factory()->published()->create(['username' => 'tanpafoto', 'avatar_path' => null]);

        $this->get('/tanpafoto')->assertOk()
            ->assertSee('<meta property="og:image" content="http://localhost/images/brand/kustore-og.png">', false)
            ->assertSee('images/brand/kustore-logo-light.png', false);
    }
}
