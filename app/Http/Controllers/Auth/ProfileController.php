<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionController;
use App\Models\Madhab;
use App\Mail\CompleteProfileRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function showCompleteForm()
    {
        $user = Auth::user();
        $hasPendingQuestion = session()->has('pending_question');
        $isSecondQuestion = $user->questions()->count() >= 1;
        $showMobile = true; // Always present the modern ISD country phone input

        // Preselect the preferred madhab
        $preferredMadhabId = $user->madhab_id;
        if ($hasPendingQuestion) {
            $pendingQuestion = session('pending_question');
            if (!empty($pendingQuestion['madhab_id'])) {
                $preferredMadhabId = $pendingQuestion['madhab_id'];
            }
        }

        // If profile is already completed AND no pending question, redirect
        $hasAddress  = !empty($user->address_line_1);
        $hasGender   = !empty($user->gender) && strtolower($user->gender) !== 'not set';
        $hasMobile   = !empty(trim((string)$user->mobile));
        $hasCountry  = !empty(trim((string)$user->country)) && strtolower(trim((string)$user->country)) !== 'not set';
        $hasMadhab   = !empty($user->madhab_id);
        $isProfileComplete = $hasAddress && $hasGender && $hasMobile && $hasCountry && $hasMadhab && $user->profile_completed;

        if ($isProfileComplete && !$hasPendingQuestion) {
            return redirect()->route('user.dashboard');
        }

        $madhabs = Madhab::all();

        return view('auth.profile-complete', compact(
            'hasPendingQuestion',
            'isSecondQuestion',
            'showMobile',
            'preferredMadhabId',
            'madhabs'
        ));
    }

    public function complete(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'gender'          => ['required', 'in:Male,Female'],
            'address_line_1'  => ['required', 'string', 'max:255'],
            'address_line_2'  => ['nullable', 'string', 'max:255'],
            'country'         => ['required', 'string', 'max:100'],
            'country_code'    => ['nullable', 'string', 'max:10'],
            'mobile'          => ['required', 'string', 'min:7', 'max:25'],
            'state'           => ['required', 'string', 'max:100'],
            'district'        => ['required', 'string', 'max:100'],
            'madhab_id'       => ['required', 'exists:madhabs,id'],
            'mother_language' => ['nullable', 'string', 'max:50'],
        ];

        $request->validate($rules);

        $updateData = [
            'gender'            => $request->gender,
            'address_line_1'    => $request->address_line_1,
            'address_line_2'    => $request->address_line_2,
            'country'           => $request->country,
            'state'             => $request->state,
            'district'          => $request->district,
            'madhab_id'         => $request->madhab_id,
            'mother_language'   => $request->mother_language,
            'profile_completed' => true,
        ];

        if ($request->filled('country_code')) {
            $updateData['country_code'] = trim($request->country_code);
        }

        if ($request->filled('mobile')) {
            $rawMobile = trim($request->mobile);
            $countryCode = trim($request->input('country_code', ''));
            // If rawMobile doesn't start with '+' and countryCode is present, format cleanly as international number
            if (!str_starts_with($rawMobile, '+') && !empty($countryCode)) {
                $code = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
                $updateData['mobile'] = $code . ltrim($rawMobile, '0');
            } else {
                $updateData['mobile'] = $rawMobile;
            }
        }

        $user->update($updateData);

        if (class_exists(\App\Models\UserActivityLog::class)) {
            \App\Models\UserActivityLog::record(
                action: 'profile_completed',
                description: 'Completed profile details (Country: ' . $user->country . ', Mobile: ' . $user->mobile . ')',
                status: 'success',
                user: $user,
                identifier: $user->email,
                actionCategory: 'profile',
                contextData: ['country' => $user->country, 'state' => $user->state, 'mobile' => $user->mobile]
            );
        }

        // Atomically pull pending question from session to guarantee single execution
        if (session()->has('pending_question')) {
            $pendingQuestion = session()->pull('pending_question');
            
            if ($pendingQuestion) {
                // Post the question
                $question = QuestionController::saveQuestion($user->id, $pendingQuestion);

                return redirect()->route('home')->with('success', 'Your profile details have been completed and your question has been posted successfully! Question ID: ' . $question->question_id);
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'Profile details completed successfully!');
    }

    /**
     * Bypassing profile completion is disabled. Mobile number and Country are mandatory.
     */
    public function skip(Request $request)
    {
        return redirect()->route('profile.complete')->with('error', 'Mobile number and Country are mandatory to submit a question. Please complete your profile details.');
    }
}
