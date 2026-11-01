<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\FatwaPublishedMail;
use App\Mail\QuestionAssignedMail;
use App\Models\Category;
use App\Models\QuestionAndAnswer;
use App\Models\Reference;
use App\Models\FatwaActivityLog;
use App\Models\User;
use App\Models\QuestionStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionAdminController extends Controller
{
    /** List all questions with filters */
    public function index(Request $request)
    {
        $query = QuestionAndAnswer::with('user', 'category', 'assignedMufti', 'madhab')->latest();

        if ($request->filled('status')) {
            if ($request->status === 'drafted' || $request->status === 'draft') {
                $query->where(function ($q) {
                    $q->whereNotNull('draft_answer')
                      ->where('draft_answer', '!=', '')
                      ->whereNotIn('status', ['published', 'rejected']);
                });
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('madhab_id')) {
            $query->where('madhab_id', $request->madhab_id);
        }
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('question_id', 'like', '%' . $term . '%')
                  ->orWhere('title', 'like', '%' . $term . '%')
                  ->orWhere('body', 'like', '%' . $term . '%')
                  ->orWhereHas('user', function ($u) use ($term) {
                      $u->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                  });
            });
        }

        if ($request->filled('assigned_to')) {
            $assignedId = $request->assigned_to;
            $query->where(function ($q) use ($assignedId) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('question_and_answer', 'assigned_mufti_id')) {
                    $q->where('assigned_mufti_id', $assignedId);
                } elseif (\Illuminate\Support\Facades\Schema::hasColumn('question_and_answer', 'assigned_to')) {
                    $q->where('assigned_to', $assignedId);
                } else {
                    $q->where('assigned_mufti_id', $assignedId);
                }
            });
        }

        $questions = $query->paginate(20)->withQueryString();
        $muftis    = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->get();

        $statusCounts = QuestionAndAnswer::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $summaryStats = [
            'total'        => QuestionAndAnswer::count(),
            'published'    => (int) $statusCounts->get('published', 0),
            'pending'      => (int) $statusCounts->get('pending', 0),
            'under_review' => (int) $statusCounts->get('under_review', 0),
            'rejected'     => (int) $statusCounts->get('rejected', 0),
        ];

        return view('admin.questions.index', compact('questions', 'muftis', 'summaryStats'));
    }

    public function show(QuestionAndAnswer $question)
    {
        $question->load([
            'user', 'category', 'assignedMufti', 
            'references',
            'statusHistory' => fn($q) => $q->with('actor')->orderBy('created_at', 'desc')
        ]);

        if (Auth::check()) {
            $adminOpenedKey = 'admin_opened_q_' . $question->id . '_' . Auth::id();
            if (!session()->has($adminOpenedKey)) {
                session()->put($adminOpenedKey, true);
                FatwaActivityLog::log(
                    $question->id, 'admin_opened',
                    'Inquiry ' . $question->question_id . ' Opened by Admin ' . Auth::user()->name,
                    'bi-eye-fill', 'primary',
                    'Admin ' . Auth::user()->name . ' opened and reviewed inquiry details @ ' . now()->format('Y-m-d H:i:s'),
                    Auth::id()
                );
            }
        }

        $logs = FatwaActivityLog::getComprehensiveTimeline($question);

        $madhab = $question->madhab;
        $muftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('is_active', true)
            ->with('madhab')
            ->get()
            ->sortBy(function ($m) use ($question) {
                if ($question->madhab_id && $m->madhab_id === $question->madhab_id) {
                    return 0 . '_' . $m->name;
                }
                return 1 . '_' . $m->name;
            })
            ->values();

        return view('admin.questions.show', compact('question', 'muftis', 'logs'));
    }

    /** Full fatwa history / timeline */
    public function history(QuestionAndAnswer $question)
    {
        $question->load([
            'user', 'category', 'assignedMufti',
            'publisher', 'statusHistory.actor'
        ]);

        $logs = FatwaActivityLog::getComprehensiveTimeline($question);

        return view('admin.questions.history', compact('question', 'logs'));
    }

    /** Show Accept Question Form Page */
    public function showAcceptForm(QuestionAndAnswer $question)
    {
        if ($question->status !== 'pending') {
            return redirect()->route('admin.questions.show', $question)->with('error', 'This question has already been accepted or processed.');
        }

        $mainCategories = Category::whereNull('parent_id')->get();
        $subCategories  = Category::whereNotNull('parent_id')->get();

        return view('admin.questions.accept', compact('question', 'mainCategories', 'subCategories'));
    }

    /** Accept and update question details */
    public function accept(Request $request, QuestionAndAnswer $question)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'language'    => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $previousStatus = $question->status;

        $question->update([
            'status'      => 'accepted',
            'title'       => $request->title,
            'language'    => $request->language,
            'category_id' => $request->category_id,
        ]);

        FatwaActivityLog::log(
            $question->id, 'accepted', 'Question Accepted & Categorised',
            'bi-check-circle', 'success',
            'Categorised under: ' . optional($question->fresh()->category)->name, Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'accepted',
            'user_id'                => Auth::id(),
            'remarks'                => 'Question accepted and categorized under: ' . optional($question->fresh()->category)->name,
        ]);

        return redirect()->route('admin.questions.show', $question)->with('success', 'Question accepted and categorized successfully.');
    }

    /** Assign question to a Mufti */
    public function assign(Request $request, QuestionAndAnswer $question)
    {
        $request->validate([
            'mufti_id' => ['required', 'exists:users,id'],
        ]);

        $mufti = User::findOrFail($request->mufti_id);
        $previousStatus = $question->status;

        $question->update([
            'assigned_mufti_id' => $mufti->id,
            'status'            => 'assigned',
        ]);

        FatwaActivityLog::log(
            $question->id, 'assigned', 'Assigned ' . $question->question_id . ' to Mufti ' . $mufti->name,
            'bi-person-check', 'primary',
            'Assigned ' . $question->question_id . ' to Mufti ' . $mufti->name, Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'assigned',
            'new_assigned_mufti_id'  => $mufti->id,
            'user_id'                => Auth::id(),
            'remarks'                => 'Assigned ' . $question->question_id . ' to Mufti ' . $mufti->name,
        ]);

        try {
            Mail::to($mufti->email)->send(new QuestionAssignedMail($question));
        } catch (\Exception $e) {
            // Fail silently if mail server is not configured
        }

        return redirect()->route('admin.questions.show', $question)->with('success', 'Question assigned to ' . $mufti->name);
    }

    /** Admin writes or edits answer on behalf of Mufti */
    public function createAnswer(QuestionAndAnswer $question)
    {
        $question->load(['user', 'references', 'draftReferences', 'category', 'madhab', 'assignedMufti']);
        
        $madhab = $question->madhab;
        $muftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('is_active', true)
            ->with('madhab')
            ->get()
            ->sortBy(function ($m) use ($question) {
                if ($question->madhab_id && $m->madhab_id === $question->madhab_id) {
                    return 0 . '_' . $m->name;
                }
                return 1 . '_' . $m->name;
            })
            ->values();

        return view('admin.questions.answer', compact('question', 'muftis'));
    }

    public function storeAnswer(Request $request, QuestionAndAnswer $question)
    {
        $request->validate([
            'mufti_id'         => ['required', 'exists:users,id'],
            'full_answer'      => ['required', 'string'],
            'language'         => ['nullable', 'string', 'in:English,Arabic,Urdu,Malayalam,Hindi'],
            'arabic_text.*'    => ['nullable', 'string'],
            'book_name.*'      => ['nullable', 'string'],
            'ref_language.*'   => ['nullable', 'string'],
            'question_title'   => ['nullable', 'string', 'max:200'],
            'question_body'    => ['nullable', 'string'],
        ]);

        $plainAnswer = trim(strip_tags($request->full_answer ?? ''));
        if (mb_strlen($plainAnswer) < 30) {
            return back()->withErrors([
                'full_answer' => 'The answer must contain at least 30 characters before it can be saved/published (currently ' . mb_strlen($plainAnswer) . ' characters).',
            ])->withInput();
        }

        // Update the question language if provided
        if ($request->filled('language')) {
            $question->update(['language' => $request->language]);
        }

        // Update the question title and body if provided
        if ($request->has('question_title')) {
            $question->update(['title' => $request->question_title]);
        }
        if ($request->has('question_body')) {
            $question->update(['body' => $request->question_body]);
        }

        $isPublished = $question->status === 'published';
        $previousStatus = $question->status;

        if ($isPublished) {
            $question->update([
                'answer'            => $request->full_answer,
                'assigned_mufti_id' => $request->mufti_id,
            ]);
            $question->references()->delete();

            // Handle featured image update for published fatwa
            if ($request->filled('featured_image_cropped')) {
                $imagePath = $this->saveCroppedImage($request->featured_image_cropped);
                if ($imagePath) {
                    $question->update(['featured_image' => $imagePath]);
                }
            }
        } else {
            $question->update([
                'draft_answer'      => $request->full_answer,
                'status'            => 'answered',
                'assigned_mufti_id' => $request->mufti_id,
            ]);
            $question->draftReferences()->delete();
        }

        // Save supporting references
        if ($request->filled('arabic_text') || $request->filled('book_name')) {
            $arabicTexts = (array) ($request->arabic_text ?? []);
            $bookNames = (array) ($request->book_name ?? []);
            $maxCount = max(count($arabicTexts), count($bookNames));
            for ($i = 0; $i < $maxCount; $i++) {
                $text = trim($arabicTexts[$i] ?? '');
                $book = trim($bookNames[$i] ?? '');
                if (!empty($text) || !empty($book)) {
                    Reference::create([
                        'question_and_answer_id' => $question->id,
                        'arabic_text'            => $text ?: '—',
                        'book_name'              => $book ?: '',
                        'language'               => $request->ref_language[$i] ?? 'Arabic',
                        'sort_order'             => $i,
                        'is_draft'               => !$isPublished,
                    ]);
                }
            }
        }

        if (!$isPublished) {
            FatwaActivityLog::log(
                $question->id, 'answer_submitted', 'Answer Written & Submitted for Review',
                'bi-pencil-square', 'info',
                'Written by: ' . (User::find($request->mufti_id)?->name ?? 'Admin'),
                Auth::id()
            );

            QuestionStatusHistory::create([
                'question_and_answer_id' => $question->id,
                'previous_status'        => $previousStatus,
                'new_status'             => 'answered',
                'user_id'                => Auth::id(),
                'remarks'                => 'Answer written & submitted by: ' . (User::find($request->mufti_id)?->name ?? 'Admin'),
            ]);

            return redirect()->route('admin.questions.verify', $question)->with('success', 'Answer saved. Now verify and publish.');
        }

        FatwaActivityLog::log(
            $question->id, 'answer_updated', 'Published Answer Updated',
            'bi-pencil', 'warning',
            'Answer edited after publishing.', Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'published',
            'user_id'                => Auth::id(),
            'remarks'                => 'Published answer updated by Admin.',
        ]);

        return redirect()->route('admin.questions.show', $question)->with('success', 'Published answer updated successfully.');
    }

    /** Admin verifies Mufti's answer */
    public function verify(QuestionAndAnswer $question)
    {
        $question->load('references', 'user');

        $writtenByMuftiId = $question->assigned_mufti_id;
        $madhab = $question->madhab;
        $verifierMuftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('is_active', true)
            ->where('id', '!=', $writtenByMuftiId)
            ->with('madhab')
            ->get()
            ->sortBy(function ($m) use ($question) {
                if ($question->madhab_id && $m->madhab_id === $question->madhab_id) {
                    return 0 . '_' . $m->name;
                }
                return 1 . '_' . $m->name;
            })
            ->values();

        $approverMuftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('can_check_approve', true)
            ->get();

        return view('admin.fatwas.verify', compact('question', 'verifierMuftis', 'approverMuftis'));
    }

    public function approve(Request $request, QuestionAndAnswer $question)
    {
        $request->validate([
            'verified_by'          => ['required', 'array'],
            'verified_by.*'        => ['exists:users,id'],
            'checked_approved_by'  => ['nullable', 'exists:users,id'],
        ]);

        // Ensure Question Title is present before publishing
        if (!$question->title || strlen($question->title) > 200) {
            return redirect()->back()->withErrors(['title' => 'A Question Summary (max 200 chars) is mandatory for publishing. Please edit the question details first.']);
        }

        // Ensure Answer has at least 30 characters before publishing
        $answerText = trim(strip_tags($question->draft_answer ?? ($question->answer ?? '')));
        if (mb_strlen($answerText) < 30) {
            return redirect()->back()->withErrors(['answer' => 'Cannot publish: The fatwa answer must contain at least 30 characters (currently ' . mb_strlen($answerText) . ' characters).']);
        }

        $featuredImagePath = null;
        if ($request->filled('featured_image_cropped')) {
            $featuredImagePath = $this->saveCroppedImage($request->featured_image_cropped);
        } elseif ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/fatwas'), $filename);
            $featuredImagePath = 'uploads/fatwas/' . $filename;
        }

        $previousStatus = $question->status;

        $isPublic = $request->input('publish_visibility', 'public') !== 'private'; // Default is Public (is_public = true)

        $updateData = [
            'status'              => 'published',
            'is_public'           => $isPublic,
            'answer'              => $question->draft_answer ?? $question->answer,
            'draft_answer'        => null,
            'published_by'        => Auth::id(),
            'published_at'        => now(),
            'verified_by'         => $request->verified_by,
            'checked_approved_by' => $request->checked_approved_by ?: null,
        ];
        if ($featuredImagePath) {
            $updateData['featured_image'] = $featuredImagePath;
        }

        $question->update($updateData);

        // Convert draft references to live references
        $question->references()->delete(); // Clear existing live ones
        $question->draftReferences()->update(['is_draft' => false]); // Make draft references live

        $pubTypeLabel = $isPublic ? 'Fatwa Published Publicly' : 'Fatwa Published Privately (Questioner & Shared Link Only)';

        FatwaActivityLog::log(
            $question->id, 'published', $pubTypeLabel,
            'bi-patch-check-fill', 'success',
            $pubTypeLabel . ' by: ' . Auth::user()->name .
            (count($request->verified_by) > 0 ? ' | Verified by: ' . User::whereIn('id', $request->verified_by)->pluck('name')->implode(', ') : '') .
            ($request->checked_approved_by ? ' | Approved by: ' . optional(User::find($request->checked_approved_by))->name : ''),
            Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'published',
            'user_id'                => Auth::id(),
            'remarks'                => $pubTypeLabel . '. Published by: ' . Auth::user()->name . 
                                       (count($request->verified_by) > 0 ? ' | Verified by: ' . User::whereIn('id', $request->verified_by)->pluck('name')->implode(', ') : '') . 
                                       ($request->checked_approved_by ? ' | Approved by: ' . optional(User::find($request->checked_approved_by))->name : ''),
        ]);

        // Notify questioner
        try {
            Mail::to($question->user->email)->send(new FatwaPublishedMail($question));
        } catch (\Exception $e) {
            // Fail silently if mail server is not configured
        }

        return redirect()->route('admin.questions.index')->with('success', $isPublic ? 'Fatwa published publicly to archive successfully!' : 'Fatwa published privately for questioner & shared-link recipients!');
    }

    /**
     * Toggle public archive visibility for a published fatwa
     */
    public function togglePublicVisibility(Request $request, QuestionAndAnswer $question)
    {
        $newVisibility = !$question->is_public;
        $question->update(['is_public' => $newVisibility]);

        $label = $newVisibility 
            ? 'Fatwa Made Publicly Visible in Archive' 
            : 'Fatwa Unpublished from Public (Now Private to Questioner & Shared Link)';
        
        FatwaActivityLog::log(
            $question->id, 'visibility_changed', $label,
            $newVisibility ? 'bi-globe' : 'bi-lock-fill',
            $newVisibility ? 'success' : 'warning',
            'Visibility updated by Admin ' . Auth::user()->name,
            Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $question->status,
            'new_status'             => $question->status,
            'user_id'                => Auth::id(),
            'remarks'                => $label . ' by ' . Auth::user()->name,
        ]);

        return redirect()->back()->with('success', $newVisibility 
            ? 'Fatwa is now published publicly in the global archive and search.' 
            : 'Fatwa unpublished from public archive. (Accessible only to questioner & logged-in shared link users).');
    }

    /**
     * Unpublish fatwa completely back to answered/under_review
     */
    public function unpublish(Request $request, QuestionAndAnswer $question)
    {
        $previousStatus = $question->status;
        $targetStatus = $request->input('target_status', 'answered');

        $question->update([
            'status'       => $targetStatus,
            'is_public'    => false,
            'published_at' => null,
            'draft_answer' => $question->answer,
        ]);

        FatwaActivityLog::log(
            $question->id, 'unpublished', 'Fatwa Unpublished from Public',
            'bi-eye-slash-fill', 'warning',
            'Fatwa status reverted from ' . $previousStatus . ' to ' . $targetStatus . ' by Admin ' . Auth::user()->name,
            Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => $targetStatus,
            'user_id'                => Auth::id(),
            'remarks'                => 'Fatwa unpublished and moved back to ' . $targetStatus . ' by ' . Auth::user()->name,
        ]);

        return redirect()->back()->with('success', 'Fatwa unpublished and moved back to ' . ucfirst($targetStatus) . '.');
    }

    public function reject(Request $request, QuestionAndAnswer $question)
    {
        // Check if this is a structured question rejection from the rejection modal
        if ($request->filled('rejection_reason')) {
            $request->validate([
                'rejection_reason'   => ['required', 'string'],
                'explanation'        => ['nullable', 'string', 'max:2000'],
                'internal_note'      => ['nullable', 'string', 'max:2000'],
                'notify_questioner'  => ['nullable'],
            ]);

            // If "Other" reason is selected, require explanation
            if (strtolower(trim($request->rejection_reason)) === 'other' && empty(trim($request->explanation ?? ''))) {
                return redirect()->back()->withErrors(['explanation' => 'An explanation is required when "Other" is selected as the rejection reason.'])->withInput();
            }

            // Reject is only permitted before accepting the question (status must be 'pending')
            if ($question->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending questions can be rejected before acceptance. Once accepted or published, questions cannot be rejected.');
            }

            $previousStatus = $question->status;
            $reason = trim($request->rejection_reason);
            $explanation = trim($request->explanation ?? '');
            $internalNote = trim($request->internal_note ?? '');

            // Update status to rejected
            $question->update([
                'status'    => 'rejected',
                'status_id' => \App\Models\Status::REJECTED,
            ]);

            // Build detailed note for Activity Log
            $logNotes = "Reason: {$reason}";
            if (!empty($explanation)) {
                $logNotes .= "\nPublic Explanation: {$explanation}";
            }
            if (!empty($internalNote)) {
                $logNotes .= "\n[Internal Note]: {$internalNote}";
            }

            // Log event in FatwaActivityLog
            FatwaActivityLog::log(
                $question->id,
                'rejected',
                'Question Rejected: ' . $reason,
                'bi-x-octagon-fill',
                'danger',
                $logNotes,
                Auth::id()
            );

            // Log event in QuestionStatusHistory
            QuestionStatusHistory::create([
                'question_and_answer_id' => $question->id,
                'previous_status'        => $previousStatus,
                'new_status'             => 'rejected',
                'user_id'                => Auth::id(),
                'remarks'                => 'Question rejected. Reason: ' . $reason . ($explanation ? ' (' . $explanation . ')' : ''),
            ]);

            // Attempt email notification if requested and questioner email exists
            $mailStatus = 'not_requested';
            $mailError = null;
            if ($request->boolean('notify_questioner') && $question->user && !empty($question->user->email)) {
                try {
                    // Try to send rejection mail if Mailable exists
                    if (class_exists(\App\Mail\QuestionRejectedMail::class)) {
                        Mail::to($question->user->email)->send(new \App\Mail\QuestionRejectedMail($question, $reason, $explanation));
                        $mailStatus = 'sent';
                    } else {
                        // Inline fallback mail
                        Mail::send('emails.question-rejected', [
                            'question'    => $question,
                            'reason'      => $reason,
                            'explanation' => $explanation,
                        ], function ($message) use ($question) {
                            $message->to($question->user->email)
                                    ->subject('Update Regarding Your Submitted Islamic Question – ' . $question->question_id);
                        });
                        $mailStatus = 'sent';
                    }
                } catch (\Throwable $e) {
                    $mailStatus = 'failed';
                    $mailError = $e->getMessage();
                    \Illuminate\Support\Facades\Log::warning("Question rejection notification email failed: " . $e->getMessage());
                }
            }

            // Record in question_rejection_logs table if migrated
            if (\Illuminate\Support\Facades\Schema::hasTable('question_rejection_logs')) {
                try {
                    \Illuminate\Support\Facades\DB::table('question_rejection_logs')->insert([
                        'question_and_answer_id' => $question->id,
                        'question_id'            => $question->question_id,
                        'rejection_reason_key'   => \Illuminate\Support\Str::slug($reason, '_'),
                        'rejection_reason'       => $reason,
                        'explanation'            => $explanation ?: null,
                        'internal_note'          => $internalNote ?: null,
                        'rejected_by'            => Auth::id(),
                        'rejected_at'            => now(),
                        'notification_status'    => $mailStatus,
                        'notification_error'     => $mailError,
                        'is_reopened'            => false,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not insert into question_rejection_logs: " . $e->getMessage());
                }
            }

            return redirect()->route('admin.questions.show', $question)
                ->with('success', 'Question ' . $question->question_id . ' has been rejected with the recorded reason.');
        }

        // Legacy handler: Answer verification rejection (moves back to under_review)
        $request->validate(['admin_notes' => 'required|string']);
        $previousStatus = $question->status;

        $question->update(['status' => 'under_review']);

        FatwaActivityLog::log(
            $question->id, 'rejected', 'Answer Rejected — Returned to Mufti',
            'bi-x-circle', 'danger',
            'Admin notes: ' . $request->admin_notes, Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'under_review',
            'user_id'                => Auth::id(),
            'remarks'                => 'Answer rejected. Admin notes: ' . $request->admin_notes,
        ]);

        return redirect()->back()->with('success', 'Answer rejected with notes.');
    }

    public function requestCorrection(Request $request, QuestionAndAnswer $question)
    {
        $request->validate(['admin_notes' => 'required|string']);
        $previousStatus = $question->status;

        $question->update(['status' => 'under_review']);

        FatwaActivityLog::log(
            $question->id, 'correction_requested', 'Correction Requested from Mufti',
            'bi-arrow-repeat', 'warning',
            'Admin notes: ' . $request->admin_notes, Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $previousStatus,
            'new_status'             => 'under_review',
            'user_id'                => Auth::id(),
            'remarks'                => 'Correction requested from Mufti. Admin notes: ' . $request->admin_notes,
        ]);

        return redirect()->back()->with('success', 'Correction requested from Mufti.');
    }

    /** Add private scholarly comment to history */
    public function addHistoryComment(Request $request, QuestionAndAnswer $question)
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $isAdmin = Auth::user()->role?->name === 'admin';

        FatwaActivityLog::log(
            $question->id,
            'scholarly_comment',
            $isAdmin ? 'Admin Note / Feedback' : 'Mufti Message / Comment',
            'bi-chat-dots-fill',
            $isAdmin ? 'danger' : 'warning',
            $request->comment,
            Auth::id()
        );

        QuestionStatusHistory::create([
            'question_and_answer_id' => $question->id,
            'previous_status'        => $question->status,
            'new_status'             => $question->status,
            'user_id'                => Auth::id(),
            'remarks'                => ($isAdmin ? 'Admin Note: ' : 'Mufti Note: ') . $request->comment,
        ]);

        return redirect()->back()->with('success', 'Comment added to history.');
    }

    /**
     * Step 1: Generate and Send High-Security Fatwa Deletion OTP to Admin Email.
     */
    public function sendDeleteOtp(Request $request, QuestionAndAnswer $question)
    {
        $user = Auth::user();
        if (!$user || (!$user->role || $user->role->name !== 'admin') && $user->role_id != 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized administrative action.'], 403);
        }

        $otp = (string) mt_rand(100000, 999999);
        $qid = $question->question_id ?: ('#' . $question->id);
        $title = $question->title ?: Str::limit(strip_tags($question->body), 60);

        session([
            'fatwa_del_otp_' . $question->id => [
                'otp'        => $otp,
                'user_id'    => $user->id,
                'expires_at' => now()->addMinutes(10),
            ]
        ]);

        // Email OTP to authenticated Admin
        try {
            Mail::to($user->email)->send(new \App\Mail\FatwaDeletionOtpMail(
                $otp,
                $qid,
                $title,
                $user->name,
                $request->ip()
            ));
        } catch (\Exception $e) {
            \Log::warning("Deletion OTP mail failed: " . $e->getMessage());
        }

        $maskedEmail = Str::mask($user->email, '*', 3, max(1, strpos($user->email, '@') - 3));

        return response()->json([
            'success' => true,
            'message' => "Security verification code sent to {$maskedEmail}",
            'masked_email' => $maskedEmail,
            // Provide OTP in response if running on local environment for seamless testing
            'dev_otp' => app()->environment('local') ? $otp : null,
        ]);
    }

    /**
     * Step 1b: Verify Admin Email OTP.
     */
    public function verifyDeleteOtp(Request $request, QuestionAndAnswer $question)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $sessionData = session('fatwa_del_otp_' . $question->id);

        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired or was not requested. Please request a new code.'
            ], 422);
        }

        if (now()->greaterThan($sessionData['expires_at'])) {
            session()->forget('fatwa_del_otp_' . $question->id);
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new code.'
            ], 422);
        }

        if (trim($request->otp) !== trim($sessionData['otp'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid 6-digit OTP code. Please check and try again.'
            ], 422);
        }

        // Mark OTP as verified for this fatwa in session for 15 minutes
        session([
            'fatwa_del_verified_' . $question->id => [
                'user_id'    => Auth::id(),
                'verified_at'=> now(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. Proceed to live selfie identity verification.'
        ]);
    }

    /**
     * Step 2 & 3: High-Security Permanent Deletion with Selfie Verification & Audit Trail.
     */
    public function destroy(Request $request, QuestionAndAnswer $question)
    {
        $user = Auth::user();
        if (!$user || (!$user->role || $user->role->name !== 'admin') && $user->role_id != 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized administrative action.'], 403);
        }

        $sessionVerified = session('fatwa_del_verified_' . $question->id);
        
        // If OTP wasn't pre-verified in session, check if passed in direct request
        if (!$sessionVerified) {
            $sessionData = session('fatwa_del_otp_' . $question->id);
            if ($request->filled('otp') && $sessionData && trim($request->otp) === trim($sessionData['otp'])) {
                // Verified via direct payload
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Security verification incomplete. Please enter the OTP code sent to your email.'
                ], 422);
            }
        }

        $qid = $question->question_id ?: ('#' . $question->id);
        $title = $question->title ?: strip_tags($question->body);

        // Process Selfie Verification Image
        $selfiePath = null;
        if ($request->filled('selfie_image')) {
            $selfiePath = $this->saveSelfieAuditImage($request->selfie_image, $qid, $user->id);
        } elseif ($request->hasFile('selfie_file')) {
            $file = $request->file('selfie_file');
            $filename = 'selfie_' . $qid . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/audit/selfies'), $filename);
            $selfiePath = 'uploads/audit/selfies/' . $filename;
        }

        // Clean up references & drafts
        $question->references()->delete();
        $question->draftReferences()->delete();

        // Clean up uploaded files and records (if table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('question_files')) {
            try {
                $files = \App\Models\QuestionFile::where('question_id', $question->id)->get();
                foreach ($files as $f) {
                    if ($f->file_path && Storage::disk('public')->exists($f->file_path)) {
                        Storage::disk('public')->delete($f->file_path);
                    }
                    $f->delete();
                }
            } catch (\Exception $e) {
                \Log::warning("QuestionFile cleanup notice: " . $e->getMessage());
            }
        }

        // Clean up featured image if stored
        if ($question->featured_image) {
            if (Storage::disk('public')->exists($question->featured_image)) {
                Storage::disk('public')->delete($question->featured_image);
            } elseif (file_exists(public_path($question->featured_image))) {
                @unlink(public_path($question->featured_image));
            }
        }

        // Clean up logs and history
        QuestionStatusHistory::where('question_and_answer_id', $question->id)->delete();
        FatwaActivityLog::where('question_id', $question->id)->delete();


        // Permanent Deletion
        $question->delete();

        // Clear verification sessions
        session()->forget('fatwa_del_otp_' . $question->id);
        session()->forget('fatwa_del_verified_' . $question->id);

        \Log::info("FATWA DELETED: Fatwa {$qid} ('{$title}') permanently deleted by Admin {$user->name} (ID: {$user->id}) with Selfie Audit: {$selfiePath}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'status'       => 'done',
                'question_id'  => $qid,
                'message'      => "Fatwa {$qid} has been permanently deleted.",
                'redirect_url' => route('admin.questions.index')
            ]);
        }

        return redirect()->route('admin.questions.index')->with('success', "Fatwa {$qid} has been permanently deleted.");
    }

    /** Helper to save selfie base64 image for security audit trail */
    private function saveSelfieAuditImage($base64Data, $questionId, $userId): ?string
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                if ($data === false) return null;

                $filename = 'selfie_q' . preg_replace('/[^A-Za-z0-9]/', '', $questionId) . '_u' . $userId . '_' . time() . '.' . $type;
                $directory = public_path('uploads/audit/selfies');

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                file_put_contents($directory . '/' . $filename, $data);
                return 'uploads/audit/selfies/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error("Selfie save error: " . $e->getMessage());
        }
        return null;
    }


    /** Helper to decode base64 cropped image and save to disk */
    private function saveCroppedImage($base64Data)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc
                $data = base64_decode($data);

                if ($data === false) return null;

                $filename = 'fatwa_' . time() . '_' . Str::random(5) . '.' . $type;
                $directory = public_path('uploads/fatwas');
                
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                file_put_contents($directory . '/' . $filename, $data);
                return 'uploads/fatwas/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error("Cropped Image upload error: " . $e->getMessage());
        }
        return null;
    }
}

