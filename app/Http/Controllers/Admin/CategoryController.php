<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display the hierarchical category tree and management panel.
     */
    public function index()
    {
        // Fetch only root categories and eager-load their children with question counts
        $categories = Category::with([
            'children' => function ($q) {
                $q->withCount('questions')
                  ->orderBy('sort_order')
                  ->orderBy('name');
            }
        ])
        ->withCount('questions')
        ->whereNull('parent_id')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        // All categories for the parent dropdown
        $allCategories = Category::whereNull('parent_id')
            ->with(['children' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'allCategories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'name_ar'   => ['nullable', 'string', 'max:100'],
            'name_ur'   => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $category = Category::create([
            'name'       => $request->name,
            'name_ar'    => $request->name_ar,
            'name_ur'    => $request->name_ur,
            'parent_id'  => $request->filled('parent_id') ? $request->parent_id : null,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => (int) Category::where('parent_id', $request->parent_id)->max('sort_order') + 1,
            'slug'       => Str::slug($request->name) . '-' . uniqid(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'name_ar'   => ['nullable', 'string'],
            'name_ur'   => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id', function ($attribute, $value, $fail) use ($category) {
                if ($value && (int) $value === (int) $category->id) {
                    $fail('A category cannot be its own parent.');
                }
            }],
        ]);

        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;
        if ($parentId === (int) $category->id) {
            $parentId = $category->parent_id;
        }

        $isActive = $request->has('is_active') ? $request->boolean('is_active') : $category->is_active;

        $category->update([
            'name'      => $request->name,
            'name_ar'   => $request->name_ar,
            'name_ur'   => $request->name_ur,
            'parent_id' => $parentId,
            'is_active' => $isActive,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Category updated successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Quick toggle active/inactive status for a category.
     */
    public function toggleStatus(Category $category)
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => (bool) $category->is_active,
                'message'   => 'Category visibility updated.'
            ]);
        }

        return back()->with('success', 'Category visibility updated.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Re-assign or orphan child categories to root
        $category->children()->update(['parent_id' => null]);
        $category->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
