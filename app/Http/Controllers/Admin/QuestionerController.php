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

        $query = User::whereHas('questions')
            ->withCount([
                'questions as total_questions',
                'questions as pending_questions' => fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']),
                'questions as published_questions' => fn($q) => $q->where('status', 'published'),
            ]);

        // Multi-field search
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('country', 'like', "%{$term}%");
            });
        }

        // Apply whitelisted sorting
        $query->orderBy($allowedSorts[$sort], $direction);

        $questioners = $query->paginate($perPage)->withQueryString();

        // High-level KPI summary cards for admin overview
        $totalQuestioners = User::whereHas('questions')->count();
        $activeInquirers = User::whereHas('questions', fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']))->count();
        $publishedInquirers = User::whereHas('questions', fn($q) => $q->where('status', 'published'))->count();
        $countriesCount = User::whereHas('questions')
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

        $query = User::whereHas('questions')
            ->withCount([
                'questions as total_questions',
                'questions as pending_questions' => fn($q) => $q->whereIn('status', ['pending', 'accepted', 'assigned', 'answered']),
                'questions as published_questions' => fn($q) => $q->where('status', 'published'),
            ]);

        if (!empty($ids) && is_array($ids)) {
            $query->whereIn('id', $ids);
        } elseif ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('country', 'like', "%{$term}%");
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
}
