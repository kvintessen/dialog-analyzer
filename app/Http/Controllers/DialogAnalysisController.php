<?php

namespace App\Http\Controllers;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Http\RedirectResponse;

class DialogAnalysisController extends Controller
{
    public function store(Dialog $dialog, AnalysisRunner $runner): RedirectResponse
    {
        $runner->analyze($dialog);

        return redirect()->route('dialogs.show', $dialog)->with('success', 'Анализ диалога обновлён.');
    }
}
