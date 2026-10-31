@extends('layouts.admin')

@section('title', 'Categories')
@section('topbar-title', 'Category Tree Management')
@section('topbar-icon', 'bi-tags-fill')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
    <span class="text-dark fw-semibold">Categories</span>
@endsection

@push('styles')
<style>
    /* ── Scoped Category Tree Management Enterprise Styles ── */
    .cat-mgmt-container {
        font-family: inherit;
    }
    
    .cat-arabic-text {
        font-family: 'Amiri', 'Noto Naskh Arabic', serif !important;
        font-size: 1.05rem;
        line-height: 1.25;
        direction: rtl;
        display: inline-block;
    }
    
    .cat-urdu-text {
        font-family: 'Noto Nastaliq Urdu', serif !important;
        font-size: 1.05rem;
        line-height: 1.35;
        direction: rtl;
        display: inline-block;
    }

    .lang-pill-badge {
        font-size: 0.74rem;
        font-weight: 700;
        padding: 3px 8px;
        background: #F1F5F9;
        color: #64748B;
        border-radius: 6px;
        border: 1px solid #E2E8F0;
        pointer-events: none;
        letter-spacing: 0.5px;
    }

    /* ── Form Inputs Polish ── */
    .cat-form-input {
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        padding: 0.65rem 0.95rem;
        font-size: 0.88rem;
        transition: all 0.15s ease;
    }
    .cat-form-input:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* ── Parent Category Header Card ── */
    .parent-cat-card {
        border-radius: 12px;
        padding: 0.85rem 1.35rem;
        min-height: 54px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }
    .parent-cat-card:hover {
        filter: brightness(0.98);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .parent-cat-card.is-expanded {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    /* ── Nested Child Items Container ── */
    .children-tree-box {
        border: 1px solid #E2E8F0;
        border-top: none;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        background: #FFFFFF;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .child-cat-row {
        padding: 0.85rem 1.35rem;
        min-height: 52px;
        border-bottom: 1px solid #F1F5F9;
        transition: background-color 0.12s ease;
    }
    .child-cat-row:last-child {
        border-bottom: none;
    }
    .child-cat-row:hover {
        background-color: #F8FAFC;
    }

    /* ── Icon Action Buttons ── */
    .btn-icon-action {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        transition: all 0.15s ease;
    }
    .btn-icon-action:hover {
        background: #F1F5F9;
        color: #1E293B;
        border-color: #CBD5E1;
    }
    .btn-icon-action.btn-action-delete {
        border-color: #FECDD3;
        background: #FFF1F2;
        color: #E11D48;
    }
    .btn-icon-action.btn-action-delete:hover {
        background: #FEE2E2;
        border-color: #FDA4AF;
        color: #BE123C;
    }

    /* ── Chevron Rotation ── */
    .tree-toggle-arrow {
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        font-size: 0.95rem;
        font-weight: 700;
    }
    .tree-toggle-arrow.rotated {
        transform: rotate(-90deg);
    }

    .drag-grip {
        cursor: grab;
        color: #94A3B8;
        font-size: 1.1rem;
        width: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Custom Vibrant Emerald Green Toggle Switch (Reference Design) ── */
    .cat-status-switch .form-check-input {
        width: 38px;
        height: 20px;
        background-color: #CBD5E1;
        border-color: #CBD5E1;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
        cursor: pointer;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    }
    .cat-status-switch .form-check-input:checked {
        background-color: #10B981 !important;
        border-color: #10B981 !important;
    }
    .cat-status-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        border-color: #10B981;
    }

    /* ── Modern Header Outline Buttons ── */
    .btn-tree-ctrl {
        padding: 0.5rem 0.95rem;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-tree-ctrl:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #0F172A;
    }
</style>
@endpush

@section('content')
<div class="cat-mgmt-container">
    <div class="row g-4">
        
        {{-- ========================================================================= --}}
        {{-- LEFT COLUMN: Add New Category Form & Tips (~32% width)                    --}}
        {{-- ========================================================================= --}}
        <div class="col-12 col-lg-4 col-xl-4">
            
            {{-- Card 1: Add New Category / Edit Form --}}
            <div class="modern-card p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" 
                         style="width: 40px; height: 40px; background: #EFF6FF; color: #2563EB;">
                        <i class="bi bi-plus-lg fs-5" id="form-header-icon"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark" id="form-title" style="font-size: 1.02rem;">Add New Category</h6>
                        <span class="text-muted small" id="form-subtitle" style="font-size: 0.8rem;">Create a new category and place it in the hierarchy</span>
                    </div>
                </div>

                <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    {{-- Category Name (English) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Category Name (English) <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   name="name" 
                                   id="catName" 
                                   class="form-control cat-form-input pe-5" 
                                   placeholder="e.g. Marriage" 
                                   required>
                            <span class="position-absolute top-50 end-0 translate-middle-y me-2.5 lang-pill-badge">/A</span>
                        </div>
                    </div>

                    {{-- Arabic Name (Arabic) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Arabic Name (Arabic)
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   name="name_ar" 
                                   id="catNameAr" 
                                   class="form-control cat-form-input cat-arabic-text pe-5" 
                                   dir="rtl" 
                                   placeholder="مثال: الزواج">
                            <span class="position-absolute top-50 end-0 translate-middle-y me-2.5 lang-pill-badge">/A</span>
                        </div>
                    </div>

                    {{-- Urdu Name (Urdu) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Urdu Name (Urdu)
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   name="name_ur" 
                                   id="catNameUr" 
                                   class="form-control cat-form-input cat-urdu-text pe-5" 
                                   dir="rtl" 
                                   placeholder="مثال: نکاح">
                            <span class="position-absolute top-50 end-0 translate-middle-y me-2.5 lang-pill-badge">/A</span>
                        </div>
                    </div>

                    {{-- Parent Category Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Parent Category
                        </label>
                        <select name="parent_id" id="catParentId" class="form-select cat-form-input">
                            <option value="">— Top Level (Root) —</option>
                            @foreach($allCategories as $parentCat)
                                <option value="{{ $parentCat->id }}">{{ $parentCat->name }}</option>
                                @foreach($parentCat->children as $sub)
                                    <option value="{{ $sub->id }}">&nbsp;&nbsp;↳ {{ $sub->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                            Select where this category belongs in the tree.
                        </div>
                    </div>

                    {{-- Active on Public Site Toggle --}}
                    <div class="mb-4 pt-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-check-label fw-bold text-dark small d-block" for="catIsActive">
                                    Active on Public Site
                                </label>
                                <span class="text-muted small" style="font-size: 0.76rem;">
                                    Make this category visible on the public website.
                                </span>
                            </div>
                            <div class="form-check form-switch cat-status-switch mb-0 ms-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="catIsActive" value="1" checked style="cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-modern-primary py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" id="btnSubmitCategory" style="font-size: 0.92rem; border-radius: 10px;">
                            <i class="bi bi-folder-check"></i> <span id="btnSubmitText">Save Category</span>
                        </button>
                        <button type="button" class="btn btn-modern-secondary py-2 fw-semibold d-none" id="btnCancelEdit" onclick="cancelEditMode()">
                            Cancel Edit
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 2: Category Tips --}}
            <div class="modern-card p-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center gap-2 mb-3 text-primary">
                    <i class="bi bi-info-circle fs-5"></i>
                    <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">Category Tips</h6>
                </div>
                <ul class="text-muted ps-3 mb-0" style="line-height: 1.8; font-size: 0.84rem;">
                    <li>Use clear and concise names.</li>
                    <li>Arabic and Urdu names are optional but recommended.</li>
                    <li>Select the appropriate parent category.</li>
                    <li>Inactive categories will not be shown on the public site.</li>
                </ul>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- RIGHT COLUMN: Hierarchical Category Tree (~68% width)                     --}}
        {{-- ========================================================================= --}}
        <div class="col-12 col-lg-8 col-xl-8">
            <div class="modern-card p-4" style="border-radius: 16px;">
                
                {{-- Right Header Control Bar --}}
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" 
                             style="width: 42px; height: 42px; background: #EFF6FF; color: #2563EB; border-radius: 12px;">
                            <i class="bi bi-layers-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">Hierarchical Category Tree</h6>
                            <span class="text-muted small" style="font-size: 0.82rem;">Organize and manage the nested structure of fatwa topics.</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                        {{-- Live Category Search --}}
                        <div class="position-relative flex-grow-1 flex-md-grow-0" style="min-width: 230px;">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.82rem;"></i>
                            <input type="text" 
                                   id="treeSearchInput" 
                                   class="form-control cat-form-input ps-5 py-1.5" 
                                   style="font-size: 0.84rem;"
                                   placeholder="Search categories..." 
                                   oninput="filterCategoryTree(this.value)">
                        </div>

                        {{-- Expand All --}}
                        <button type="button" class="btn-tree-ctrl" onclick="expandAllCategories()">
                            <i class="bi bi-arrows-expand"></i> Expand All
                        </button>

                        {{-- Collapse All --}}
                        <button type="button" class="btn-tree-ctrl" onclick="collapseAllCategories()">
                            <i class="bi bi-arrows-collapse"></i> Collapse All
                        </button>
                    </div>
                </div>

                {{-- Category Tree Container --}}
                <div class="category-tree-root" id="categoryTreeRoot">
                    @php
                        // Harmonious Soft Pastel Theme Palettes (Reference Design)
                        $groupPalettes = [
                            // 1. Soft Emerald Green (Faiths & Beliefs)
                            ['bg' => '#ECFDF5', 'border' => '#A7F3D0', 'text' => '#065F46', 'badgeBg' => '#D1FAE5', 'badgeText' => '#065F46', 'iconColor' => '#059669'],
                            // 2. Soft Sky Blue (Prayers & Duties)
                            ['bg' => '#EFF6FF', 'border' => '#BFDBFE', 'text' => '#1E40AF', 'badgeBg' => '#DBEAFE', 'badgeText' => '#1E40AF', 'iconColor' => '#2563EB'],
                            // 3. Soft Lavender Purple (Social & Family)
                            ['bg' => '#FAF5FF', 'border' => '#E9D5FF', 'text' => '#6B21A8', 'badgeBg' => '#F3E8FF', 'badgeText' => '#6B21A8', 'iconColor' => '#9333EA'],
                            // 4. Soft Warm Amber (Transactions & Finance)
                            ['bg' => '#FFFBEB', 'border' => '#FDE68A', 'text' => '#92400E', 'badgeBg' => '#FEF3C7', 'badgeText' => '#92400E', 'iconColor' => '#D97706'],
                            // 5. Soft Rose Crimson (Food & Lifestyle)
                            ['bg' => '#FFF1F2', 'border' => '#FECDD3', 'text' => '#9F1239', 'badgeBg' => '#FFE4E6', 'badgeText' => '#9F1239', 'iconColor' => '#E11D48'],
                            // 6. Soft Ocean Teal (Ethics & Conduct)
                            ['bg' => '#F0FDFA', 'border' => '#99F6E4', 'text' => '#115E59', 'badgeBg' => '#CCFBF1', 'badgeText' => '#115E59', 'iconColor' => '#0D9488'],
                            // 7. Soft Sky Cyan (Miscellaneous)
                            ['bg' => '#F0F9FF', 'border' => '#BAE6FD', 'text' => '#0369A1', 'badgeBg' => '#E0F2FE', 'badgeText' => '#0369A1', 'iconColor' => '#0284C7'],
                        ];
                    @endphp

                    @forelse($categories as $index => $rootCat)
                        @php
                            $theme = $groupPalettes[$index % count($groupPalettes)];
                            $childCount = $rootCat->children->count();
                            // First group expanded by default (as in screenshot), others collapsed
                            $isDefaultExpanded = ($index === 0);
                        @endphp

                        <div class="parent-category-block mb-3" 
                             id="parentBlock-{{ $rootCat->id }}" 
                             data-cat-name="{{ strtolower($rootCat->name) }}"
                             data-cat-ar="{{ $rootCat->name_ar }}"
                             data-cat-ur="{{ $rootCat->name_ur }}">
                            
                            {{-- Parent Header Bar --}}
                            <div class="parent-cat-card d-flex align-items-center justify-content-between {{ $isDefaultExpanded ? 'is-expanded' : '' }}" 
                                 id="parentCard-{{ $rootCat->id }}"
                                 style="background: {{ $theme['bg'] }}; border: 1px solid {{ $theme['border'] }};"
                                 onclick="toggleCategoryBranch({{ $rootCat->id }})">
                                
                                <div class="d-flex align-items-center gap-2.5 flex-grow-1 overflow-hidden" style="cursor: pointer;">
                                    {{-- Chevron Arrow --}}
                                    <i class="bi bi-chevron-down tree-toggle-arrow {{ $isDefaultExpanded ? '' : 'rotated' }}" 
                                       id="chevron-{{ $rootCat->id }}" 
                                       style="color: {{ $theme['text'] }};"></i>

                                    {{-- Folder Icon --}}
                                    <i class="bi bi-folder-fill" style="color: {{ $theme['iconColor'] }}; font-size: 1.35rem; margin-right: 4px;"></i>

                                    {{-- English Name + Arabic Name --}}
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <span class="fw-bold text-dark text-truncate" style="font-size: 0.98rem; letter-spacing: -0.2px;">
                                            {{ $rootCat->name }}
                                        </span>
                                        @if($rootCat->name_ar)
                                            <span class="text-muted small" style="margin: 0 4px;">—</span>
                                            <span class="cat-arabic-text text-truncate" style="color: {{ $theme['text'] }}; font-weight: 700; font-size: 1.12rem;">
                                                {{ $rootCat->name_ar }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Right Controls: Count Badge + Three-dot Menu --}}
                                <div class="d-flex align-items-center gap-2.5" onclick="event.stopPropagation();">
                                    <span class="badge rounded-pill fw-bold" 
                                          style="background: {{ $theme['badgeBg'] }}; color: {{ $theme['badgeText'] }}; font-size: 0.78rem; padding: 5px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                        {{ $childCount }} {{ Str::plural('category', $childCount) }}
                                    </span>

                                    {{-- Three-dot Menu --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm p-1 text-muted d-inline-flex align-items-center justify-content-center" 
                                                type="button" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false" 
                                                style="border: none; background: transparent; width: 32px; height: 32px; border-radius: 8px;">
                                            <i class="bi bi-three-dots-vertical fs-6"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1.5" style="border-radius: 12px; font-size: 0.84rem;">
                                            <li>
                                                <button class="dropdown-item py-1.5" onclick="editCategory({{ $rootCat->id }}, '{{ addslashes($rootCat->name) }}', '{{ addslashes($rootCat->name_ar ?? '') }}', '{{ addslashes($rootCat->name_ur ?? '') }}', {{ $rootCat->is_active ? 1 : 0 }}, null)">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Parent Category
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item py-1.5" onclick="prepareAddSubcategory({{ $rootCat->id }}, '{{ addslashes($rootCat->name) }}')">
                                                    <i class="bi bi-plus-circle text-success me-2"></i>Add Subcategory Here
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <button class="dropdown-item py-1.5 text-danger" onclick="openDeleteModal({{ $rootCat->id }}, '{{ addslashes($rootCat->name) }}', {{ $rootCat->questions_count ?? 0 }})">
                                                    <i class="bi bi-trash3 me-2"></i>Delete Parent Category
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Children Tree Container --}}
                            <div class="children-tree-box {{ $isDefaultExpanded ? '' : 'd-none' }}" id="children-{{ $rootCat->id }}">
                                @forelse($rootCat->children as $child)
                                    <div class="child-cat-row d-flex align-items-center justify-content-between" 
                                         id="childRow-{{ $child->id }}"
                                         data-cat-name="{{ strtolower($child->name) }}"
                                         data-cat-ar="{{ $child->name_ar }}"
                                         data-cat-ur="{{ $child->name_ur }}">
                                        
                                        {{-- Left: Drag Grip + Doc Icon + Name (EN) - Name (AR) --}}
                                        <div class="d-flex align-items-center gap-2.5 flex-grow-1 overflow-hidden">
                                            <i class="bi bi-grip-vertical drag-grip" title="Drag handle"></i>
                                            <i class="bi bi-file-earmark-text" style="color: #64748B; font-size: 1.15rem; width: 22px; display: inline-flex; align-items: center; justify-content: center; margin-right: 4px;"></i>
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <span class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">
                                                    {{ $child->name }}
                                                </span>
                                                @if($child->name_ar)
                                                    <span class="text-muted small" style="margin: 0 4px;">—</span>
                                                    <span class="cat-arabic-text text-truncate" style="color: #475569; font-size: 1.05rem; font-weight: 600;">
                                                        {{ $child->name_ar }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Right: Question Count + Emerald Toggle Switch + Edit + Delete --}}
                                        <div class="d-flex align-items-center gap-3 flex-shrink-0 ms-2">
                                            {{-- Question / Fatwa Count Badge --}}
                                            <span class="badge" style="background: #EFF6FF; color: #2563EB; font-weight: 700; border-radius: 20px; padding: 4px 12px; font-size: 0.78rem; border: 1px solid #DBEAFE; min-width: 34px; text-align: center;">
                                                {{ $child->questions_count ?? 0 }}
                                            </span>

                                            {{-- Active Status Toggle (Vibrant Emerald Green) --}}
                                            <div class="form-check form-switch cat-status-switch mb-0" title="Toggle visibility on public site">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       role="switch" 
                                                       id="toggle-{{ $child->id }}" 
                                                       {{ $child->is_active ? 'checked' : '' }} 
                                                       onchange="toggleCategoryStatus({{ $child->id }}, this)">
                                            </div>

                                            {{-- Edit Button --}}
                                            <button type="button" 
                                                    class="btn-icon-action" 
                                                    title="Edit Category"
                                                    onclick="editCategory({{ $child->id }}, '{{ addslashes($child->name) }}', '{{ addslashes($child->name_ar ?? '') }}', '{{ addslashes($child->name_ur ?? '') }}', {{ $child->is_active ? 1 : 0 }}, {{ $child->parent_id }})">
                                                <i class="bi bi-pencil" style="font-size: 0.82rem;"></i>
                                            </button>

                                            {{-- Delete Button --}}
                                            <button type="button" 
                                                    class="btn-icon-action btn-action-delete" 
                                                    title="Delete Category"
                                                    onclick="openDeleteModal({{ $child->id }}, '{{ addslashes($child->name) }}', {{ $child->questions_count ?? 0 }})">
                                                <i class="bi bi-trash3" style="font-size: 0.84rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-muted small">
                                        <i class="bi bi-folder2 me-1"></i> No subcategories yet in this group.
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-folder2-open display-4 text-slate-300 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">No Categories Found</h6>
                            <p class="small text-muted mb-0">Create your first category using the form on the left.</p>
                        </div>
                    @endforelse

                    {{-- Empty Search State --}}
                    <div id="emptySearchState" class="text-center py-5 text-muted d-none">
                        <i class="bi bi-search fs-1 text-slate-300 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">No Matching Categories</h6>
                        <p class="small text-muted mb-3">No categories matched your search term.</p>
                        <button type="button" class="btn btn-sm btn-modern-primary" onclick="clearTreeSearch()">
                            Clear Search
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

{{-- Safe Delete Confirmation Modal --}}
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                     style="width: 56px; height: 56px; background: #FFF1F2; color: #E11D48;">
                    <i class="bi bi-exclamation-triangle fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="deleteCategoryModalLabel">Delete Category?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to delete <strong class="text-dark" id="deleteCategoryName">this category</strong>?
                </p>
                <div id="deleteCategoryWarning" class="alert alert-warning py-2 px-3 small text-start d-none mb-3" style="border-radius: 10px;">
                    <i class="bi bi-info-circle me-1"></i>
                    This category currently has <strong id="deleteQuestionCount">0</strong> fatwa inquiries attached. They will remain intact but will have their category unassigned.
                </div>
                <form id="deleteCategoryForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-modern-secondary px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill fw-semibold">Delete Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Expand / Collapse Single Branch
    function toggleCategoryBranch(parentId) {
        const box = document.getElementById(`children-${parentId}`);
        const chevron = document.getElementById(`chevron-${parentId}`);
        const card = document.getElementById(`parentCard-${parentId}`);
        if (!box) return;

        if (box.classList.contains('d-none')) {
            box.classList.remove('d-none');
            if (chevron) chevron.classList.remove('rotated');
            if (card) card.classList.add('is-expanded');
        } else {
            box.classList.add('d-none');
            if (chevron) chevron.classList.add('rotated');
            if (card) card.classList.remove('is-expanded');
        }
    }

    // Expand All Branches
    function expandAllCategories() {
        document.querySelectorAll('.children-tree-box').forEach(box => box.classList.remove('d-none'));
        document.querySelectorAll('.tree-toggle-arrow').forEach(chevron => chevron.classList.remove('rotated'));
        document.querySelectorAll('.parent-cat-card').forEach(card => card.classList.add('is-expanded'));
    }

    // Collapse All Branches
    function collapseAllCategories() {
        document.querySelectorAll('.children-tree-box').forEach(box => box.classList.add('d-none'));
        document.querySelectorAll('.tree-toggle-arrow').forEach(chevron => chevron.classList.add('rotated'));
        document.querySelectorAll('.parent-cat-card').forEach(card => card.classList.remove('is-expanded'));
    }

    // Edit Category Mode
    function editCategory(id, name, nameAr, nameUr, isActive, parentId) {
        const form = document.getElementById('categoryForm');
        const formMethod = document.getElementById('formMethod');
        const formTitle = document.getElementById('form-title');
        const formSubtitle = document.getElementById('form-subtitle');
        const formHeaderIcon = document.getElementById('form-header-icon');
        const btnSubmitText = document.getElementById('btnSubmitText');
        const btnCancel = document.getElementById('btnCancelEdit');

        form.action = `/admin/categories/${id}`;
        formMethod.value = 'PUT';

        formTitle.textContent = 'Edit Category';
        formSubtitle.textContent = `Update details for "${name}"`;
        formHeaderIcon.className = 'bi bi-pencil-square fs-5';
        btnSubmitText.textContent = 'Update Category';
        btnCancel.classList.remove('d-none');

        // Populate fields
        document.getElementById('catName').value = name;
        document.getElementById('catNameAr').value = nameAr || '';
        document.getElementById('catNameUr').value = nameUr || '';
        document.getElementById('catParentId').value = parentId || '';
        document.getElementById('catIsActive').checked = (isActive === 1);

        // Smooth scroll to form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Cancel Edit Mode
    function cancelEditMode() {
        const form = document.getElementById('categoryForm');
        const formMethod = document.getElementById('formMethod');
        const formTitle = document.getElementById('form-title');
        const formSubtitle = document.getElementById('form-subtitle');
        const formHeaderIcon = document.getElementById('form-header-icon');
        const btnSubmitText = document.getElementById('btnSubmitText');
        const btnCancel = document.getElementById('btnCancelEdit');

        form.reset();
        form.action = "{{ route('admin.categories.store') }}";
        formMethod.value = 'POST';

        formTitle.textContent = 'Add New Category';
        formSubtitle.textContent = 'Create a new category and place it in the hierarchy';
        formHeaderIcon.className = 'bi bi-plus-lg fs-5';
        btnSubmitText.textContent = 'Save Category';
        btnCancel.classList.add('d-none');
        document.getElementById('catIsActive').checked = true;
    }

    // Context action: Add Subcategory under selected parent
    function prepareAddSubcategory(parentId, parentName) {
        cancelEditMode();
        document.getElementById('catParentId').value = parentId;
        const nameInput = document.getElementById('catName');
        nameInput.focus();
        nameInput.placeholder = `e.g. Subtopic of ${parentName}`;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Toggle Active / Inactive Status via AJAX
    function toggleCategoryStatus(id, checkboxEl) {
        const originalState = !checkboxEl.checked;
        checkboxEl.disabled = true;

        fetch(`/admin/categories/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            checkboxEl.disabled = false;
            if (data.success) {
                checkboxEl.checked = data.is_active;
            } else {
                checkboxEl.checked = originalState;
                alert('Could not update category status.');
            }
        })
        .catch(err => {
            checkboxEl.disabled = false;
            checkboxEl.checked = originalState;
            console.error('Error toggling status:', err);
        });
    }

    // Safe Delete Modal
    function openDeleteModal(id, name, questionCount) {
        const form = document.getElementById('deleteCategoryForm');
        form.action = `/admin/categories/${id}`;

        document.getElementById('deleteCategoryName').textContent = `"${name}"`;

        const warningEl = document.getElementById('deleteCategoryWarning');
        const countEl = document.getElementById('deleteQuestionCount');
        if (questionCount > 0) {
            countEl.textContent = questionCount;
            warningEl.classList.remove('d-none');
        } else {
            warningEl.classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
        modal.show();
    }

    // Real-Time Live Category Search
    function filterCategoryTree(searchTerm) {
        const term = searchTerm.trim().toLowerCase();
        const parentBlocks = document.querySelectorAll('.parent-category-block');
        const emptyState = document.getElementById('emptySearchState');
        let totalVisibleMatches = 0;

        if (!term) {
            parentBlocks.forEach(block => {
                block.classList.remove('d-none');
                block.querySelectorAll('.child-cat-row').forEach(child => child.classList.remove('d-none'));
            });
            emptyState.classList.add('d-none');
            return;
        }

        parentBlocks.forEach(block => {
            const pNameEn = block.getAttribute('data-cat-name') || '';
            const pNameAr = block.getAttribute('data-cat-ar') || '';
            const pNameUr = block.getAttribute('data-cat-ur') || '';
            const parentMatches = pNameEn.includes(term) || pNameAr.includes(term) || pNameUr.includes(term);

            let matchingChildren = 0;
            const childRows = block.querySelectorAll('.child-cat-row');
            childRows.forEach(child => {
                const cNameEn = child.getAttribute('data-cat-name') || '';
                const cNameAr = child.getAttribute('data-cat-ar') || '';
                const cNameUr = child.getAttribute('data-cat-ur') || '';
                const childMatches = cNameEn.includes(term) || cNameAr.includes(term) || cNameUr.includes(term);

                if (parentMatches || childMatches) {
                    child.classList.remove('d-none');
                    matchingChildren++;
                } else {
                    child.classList.add('d-none');
                }
            });

            if (parentMatches || matchingChildren > 0) {
                block.classList.remove('d-none');
                // Automatically expand parents that contain matching children
                const childrenBox = block.querySelector('.children-tree-box');
                const chevron = block.querySelector('.tree-toggle-arrow');
                const card = block.querySelector('.parent-cat-card');
                if (childrenBox) childrenBox.classList.remove('d-none');
                if (chevron) chevron.classList.remove('rotated');
                if (card) card.classList.add('is-expanded');
                totalVisibleMatches++;
            } else {
                block.classList.add('d-none');
            }
        });

        if (totalVisibleMatches === 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }
    }

    function clearTreeSearch() {
        const input = document.getElementById('treeSearchInput');
        input.value = '';
        filterCategoryTree('');
    }
</script>
@endpush

@endsection
