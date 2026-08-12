<?php

declare(strict_types=1);

/** Page title (h1). */
function ui_h1(): string
{
    return 'text-2xl font-semibold text-slate-900 dark:text-white';
}

/** Section title (h2) inside cards. */
function ui_h2(): string
{
    return 'text-sm font-semibold text-slate-900 dark:text-white';
}

/** Card / panel surface. */
function ui_card(string $extra = ''): string
{
    $base = 'rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900';

    return trim($base . ' ' . $extra);
}

/** Primary indigo button. */
function ui_btn_primary(): string
{
    return 'rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400';
}

/** Secondary outline button. */
function ui_btn_secondary(): string
{
    return 'rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700';
}

/** Uppercase xs tracking field label. */
function ui_label(): string
{
    return 'block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';
}

/** Text / number / date input class string. */
function ui_input(string $extra = ''): string
{
    $base = 'mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100';

    return trim($base . ' ' . $extra);
}

/** Select class string. */
function ui_select(string $extra = ''): string
{
    $base = 'mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100';

    return trim($base . ' ' . $extra);
}

/** Textarea class string. */
function ui_textarea(string $extra = ''): string
{
    return ui_input($extra);
}

/** Inline text link (indigo). */
function ui_link(): string
{
    return 'font-semibold text-indigo-700 hover:underline dark:text-indigo-300';
}

/** Muted body / intro text. */
function ui_muted(): string
{
    return 'text-sm text-slate-600 dark:text-slate-400';
}

/** Table wrapper. */
function ui_table_wrap(): string
{
    return 'overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900';
}

/** Table header row. */
function ui_thead(): string
{
    return 'border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400';
}

/** Flash / banner surface by tone: success | error | warn. */
function ui_flash(string $tone = 'warn'): string
{
    return match ($tone) {
        'success' => 'rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-100',
        'error' => 'rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-950 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-100',
        default => 'rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100',
    };
}

/** Dashed empty-state panel. */
function ui_empty(): string
{
    return 'rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 px-4 py-8 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-400';
}

/** Alert pill on dashboard (amber). */
function ui_alert_pill(): string
{
    return 'rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-950 ring-1 ring-amber-200 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-100 dark:ring-amber-800 dark:hover:bg-amber-900/50';
}
