<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryId = $request->integer('category_id');
        $selectedCategory = $categoryId > 0
            ? Category::query()->withLocaleTranslations()->find($categoryId)
            : null;

        $products = Product::query()
            ->withLocaleTranslations()
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->searchTranslation($search);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('Dashboard.products.index', [
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $selectedCategory,
            'categories' => $this->categoriesForSelect(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Dashboard.products.create', [
            'categories' => $this->categoriesForSelect(),
            'locales' => $this->formLocales(),
            'product' => new Product(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), [], $this->validationAttributes());
        $imagePath = $request->hasFile('image') ? $this->storeProductImage($request->file('image')) : null;

        try {
            DB::transaction(function () use ($validated, $imagePath): void {
                $product = Product::create([
                    'category_id' => $validated['category_id'],
                    'image' => $imagePath,
                    'purchase_price' => $validated['purchase_price'],
                    'selling_price' => $validated['selling_price'],
                    'stock' => $validated['stock'],
                ]);

                $product->syncTranslations($validated['translations']);
            });
        } catch (\Throwable $exception) {
            $this->deleteProductImage($imagePath);

            throw $exception;
        }

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return redirect()->route('dashboard.products.edit', $product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('category', 'translations');

        return view('Dashboard.products.edit', [
            'categories' => $this->categoriesForSelect(),
            'product' => $product,
            'locales' => $this->formLocales(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate($this->rules(), [], $this->validationAttributes());
        $currentImagePath = $product->image;
        $newImagePath = $request->hasFile('image')
            ? $this->storeProductImage($request->file('image'))
            : $currentImagePath;

        try {
            DB::transaction(function () use ($product, $validated, $newImagePath): void {
                $product->update([
                    'category_id' => $validated['category_id'],
                    'image' => $newImagePath,
                    'purchase_price' => $validated['purchase_price'],
                    'selling_price' => $validated['selling_price'],
                    'stock' => $validated['stock'],
                ]);

                $product->syncTranslations($validated['translations']);
            });
        } catch (\Throwable $exception) {
            if ($newImagePath !== $currentImagePath) {
                $this->deleteProductImage($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== $currentImagePath) {
            $this->deleteProductImage($currentImagePath);
        }

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $imagePath = $product->image;

        $product->delete();
        $this->deleteProductImage($imagePath);

        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.products.index');
    }

    /**
     * @return list<string>
     */
    private function formLocales(): array
    {
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales', []));

        if ($supportedLocales === []) {
            return [config('app.locale', 'en')];
        }

        $currentLocale = app()->getLocale();

        return collect($supportedLocales)
            ->sort(function (string $left, string $right) use ($currentLocale) {
                return [$left !== $currentLocale, $left] <=> [$right !== $currentLocale, $right];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
        ];

        foreach ($this->formLocales() as $locale) {
            $rules["translations.{$locale}.name"] = ['required', 'string', 'max:255'];
            $rules["translations.{$locale}.description"] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = [
            'category_id' => __('site.category'),
            'image' => __('site.image'),
            'purchase_price' => __('site.purchase_price'),
            'selling_price' => __('site.selling_price'),
            'stock' => __('site.warehouse'),
        ];

        foreach ($this->formLocales() as $locale) {
            $localeLabel = __("site.locale_{$locale}");

            if ($localeLabel === "site.locale_{$locale}") {
                $localeLabel = strtoupper($locale);
            }

            $attributes["translations.{$locale}.name"] = __('site.name_in_locale', ['locale' => $localeLabel]);
            $attributes["translations.{$locale}.description"] = __('site.description_in_locale', ['locale' => $localeLabel]);
        }

        return $attributes;
    }

    private function storeProductImage(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $path = 'products/'.Str::uuid()->toString().'.'.$extension;
        Storage::disk('public')->put($path, $file->getContent());

        return $path;
    }

    private function deleteProductImage(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function categoriesForSelect()
    {
        return Category::query()
            ->withLocaleTranslations()
            ->get()
            ->sortBy('name')
            ->values();
    }
}