<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        (new NotificationService)->ensureWeeklySummary($user);
        (new NotificationService)->ensureMonthlySummary($user);
        (new NotificationService)->ensureReminder($user);

        $notifications = $user->notificationsUser()->orderByDesc('created_at')->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(UserNotification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function readAll(): JsonResponse
    {
        Auth::user()->notificationsUser()->unread()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}