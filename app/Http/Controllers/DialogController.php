<?php

namespace App\Http\Controllers;

use App\Models\AnalysisEvent;
use App\Models\Dialog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DialogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $dialogs = Dialog::query()
            ->when(! $user->isAnalyst(), fn ($query) => $query->where('manager_id', $user->id))
            ->with(['manager:id,name', 'events:id,dialog_id,severity'])
            ->withCount('messages')
            ->withMax('messages as last_message_at', 'sent_at')
            ->get()
            ->sortByDesc('last_message_at')
            ->values()
            ->map(fn (Dialog $dialog) => [
                'id' => $dialog->id,
                'client_name' => $dialog->client_name,
                'manager_name' => $dialog->manager->name,
                'result' => $dialog->result->value,
                'messages_count' => $dialog->messages_count,
                'last_message_at' => $dialog->last_message_at ? Carbon::parse($dialog->last_message_at) : null,
                'events_count' => $dialog->events->count(),
                'max_severity' => $this->maxSeverity($dialog->events),
            ]);

        return Inertia::render('Dialogs/Index', ['dialogs' => $dialogs]);
    }

    public function show(Dialog $dialog): Response
    {
        Gate::authorize('view', $dialog);

        $dialog->load(['manager:id,name', 'messages', 'events.rule']);

        return Inertia::render('Dialogs/Show', [
            'dialog' => [
                'id' => $dialog->id,
                'client_name' => $dialog->client_name,
                'manager_name' => $dialog->manager->name,
                'result' => $dialog->result->value,
            ],
            'messages' => $dialog->messages->map(fn ($message) => [
                'id' => $message->id,
                'sender' => $message->sender->value,
                'body' => $message->body,
                'sent_at' => $message->sent_at,
            ]),
            'events' => $dialog->events
                ->sortByDesc(fn (AnalysisEvent $event) => $event->severity->rank())
                ->values()
                ->map(fn (AnalysisEvent $event) => [
                    'id' => $event->id,
                    'severity' => $event->severity->value,
                    'title' => $event->title,
                    'description' => $event->description,
                    'evidence' => $event->evidence,
                    'rule_name' => $event->rule->name,
                ]),
        ]);
    }

    /**
     * @param  Collection<int, AnalysisEvent>  $events
     */
    private function maxSeverity(Collection $events): ?string
    {
        if ($events->isEmpty()) {
            return null;
        }

        return $events
            ->sortBy(fn (AnalysisEvent $event) => $event->severity->rank())
            ->last()
            ->severity
            ->value;
    }
}
