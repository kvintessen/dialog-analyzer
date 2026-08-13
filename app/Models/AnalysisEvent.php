<?php

namespace App\Models;

use App\Enums\EventSeverity;
use Database\Factories\AnalysisEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dialog_id', 'analysis_rule_id', 'severity', 'title', 'description', 'evidence', 'detected_at'])]
class AnalysisEvent extends Model
{
    /** @use HasFactory<AnalysisEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'severity' => EventSeverity::class,
            'evidence' => 'array',
            'detected_at' => 'datetime',
        ];
    }

    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AnalysisRule::class, 'analysis_rule_id');
    }
}
