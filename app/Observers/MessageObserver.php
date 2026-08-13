<?php

namespace App\Observers;

use App\Jobs\AnalyzeDialogJob;
use App\Models\Message;

class MessageObserver
{
    public function created(Message $message): void
    {
        AnalyzeDialogJob::dispatch($message->dialog);
    }
}
