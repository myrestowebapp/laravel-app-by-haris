<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\QuestionAndAnswer;
use App\Models\FatwaFeaturedRule;
use App\Models\Madhab;
use Illuminate\Http\Request;

class FeaturedRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = FatwaFeaturedRule::with(['category', 'madhab'])
            ->orderByDesc('priority');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('section_label', 'like', "%{$term}%")
                  ->orWhere('section_sub', 'like', "%{$term}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('madhab', fn($m) => $m->where('name', 'like', "%{$term}%"));
            });
        }

        $rules = $query->get();
        return view('admin.featured-rules.index', compact('rules'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        $madhabs    = Madhab::all();
        $fatwas     = QuestionAndAnswer::where('status', 'published')->latest('published_at')->take(50)->get();
        return view('admin.featured-rules.create', compact('categories', 'madhabs', 'fatwas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'hijri_month'     => 'nullable|integer|min:1|max:12',
            'hijri_day_start' => 'nullable|integer|min:1|max:30',
            'hijri_day_end'   => 'nullable|integer|min:1|max:30',
            'country_codes'   => 'nullable|array',
            'country_codes.*' => 'string|size:2',
            'category_id'     => 'nullable|exists:categories,id',
            'madhab_id'       => 'nullable|exists:madhabs,id',
            'fatwa_ids'       => 'required|array|min:1',
            'fatwa_ids.*'     => 'exists:question_and_answer,id',
            'section_label'   => 'required|string|max:120',
            'section_sub'     => 'nullable|string|max:300',
            'limit'           => 'required|integer|min:3|max:12',
            'priority'        => 'required|integer|min:0',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        FatwaFeaturedRule::create($data);

        return redirect()->route('admin.featured-rules.index')
            ->with('success', 'Featured rule created successfully.');
    }

    public function edit(FatwaFeaturedRule $featuredRule)
    {
        $categories = Category::whereNull('parent_id')->get();
        $madhabs    = Madhab::all();
        $fatwas     = QuestionAndAnswer::where('status', 'published')->latest('published_at')->take(50)->get();
        return view('admin.featured-rules.edit', compact('featuredRule', 'categories', 'madhabs', 'fatwas'));
    }

    public function update(Request $request, FatwaFeaturedRule $featuredRule)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'hijri_month'     => 'nullable|integer|min:1|max:12',
            'hijri_day_start' => 'nullable|integer|min:1|max:30',
            'hijri_day_end'   => 'nullable|integer|min:1|max:30',
            'country_codes'   => 'nullable|array',
            'country_codes.*' => 'string|size:2',
            'category_id'     => 'nullable|exists:categories,id',
            'madhab_id'       => 'nullable|exists:madhabs,id',
            'fatwa_ids'       => 'required|array|min:1',
            'fatwa_ids.*'     => 'exists:question_and_answer,id',
            'section_label'   => 'required|string|max:120',
            'section_sub'     => 'nullable|string|max:300',
            'limit'           => 'required|integer|min:3|max:12',
            'priority'        => 'required|integer|min:0',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $featuredRule->update($data);

        return redirect()->route('admin.featured-rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    public function destroy(FatwaFeaturedRule $featuredRule)
    {
        $featuredRule->delete();
        return back()->with('success', 'Rule deleted.');
    }

    public function toggleActive(FatwaFeaturedRule $featuredRule)
    {
        $featuredRule->update(['is_active' => !$featuredRule->is_active]);
        return back()->with('success', 'Rule ' . ($featuredRule->is_active ? 'disabled' : 'enabled') . '.');
    }
}
