<?php

namespace App\Http\Controllers;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DialogAnalysisController extends Controller
{
    public function store(Dialog $dialog, AnalysisRunner $runner): RedirectResponse
    {
        Gate::authorize('view', $dialog);

        try {
            $runner->analyze($dialog);
        } catch (Throwable $e) {
            $dialog->forceFill(['analysis_failed_at' => now()])->save();

            report($e);

            return redirect()->route('dialogs.show', $dialog)->with('error', 'Не удалось выполнить анализ диалога.');
        }

        return redirect()->route('dialogs.show', $dialog)->with('success', 'Анализ диалога обновлён.');
    }
}
