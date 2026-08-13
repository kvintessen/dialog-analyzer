<?php

namespace App\Http\Controllers;

use App\Http\Resources\DialogSummaryResource;
use App\Models\AnalysisEvent;
use App\Models\Dialog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DialogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $dialogs = Dialog::visibleTo($user)->withSummaryAggregates()->get()
            ->sortByDesc('last_message_at')
            ->values();

        return Inertia::render('Dialogs/Index', [
            'dialogs' => DialogSummaryResource::collection($dialogs)->resolve(),
        ]);
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
}
