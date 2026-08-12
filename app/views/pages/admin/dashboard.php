<?php
/** @var array $app */
?>

<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Dashboard</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">Enter a student ID to view live enrollment data.</p>

<div class="mt-4 rounded-2xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 text-sm text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
  After seeding, try student ID <strong class="font-mono font-semibold">123123</strong> in ID lookup.
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <a class="<?= htmlspecialchars(ui_card('p-5 transition hover:border-indigo-200 hover:shadow-md dark:hover:border-indigo-800')) ?>" href="<?= htmlspecialchars(url('/admin/students/search')) ?>">
    <div class="text-base font-semibold text-slate-900 dark:text-white">ID lookup</div>
    <div class="mt-1 <?= htmlspecialchars(ui_muted()) ?>">Search by student ID and view profile, enrollments, and holds.</div>
    <div class="mt-4 text-sm font-semibold text-indigo-700 dark:text-indigo-300">Open →</div>
  </a>
  <a class="<?= htmlspecialchars(ui_card('p-5 transition hover:border-indigo-200 hover:shadow-md dark:hover:border-indigo-800')) ?>" href="<?= htmlspecialchars(url('/admin/schedule')) ?>">
    <div class="text-base font-semibold text-slate-900 dark:text-white">Schedule</div>
    <div class="mt-1 <?= htmlspecialchars(ui_muted()) ?>">Browse sections for the current term.</div>
    <div class="mt-4 text-sm font-semibold text-indigo-700 dark:text-indigo-300">Open →</div>
  </a>
  <a class="<?= htmlspecialchars(ui_card('p-5 transition hover:border-indigo-200 hover:shadow-md dark:hover:border-indigo-800')) ?>" href="<?= htmlspecialchars(url('/admin/holds')) ?>">
    <div class="text-base font-semibold text-slate-900 dark:text-white">Holds</div>
    <div class="mt-1 <?= htmlspecialchars(ui_muted()) ?>">Look up a student and add or clear registration holds.</div>
    <div class="mt-4 text-sm font-semibold text-indigo-700 dark:text-indigo-300">Open →</div>
  </a>
</div>
