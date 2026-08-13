<?php

namespace App\Http\Controllers;

use App\Ingestion\ChannelAdapterRegistry;
use App\Ingestion\InboundChannelAdapter;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InboundMessageWebhookController extends Controller
{
    public function store(Request $request, string $channel): Response
    {
        $adapterClass = ChannelAdapterRegistry::resolve($channel);

        abort_if($adapterClass === null, 404);

        /** @var InboundChannelAdapter $adapter */
        $adapter = app($adapterClass);

        abort_unless($adapter->verify($request), 401);

        $normalized = $adapter->parse((array) $request->json()->all());

        if (Message::where('external_message_id', $normalized->externalMessageId)->exists()) {
            return response()->noContent();
        }

        $managerId = config("inbound_channels.{$channel}.manager_id");
        abort_if(blank($managerId), 422, "Channel [{$channel}] has no manager configured.");

        $dialog = Dialog::firstOrCreate(
            ['source' => $channel, 'external_thread_id' => $normalized->externalThreadId],
            [
                'manager_id' => $managerId,
                'client_name' => $normalized->clientName ?? 'Без имени',
            ],
        );

        $dialog->messages()->create([
            'sender' => $normalized->sender,
            'body' => $normalized->body,
            'sent_at' => $normalized->sentAt,
            'external_message_id' => $normalized->externalMessageId,
        ]);

        return response()->noContent();
    }
}
