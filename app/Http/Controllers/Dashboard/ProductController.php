<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ImageUploader;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        return view('dashboard.products.index', [
            'store' => $store,
            'products' => $store->products()->with('mainImage')->ordered()->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $product = new Product(['type' => ProductType::Physical, 'is_active' => true, 'requires_shipping' => true, 'currency' => $request->user()->store->currency, 'stock_quantity' => 10]);

        return view('dashboard.products.form', ['store' => $request->user()->store, 'product' => $product]);
    }

    public function store(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        $data = $this->validated($request);
        $product = new Product($data);
        $product->store()->associate($store);
        $product->slug = Product::uniqueSlugFor($store, $data['slug'] ?: $data['name']);
        $product->position = (int) $store->products()->max('position') + 1;
        $product->save();

        $this->handleImage($request, $product, $uploader);

        return redirect()->route('dashboard.products.index')->with('status', 'Product created.');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('update', $product);

        return view('dashboard.products.form', ['store' => $request->user()->store, 'product' => $product->load('mainImage')]);
    }

    public function update(Request $request, Product $product, ImageUploader $uploader): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $this->validated($request, $product);
        $product->fill($data);
        $product->slug = Product::uniqueSlugFor($product->store, $data['slug'] ?: $data['name'], $product->id);
        $product->save();

        $this->handleImage($request, $product, $uploader);

        return redirect()->route('dashboard.products.index')->with('status', 'Product saved.');
    }

    public function destroy(Product $product, ImageUploader $uploader): RedirectResponse
    {
        $this->authorize('delete', $product);
        foreach ($product->images as $image) {
            $uploader->delete($image->path);
        }
        $product->delete();

        return redirect()->route('dashboard.products.index')->with('status', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $currency = $request->user()->store->currency;
        $request->merge([
            'price' => Money::parse($request->input('price'), $currency),
            'sale_price' => Money::parse($request->input('sale_price'), $currency),
            'slug' => $request->filled('slug') ? Str::slug((string) $request->input('slug')) : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:90', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'type' => ['required', Rule::enum(ProductType::class)],
            'sku' => ['nullable', 'string', 'max:64'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'unlimited_stock' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', ...ImageUploader::rules()],
            'remove_image' => ['nullable', 'boolean'],
        ], ['sale_price.lt' => 'The sale price must be lower than the regular price.']);

        $data['currency'] = $currency;
        foreach (['unlimited_stock', 'is_active', 'is_featured'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        // Shipping defaults by type when the field isn't sent at all.
        $data['requires_shipping'] = $request->has('requires_shipping_present')
            ? $request->boolean('requires_shipping')
            : ProductType::from($data['type'])->requiresShippingByDefault();
        $data['stock_quantity'] = $data['unlimited_stock'] ? null : (int) ($data['stock_quantity'] ?? 0);
        unset($data['image'], $data['remove_image']);

        return $data;
    }

    private function handleImage(Request $request, Product $product, ImageUploader $uploader): void
    {
        $current = $product->mainImage()->first();

        if ($request->hasFile('image')) {
            $path = $uploader->store($request->file('image'), 'products');
            if ($current) {
                $uploader->delete($current->path);
                $current->update(['path' => $path, 'alt' => $product->name]);
            } else {
                $product->images()->create(['path' => $path, 'alt' => $product->name, 'is_main' => true]);
            }
        } elseif ($request->boolean('remove_image') && $current) {
            $uploader->delete($current->path);
            $current->delete();
        }
    }
}
