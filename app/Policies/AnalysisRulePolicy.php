<?php

namespace App\Policies;

use App\Models\AnalysisRule;
use App\Models\User;

class AnalysisRulePolicy
{
    public function update(User $user, AnalysisRule $analysisRule): bool
    {
        return $user->isAnalyst();
    }
}
