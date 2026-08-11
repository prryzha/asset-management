<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('assets')
            ->orderBy('nama')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'nama'),
            ],
            'kode' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('categories', 'kode'),
            ],
        ]);

        $category = Category::create($validated);

        Cache::forget('filter_categories');

        ActivityLog::record(
            $category,
            'category.created',
            "Menambahkan kategori {$category->nama}"
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'nama')->ignore($category),
            ],
            'kode' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('categories', 'kode')->ignore($category),
            ],
        ]);

        $previousName = $category->nama;

        $category->update($validated);

        Cache::forget('filter_categories');

        if ($category->wasChanged('nama')) {
            ActivityLog::record(
                $category,
                'category.updated',
                "Mengubah kategori {$previousName} menjadi {$category->nama}",
                [
                    'nama_lama' => $previousName,
                    'nama_baru' => $category->nama,
                ]
            );
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->assets()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori yang masih dipakai aset tidak dapat dihapus.');
        }

        $name = $category->nama;

        $category->delete();

        Cache::forget('filter_categories');

        ActivityLog::record(
            $category,
            'category.deleted',
            "Menghapus kategori {$name}",
            [
                'nama' => $name,
            ]
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}