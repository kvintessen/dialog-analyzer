<?php

namespace App\Models;

use App\Enums\DialogResult;
use Database\Factories\DialogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['manager_id', 'client_name', 'result'])]
class Dialog extends Model
{
    /** @use HasFactory<DialogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'result' => DialogResult::class,
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('sent_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalysisEvent::class);
    }
}
