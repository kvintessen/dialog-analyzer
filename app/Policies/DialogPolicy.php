<?php

namespace App\Policies;

use App\Models\Dialog;
use App\Models\User;

class DialogPolicy
{
    public function view(User $user, Dialog $dialog): bool
    {
        return $user->isAnalyst() || $user->id === $dialog->manager_id;
    }
}
