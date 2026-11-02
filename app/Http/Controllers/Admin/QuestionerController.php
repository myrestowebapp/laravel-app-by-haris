<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\QuestionAndAnswer;
use Illuminate\Http\Request;

class QuestionerController extends Controller
{
    /**
     * List all users who have ever asked a question with dynamic sorting, filtering, and configurable pagination.
     */
    public function index(Request $request)
    {
        $allowedSorts = [
            'name'                => 'name',
            'country'             => 'country',
            'created_at'          => 'created_at',
            'total_questions'     => 'total_questions',
            'pending_questions'   => 'pending_questions',
            'published_questions' => 'published_questions',
        ];

        $sort = $request->get('sort', 'created_at');
        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Configurable pagination density
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = User::where(function ($q) {
                $q->whereHas('questions')
                  ->orWhereHas('role', fn($r) => $r->whereIn('name', ['questioner', 'user']));
            })
            ->withCount([
                'questions as total_questions',
                'questions as pending_questions' => fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']),
                'questions as published_questions' => fn($q) => $q->where('status', 'published'),
            ]);

        // Multi-field robust search (Full name, first/last name, Arabic, email, mobile, country, and question id/content)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $words = array_values(array_filter(explode(' ', $term)));

            $query->where(function ($q) use ($term, $words) {
                // 1. Direct or partial match across all core user identity fields
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('despl_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%")
                  ->orWhere('country', 'like', "%{$term}%")
                  // 2. Search by questions asked by this questioner (Question ID or Title)
                  ->orWhereHas('questions', function ($sub) use ($term) {
                      $sub->where('question_id', 'like', "%{$term}%")
                          ->orWhere('title', 'like', "%{$term}%");
                  });

                // 3. Multi-token / First Name + Last Name matching
                if (count($words) > 1) {
                    $q->orWhere(function ($multi) use ($words) {
                        foreach ($words as $w) {
                            $multi->where(function ($part) use ($w) {
                                $part->where('name', 'like', "%{$w}%")
                                     ->orWhere('despl_name', 'like', "%{$w}%")
                                     ->orWhere('email', 'like', "%{$w}%")
                                     ->orWhere('country', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        }

        // Apply whitelisted sorting
        $query->orderBy($allowedSorts[$sort], $direction);

        $questioners = $query->paginate($perPage)->withQueryString();

        // High-level KPI summary cards for admin overview
        $totalQuestioners = User::where(function ($q) {
                $q->whereHas('questions')
                  ->orWhereHas('role', fn($r) => $r->whereIn('name', ['questioner', 'user']));
            })->count();
        $activeInquirers = User::whereHas('questions', fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']))->count();
        $publishedInquirers = User::whereHas('questions', fn($q) => $q->where('status', 'published'))->count();
        $countriesCount = User::where(function ($q) {
                $q->whereHas('questions')
                  ->orWhereHas('role', fn($r) => $r->whereIn('name', ['questioner', 'user']));
            })
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->count('country');

        $summaryStats = [
            'total'       => $totalQuestioners,
            'active'      => $activeInquirers,
            'published'   => $publishedInquirers,
            'countries'   => $countriesCount,
        ];

        return view('admin.questioners.index', compact('questioners', 'summaryStats', 'sort', 'direction', 'perPage'));
    }

    /**
     * Bulk export selected questioners or filtered results as CSV.
     */
    public function exportSelected(Request $request)
    {
        $ids = $request->input('selected_ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        $query = User::where(function ($q) {
                $q->whereHas('questions')
                  ->orWhereHas('role', fn($r) => $r->whereIn('name', ['questioner', 'user']));
            })
            ->withCount([
                'questions as total_questions',
                'questions as pending_questions' => fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']),
                'questions as published_questions' => fn($q) => $q->where('status', 'published'),
            ]);

        if (!empty($ids) && is_array($ids)) {
            $query->whereIn('id', $ids);
        } elseif ($request->filled('search')) {
            $term = trim($request->search);
            $words = array_values(array_filter(explode(' ', $term)));
            $query->where(function ($q) use ($term, $words) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('despl_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%")
                  ->orWhere('country', 'like', "%{$term}%")
                  ->orWhereHas('questions', function ($sub) use ($term) {
                      $sub->where('question_id', 'like', "%{$term}%")
                          ->orWhere('title', 'like', "%{$term}%");
                  });

                if (count($words) > 1) {
                    $q->orWhere(function ($multi) use ($words) {
                        foreach ($words as $w) {
                            $multi->where(function ($part) use ($w) {
                                $part->where('name', 'like', "%{$w}%")
                                     ->orWhere('despl_name', 'like', "%{$w}%")
                                     ->orWhere('email', 'like', "%{$w}%")
                                     ->orWhere('country', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        }

        $users = $query->orderBy('name')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="questioners_export_' . date('Y-m-d_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, ['ID', 'Name', 'Email', 'Country', 'Joined Date', 'Total Questions', 'In Progress', 'Published']);

            foreach ($users as $u) {
                fputcsv($file, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->country ?? '—',
                    $u->created_at ? $u->created_at->format('Y-m-d') : '—',
                    $u->total_questions ?? 0,
                    $u->pending_questions ?? 0,
                    $u->published_questions ?? 0,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Detail page for a single questioner.
     */
    public function show(User $user)
    {
        // Load all questions
        $questions = QuestionAndAnswer::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($qa) use ($user) {
                $daysToPublish = null;
                if ($qa->status === 'published' && $qa->published_at) {
                    $daysToPublish = (int) $qa->created_at->diffInDays($qa->published_at);
                }

                return [
                    'question'        => $qa,
                    'fatwa'           => $qa,
                    'days_to_publish' => $daysToPublish,
                    'user_views'      => 0,
                    'public_reads'    => $qa->views,
                ];
            });

        // Group stats for the dashboard counters
        $stats = [
            'total'       => $questions->count(),
            'pending'     => $questions->filter(fn($r) => in_array($r['question']->status, ['pending','accepted','assigned','answered']))->count(),
            'published'   => $questions->filter(fn($r) => $r['question']->status === 'published')->count(),
            'total_reads' => $questions->sum('public_reads'),
        ];

        // Fetch recent visitor logs (Access history - stubbed out since table is deleted)
        $visitorLogs = collect();

        return view('admin.questioners.show', compact('user', 'questions', 'stats', 'visitorLogs'));
    }

    /**
     * Delete a single questioner profile who has no associated fatwas/questions.
     */
    public function destroy(Request $request, User $user)
    {
        // 1. Safety check: Protect administrative & scholar accounts
        if ($user->id === auth()->id() || $user->hasRole(['admin', 'mufti'])) {
            $msg = 'Administrative and scholar profiles cannot be deleted.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        // 2. Core safety rule: Questioner must have no fatwas / questions
        $questionsCount = $user->questions()->count();
        if ($questionsCount > 0) {
            $msg = "Cannot delete questioner '{$user->name}' because they have {$questionsCount} associated fatwa(s) or question(s).";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 3. Workflow safety check
        $assignedCount = QuestionAndAnswer::where('assigned_mufti_id', $user->id)
            ->orWhere('published_by', $user->id)
            ->orWhere('checked_approved_by', $user->id)
            ->count();
        if ($assignedCount > 0) {
            $msg = 'Cannot delete this user because they are linked to administrative fatwa workflows.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $displayName = $user->despl_name ?: $user->name;
        $user->delete();

        $successMsg = "Questioner '{$displayName}' profile deleted successfully.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.questioners.index')->with('success', $successMsg);
    }

    /**
     * Bulk delete selected questioners who have no questions/fatwas.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('selected_ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        if (empty($ids) || !is_array($ids)) {
            $msg = 'No questioners selected for deletion.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $users = User::whereIn('id', $ids)
            ->where('id', '!=', auth()->id())
            ->withCount('questions')
            ->get();

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            if ($user->hasRole(['admin', 'mufti']) || $user->questions_count > 0) {
                $skippedCount++;
                continue;
            }

            // Check workflow link
            $hasWorkflowLink = QuestionAndAnswer::where('assigned_mufti_id', $user->id)
                ->orWhere('published_by', $user->id)
                ->orWhere('checked_approved_by', $user->id)
                ->exists();

            if ($hasWorkflowLink) {
                $skippedCount++;
                continue;
            }

            $user->delete();
            $deletedCount++;
        }

        $msg = "Successfully deleted {$deletedCount} questioner profile(s) with no fatwas.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} profile(s) preserved because they have existing fatwas or administrative roles.)";
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'skipped_count' => $skippedCount,
                'message' => $msg,
            ]);
        }

        return redirect()->route('admin.questioners.index')->with('success', $msg);
    }
}
