<?php

namespace App\Http\Controllers;

use App\Enums\DialogResult;
use App\Enums\EventSeverity;
use App\Models\Dialog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $dialogs = Dialog::query()
            ->when(! $user->isAnalyst(), fn ($query) => $query->where('manager_id', $user->id))
            ->with(['manager:id,name', 'events:id,dialog_id,severity'])
            ->withCount('messages')
            ->withMax('messages as last_message_at', 'sent_at')
            ->get();

        $stats = [
            'total_dialogs' => $dialogs->count(),
            'purchased_count' => $dialogs->where('result', DialogResult::Purchased)->count(),
            'high_severity_events_count' => $dialogs
                ->flatMap(fn (Dialog $dialog) => $dialog->events)
                ->filter(fn ($event) => $event->severity === EventSeverity::High)
                ->count(),
        ];

        $recentDialogs = $dialogs
            ->sortByDesc('last_message_at')
            ->take(5)
            ->values()
            ->map(fn (Dialog $dialog) => [
                'id' => $dialog->id,
                'client_name' => $dialog->client_name,
                'manager_name' => $dialog->manager->name,
                'result' => $dialog->result->value,
                'messages_count' => $dialog->messages_count,
                'last_message_at' => $dialog->last_message_at ? Carbon::parse($dialog->last_message_at) : null,
                'events_count' => $dialog->events->count(),
                'max_severity' => $this->maxSeverity($dialog),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentDialogs' => $recentDialogs,
        ]);
    }

    private function maxSeverity(Dialog $dialog): ?string
    {
        if ($dialog->events->isEmpty()) {
            return null;
        }

        return $dialog->events
            ->sortBy(fn ($event) => $event->severity->rank())
            ->last()
            ->severity
            ->value;
    }
}
