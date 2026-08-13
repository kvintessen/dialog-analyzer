<?php

use App\Analysis\Rules\ClientSilenceAfterManagerRule;
use App\Analysis\Rules\LongEffortNoSaleRule;
use App\Analysis\Rules\PossibleObjectionRule;
use App\Analysis\Rules\SlowResponseRule;

return [

    /*
    |--------------------------------------------------------------------------
    | Analysis rule registry
    |--------------------------------------------------------------------------
    |
    | Maps the "key" column of the analysis_rules table to the class that
    | evaluates it. Adding a brand new rule type means writing a class that
    | implements App\Analysis\AnalysisRule and registering it here — no other
    | code (controllers, models, the runner) needs to change. Tuning an
    | existing rule (thresholds, severity, enabled/disabled, keyword lists)
    | is a plain database edit through the "Правила анализа" screen.
    |
    */

    'rules' => [
        SlowResponseRule::key() => SlowResponseRule::class,
        ClientSilenceAfterManagerRule::key() => ClientSilenceAfterManagerRule::class,
        PossibleObjectionRule::key() => PossibleObjectionRule::class,
        LongEffortNoSaleRule::key() => LongEffortNoSaleRule::class,
    ],

];
