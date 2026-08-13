<?php

namespace App\Models;

use App\Enums\EventSeverity;
use Database\Factories\AnalysisRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'severity', 'enabled', 'config'])]
class AnalysisRule extends Model
{
    /** @use HasFactory<AnalysisRuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'severity' => EventSeverity::class,
            'enabled' => 'boolean',
            'config' => 'array',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalysisEvent::class);
    }
}
