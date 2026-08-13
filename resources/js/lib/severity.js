export const SEVERITY_LABELS = {
    high: 'Высокая',
    medium: 'Средняя',
    low: 'Низкая',
};

export const RESULT_LABELS = {
    purchased: 'Купил',
    not_purchased: 'Не купил',
    undecided: 'Не завершено',
};

export function severityLabel(severity) {
    return SEVERITY_LABELS[severity] ?? severity;
}

export function severityBadgeClass(severity) {
    switch (severity) {
        case 'high':
            return 'border-transparent bg-red-600 text-white hover:bg-red-600/90';
        case 'medium':
            return 'border-transparent bg-amber-700 text-white hover:bg-amber-700/90';
        case 'low':
            return 'border-transparent bg-slate-600 text-white hover:bg-slate-600/90';
        default:
            return '';
    }
}

export function resultLabel(result) {
    return RESULT_LABELS[result] ?? result;
}

export function resultBadgeClass(result) {
    switch (result) {
        case 'purchased':
            return 'border-transparent bg-emerald-700 text-white hover:bg-emerald-700/90';
        case 'not_purchased':
            return 'border-transparent bg-red-600 text-white hover:bg-red-600/90';
        default:
            return 'border-transparent bg-slate-600 text-white hover:bg-slate-600/90';
    }
}
