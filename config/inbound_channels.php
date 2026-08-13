<?php

use App\Ingestion\Adapters\WazzupAdapter;

return [

    /*
    |--------------------------------------------------------------------------
    | Inbound channel registry
    |--------------------------------------------------------------------------
    |
    | Maps a channel key (the {channel} segment of /webhooks/{channel}) to
    | the adapter class that verifies and parses its payload. Adding a new
    | channel means writing a class that implements
    | App\Ingestion\InboundChannelAdapter and registering it here — the
    | webhook controller and the rest of the pipeline (MessageObserver,
    | AnalyzeDialogJob, analysis rules) don't change.
    |
    */

    'channels' => [
        WazzupAdapter::key() => WazzupAdapter::class,
    ],

    /*
    | Per-channel config: which manager new dialogs from this channel are
    | attributed to, and the shared secret used to verify its webhook. A
    | real integration would likely map this per connected number/account
    | rather than one manager per channel — kept flat here since this is a
    | stub for a single illustrative adapter, not a certified integration.
    */
    'wazzup' => [
        'manager_id' => env('WAZZUP_DEFAULT_MANAGER_ID'),
        'webhook_secret' => env('WAZZUP_WEBHOOK_SECRET'),
    ],

];
