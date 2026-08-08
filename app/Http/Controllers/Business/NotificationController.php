<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(30)
            ->through(fn ($notification) => $this->transform($notification));

        $user->unreadNotifications->markAsRead();

        return Inertia::render('Business/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $href = $notification->data['href'] ?? route('business.notifications.index');

        return redirect($href);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    /**
     * @param  \Illuminate\Notifications\DatabaseNotification  $notification
     * @return array<string, mixed>
     */
    private function transform(object $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'href' => $data['href'] ?? null,
            'type' => $data['type'] ?? 'info',
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->timezone(config('app.timezone'))->toIso8601String(),
            'created_at_label' => $notification->created_at?->timezone(config('app.timezone'))->diffForHumans(),
        ];
    }
}
