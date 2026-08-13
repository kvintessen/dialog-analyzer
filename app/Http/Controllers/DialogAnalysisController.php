<?php

namespace App\Http\Controllers;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DialogAnalysisController extends Controller
{
    public function store(Dialog $dialog, AnalysisRunner $runner): RedirectResponse
    {
        Gate::authorize('view', $dialog);

        $runner->analyze($dialog);

        return redirect()->route('dialogs.show', $dialog)->with('success', 'Анализ диалога обновлён.');
    }
}
