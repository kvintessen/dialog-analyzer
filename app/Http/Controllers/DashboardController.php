<?php

namespace App\Http\Controllers;

use App\Enums\DialogResult;
use App\Enums\EventSeverity;
use App\Http\Resources\DialogSummaryResource;
use App\Models\Dialog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $dialogs = Dialog::visibleTo($user)->withSummaryAggregates()->get();

        $stats = [
            'total_dialogs' => $dialogs->count(),
            'purchased_count' => $dialogs->where('result', DialogResult::Purchased)->count(),
            'high_severity_events_count' => $dialogs
                ->flatMap(fn (Dialog $dialog) => $dialog->events)
                ->filter(fn ($event) => $event->severity === EventSeverity::High)
                ->count(),
        ];

        $recentDialogs = DialogSummaryResource::collection(
            $dialogs->sortByDesc('last_message_at')->take(5)->values()
        )->resolve();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentDialogs' => $recentDialogs,
        ]);
    }
}
