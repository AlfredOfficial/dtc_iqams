<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AdminAccountProtectionService;
use App\Services\AuditLogger;
use App\Services\ProfileImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        app(AuditLogger::class)->record('account.profile_updated', $request->user(), [], $request->user(), $request);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /** Store a photo for the authenticated administrator only. */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $user = $request->user();
        $oldPath = $user->avatar_path;
        $newPath = app(ProfileImageService::class)->store($validated['avatar'], 'profile-photos/admin');
        try {
            $user->update(['avatar_path' => $newPath]);
        } catch (\Throwable $exception) {
            app(ProfileImageService::class)->delete($newPath);
            throw $exception;
        }
        if ($oldPath) {
            app(ProfileImageService::class)->delete($oldPath);
        }
        app(AuditLogger::class)->record('account.profile_photo_updated', $user, [], $user, $request);

        return Redirect::route('profile.edit')->with('success', 'Profile photo updated successfully.');
    }

    /** Remove the authenticated administrator's own photo. */
    public function removePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldPath = $user->avatar_path;
        $user->update(['avatar_path' => null]);
        if ($oldPath) {
            app(ProfileImageService::class)->delete($oldPath);
        }
        app(AuditLogger::class)->record('account.profile_photo_removed', $user, [], $user, $request);

        return Redirect::route('profile.edit')->with('success', 'Profile photo removed.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $lockedUser = app(AdminAccountProtectionService::class)->assertCanChangeStatus($user, 'inactive');
            $lockedUser->update(['status' => 'inactive']);
            app(AuditLogger::class)->record('account.status_changed', $lockedUser, [
                'from' => 'active',
                'to' => 'inactive',
                'record' => 'user_account',
            ], $lockedUser, $request);
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
