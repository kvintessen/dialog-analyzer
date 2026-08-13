<?php

namespace App\Models;

use App\Enums\DialogResult;
use Database\Factories\DialogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Restrict the query to dialogs the given user is allowed to see:
     * analysts see everything, managers see only their own dialogs.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): Builder
    {
        return $query->when(! $user->isAnalyst(), fn (Builder $q) => $q->where('manager_id', $user->id));
    }

    /**
     * Load the relations and aggregates needed to render a dialog summary
     * (dashboard "recent dialogs" and the dialogs index share this shape).
     */
    #[Scope]
    protected function withSummaryAggregates(Builder $query): Builder
    {
        return $query
            ->with(['manager:id,name', 'events:id,dialog_id,severity'])
            ->withCount('messages')
            ->withMax('messages as last_message_at', 'sent_at');
    }
}
