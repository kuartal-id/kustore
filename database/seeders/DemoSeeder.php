<?php

namespace Database\Seeders;

use App\Enums\VerificationLevel;
use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Local demo accounts (password login). Password: "kustore-demo"
        $demoUser = User::create([
            'name' => 'Sekar Ayu',
            'email' => 'demo@kustore.test',
            'password' => 'kustore-demo',
            'email_verified_at' => now(),
        ]);
        $demoUser->forceFill(['is_admin' => true])->save();

        $demo = $this->store($demoUser, [
            'username' => 'demo',
            'display_name' => 'Sekar Studio',
            'bio' => "Hand-drawn batik bags, scarves and stationery from Yogyakarta.\nSmall batches, natural dyes, made to last.",
            'location' => 'Yogyakarta',
            'website' => 'https://sekarstudio.example',
            'category' => 'Art & Design',
            'account_type' => 'individual',
            'layout' => 'minimal',
            'payment_instructions' => "Transfer to BCA 0123456789 a.n. Sekar Ayu Pratiwi\nor GoPay / OVO 0812 3456 7890.\n\nSend your proof of payment on WhatsApp 0812 3456 7890 with your order number. We ship within 2 working days.",
        ], VerificationLevel::KuartalId, 'sekar');

        $this->links($demo, [
            ['Instagram', 'https://instagram.com/sekarstudio', 'instagram'],
            ['Chat on WhatsApp', 'https://wa.me/6281234567890', 'whatsapp'],
            ['Watch the process on TikTok', 'https://tiktok.com/@sekarstudio', 'tiktok'],
            ['Batik tutorials on YouTube', 'https://youtube.com/@sekarstudio', 'youtube'],
            ['Workshop schedule', 'https://sekarstudio.example/workshops', 'website'],
        ]);

        $tote = $this->product($demo, 'Batik tote bag · Parang', 'physical', 289000, 249000, 12, true, [28, 54, 64], 'Hand-drawn parang motif on heavy cotton canvas. Inside pocket, magnetic snap, 40 × 35 cm. Each bag is dyed by hand, so the colours vary slightly.');
        $this->product($demo, 'Silk scarf · Kawung', 'physical', 175000, null, 3, false, [182, 140, 96], "Lightweight silk, 90 × 90 cm, natural indigo and soga dyes.\nComes folded in a recycled gift box.");
        $this->product($demo, 'Batik pattern pack', 'digital', 75000, null, null, false, [54, 204, 100], '24 high-resolution batik patterns (PNG + SVG) for personal and small commercial projects. Download link sent after payment.');
        $this->product($demo, '1:1 batik workshop (2 hours)', 'service', 450000, null, null, false, [120, 150, 170], 'Learn canting and dyeing basics in our studio. All materials included, take home your own piece. Schedule by WhatsApp after ordering.');
        $this->product($demo, 'Indigo notebook', 'physical', 65000, null, 0, false, [46, 74, 120], 'A5 dotted notebook with a batik-printed cover. 120 pages, lay-flat binding.');

        // Second store showing the Commerce layout
        $kopiUser = User::create([
            'name' => 'Bima Santoso',
            'email' => 'kopi@kustore.test',
            'password' => 'kustore-demo',
            'email_verified_at' => now(),
        ]);
        $kopi = $this->store($kopiUser, [
            'username' => 'kopinusantara',
            'display_name' => 'Kopi Nusantara',
            'bio' => 'Single-origin Indonesian coffee, roasted every Monday in Bandung. Free delivery in Bandung for orders above Rp 200.000.',
            'location' => 'Bandung',
            'website' => 'https://kopinusantara.example',
            'category' => 'Food & Drink',
            'account_type' => 'business',
            'layout' => 'commerce',
            'payment_instructions' => "Transfer to Mandiri 1230004567 a.n. PT Kopi Nusantara.\nConfirm on WhatsApp 0813 0000 1111.",
        ], VerificationLevel::Business, 'kopi');
        $this->links($kopi, [
            ['Instagram', 'https://instagram.com/kopinusantara', 'instagram'],
            ['WhatsApp', 'https://wa.me/6281300001111', 'whatsapp'],
            ['Spotify playlist', 'https://open.spotify.com', 'spotify'],
        ]);
        foreach ([
            ['Gayo Aceh · 250 g', 98000, null, 40, true, [92, 64, 51]],
            ['Toraja Sapan · 250 g', 115000, 99000, 25, false, [120, 82, 60]],
            ['Kintamani Bali · 250 g', 105000, null, 18, false, [150, 105, 72]],
            ['Java Ijen · 250 g', 92000, null, 30, false, [70, 50, 40]],
            ['Cold brew kit', 185000, null, 8, false, [28, 54, 64]],
            ['Barista class (3 hours)', 350000, null, null, false, [54, 120, 100]],
        ] as [$name, $price, $sale, $stock, $featured, $rgb]) {
            $this->product($kopi, $name, str_contains($name, 'class') ? 'service' : 'physical', $price, $sale, $stock, $featured, $rgb, 'Freshly roasted. Tasting notes on the label.');
        }

        // A few orders + analytics so the dashboard has something to show
        $checkout = app(CheckoutService::class);
        $buyers = [['Dewi Lestari', 'dewi@example.com', 2], ['Andi Wijaya', 'andi@example.com', 1], ['Putri Maharani', 'putri@example.com', 1]];
        foreach ($buyers as $i => [$name, $email, $qty]) {
            [$order] = $checkout->placeOrder($tote->fresh('store'), [
                'name' => $name, 'email' => $email, 'phone' => '0812000000'.$i,
                'shipping_address' => 'Jl. Malioboro No. '.(10 + $i).', Yogyakarta 55271', 'quantity' => $qty, 'notes' => null,
            ]);
            if ($i > 0) {
                $order->update(['payment_status' => 'paid', 'paid_at' => now(), 'fulfillment_status' => $i === 1 ? 'shipped' : 'processing']);
            }
            $order->forceFill(['created_at' => now()->subHours(($i + 1) * 7)])->save();
        }

        foreach ([$demo, $kopi] as $store) {
            $rows = [];
            for ($d = 0; $d < 30; $d++) {
                $views = random_int(8, 40);
                for ($v = 0; $v < $views; $v++) {
                    $rows[] = ['store_id' => $store->id, 'type' => AnalyticsEvent::STORE_VIEW, 'visitor_hash' => hash('sha256', $store->id.$d.$v), 'created_at' => now()->subDays($d)];
                    if ($v % 3 === 0) {
                        $rows[] = ['store_id' => $store->id, 'type' => AnalyticsEvent::LINK_CLICK, 'visitor_hash' => hash('sha256', 'c'.$store->id.$d.$v), 'created_at' => now()->subDays($d)];
                    }
                }
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                AnalyticsEvent::insert($chunk);
            }
        }
    }

    private function store(User $user, array $attrs, VerificationLevel $level, string $avatarSeed): Store
    {
        $store = new Store($attrs + ['color_mode' => 'light', 'currency' => 'IDR']);
        $store->user()->associate($user);
        $store->verification_level = $level;
        $store->is_published = true;
        $store->published_at = now();
        $store->avatar_path = $this->avatar($attrs['display_name'], $avatarSeed);
        $store->save();

        return $store;
    }

    private function links(Store $store, array $links): void
    {
        foreach ($links as $i => [$title, $url, $icon]) {
            $store->links()->create(['title' => $title, 'url' => $url, 'icon' => $icon, 'is_visible' => true, 'position' => $i + 1])
                ->forceFill(['clicks_count' => random_int(5, 120)])->save();
        }
    }

    private function product(Store $store, string $name, string $type, int $price, ?int $sale, ?int $stock, bool $featured, array $rgb, string $description)
    {
        $product = $store->products()->create([
            'name' => $name,
            'slug' => \App\Models\Product::uniqueSlugFor($store, $name),
            'description' => $description,
            'price' => $price,
            'sale_price' => $sale,
            'currency' => 'IDR',
            'type' => $type,
            'stock_quantity' => $stock,
            'unlimited_stock' => $stock === null,
            'is_active' => true,
            'is_featured' => $featured,
            'requires_shipping' => $type === 'physical',
        ]);
        $product->images()->create(['path' => $this->productImage($rgb, $product->id), 'alt' => $name, 'is_main' => true]);

        return $product->setRelation('store', $store);
    }

    /** Flat, abstract placeholder "product shot" so screenshots aren't empty. */
    private function productImage(array $rgb, int $seed): string
    {
        $s = 800;
        $im = imagecreatetruecolor($s, $s);
        imageantialias($im, true);
        imagefill($im, 0, 0, imagecolorallocate($im, 236, 242, 244));
        [$r, $g, $b] = $rgb;
        $main = imagecolorallocate($im, $r, $g, $b);
        $light = imagecolorallocate($im, min(255, $r + 60), min(255, $g + 60), min(255, $b + 60));
        $shadow = imagecolorallocate($im, 214, 224, 228);
        imagefilledellipse($im, 400, 650, 460, 60, $shadow);
        switch ($seed % 3) {
            case 0:
                imagefilledrectangle($im, 220, 260, 580, 640, $main);
                imagesetthickness($im, 18);
                imagearc($im, 400, 260, 200, 200, 180, 360, $main);
                imagefilledrectangle($im, 220, 400, 580, 420, $light);
                break;
            case 1:
                imagefilledellipse($im, 400, 420, 380, 380, $main);
                imagefilledellipse($im, 400, 420, 200, 200, $light);
                break;
            default:
                imagefilledrectangle($im, 250, 190, 550, 640, $main);
                imagefilledrectangle($im, 290, 240, 510, 330, $light);
        }
        $path = 'products/demo-'.$seed.'-'.bin2hex(random_bytes(6)).'.webp';
        ob_start();
        imagewebp($im, null, 85);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($im);

        return $path;
    }

    private function avatar(string $name, string $seed): string
    {
        $s = 400;
        $im = imagecreatetruecolor($s, $s);
        $bg = $seed === 'kopi' ? imagecolorallocate($im, 92, 64, 51) : imagecolorallocate($im, 28, 54, 64);
        imagefill($im, 0, 0, $bg);
        $fg = imagecolorallocate($im, 241, 251, 255);
        $acc = imagecolorallocate($im, 54, 204, 100);
        if ($seed === 'kopi') {
            imagefilledellipse($im, 200, 200, 190, 250, $fg);
            imagesetthickness($im, 14);
            imagearc($im, 200, 200, 60, 250, 270, 90, $bg);
        } else {
            for ($i = 0; $i < 4; $i++) {
                imagefilledellipse($im, 140 + ($i % 2) * 120, 140 + intdiv($i, 2) * 120, 110, 110, $i === 3 ? $acc : $fg);
            }
        }
        $path = 'avatars/demo-'.$seed.'-'.bin2hex(random_bytes(6)).'.webp';
        ob_start();
        imagewebp($im, null, 90);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($im);

        return $path;
    }
}
