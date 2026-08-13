<?php

namespace App\Models;

use App\Enums\MessageSender;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dialog_id', 'sender', 'body', 'sent_at'])]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sender' => MessageSender::class,
            'sent_at' => 'datetime',
        ];
    }

    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }
}
