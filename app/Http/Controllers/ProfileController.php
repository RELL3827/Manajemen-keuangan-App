<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadCount(['transactions', 'accounts', 'categories']);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->validate([
            'notif_budget' => ['nullable', 'boolean'],
            'notif_reminder' => ['nullable', 'boolean'],
            'notif_weekly' => ['nullable', 'boolean'],
            'notif_monthly' => ['nullable', 'boolean'],
        ]);

        $user->notif_budget = $request->boolean('notif_budget');
        $user->notif_reminder = $request->boolean('notif_reminder');
        $user->notif_weekly = $request->boolean('notif_weekly');
        $user->notif_monthly = $request->boolean('notif_monthly');

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => ['required', 'image', 'max:2048'],
            ]);

            $path = $request->file('avatar')->store('avatars', 'public');

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $path;
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        $user->save();

        return Redirect::route('settings')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}