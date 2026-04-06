<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $categories = Category::query()
            ->withLocaleTranslations()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->searchTranslation($search);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('Dashboard.categories.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Dashboard.categories.create', [
            'locales' => $this->formLocales(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->translationRules());

        DB::transaction(function () use ($validated): void {
            $category = new Category();
            $category->save();
            $category->syncTranslations($validated['translations']);
        });

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return redirect()->route('dashboard.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $category->load('translations');

        return view('Dashboard.categories.edit', [
            'category' => $category,
            'locales' => $this->formLocales(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate($this->translationRules());

        DB::transaction(function () use ($category, $validated): void {
            $category->syncTranslations($validated['translations']);
        });

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.categories.index');
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
    private function translationRules(): array
    {
        $rules = [
            'translations' => ['required', 'array'],
        ];

        foreach ($this->formLocales() as $locale) {
            $rules["translations.{$locale}.name"] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }
}
