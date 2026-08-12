<?php
/** @var array<int, array<string, mixed>> $termsRows */
/** @var string $csrf */
$termsRows = $termsRows ?? [];
?>
<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Terms &amp; registration windows</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">Control whether students may register for each term and optional open/close dates. Staff overrides remain available to admins during registration.</p>

<div class="mt-6 space-y-6">
  <?php if (!$termsRows): ?>
    <div class="<?= htmlspecialchars(ui_empty()) ?>">No terms found yet. Seed or migrate the database, then return here.</div>
  <?php endif; ?>
  <?php foreach ($termsRows as $tr): ?>
    <div class="<?= htmlspecialchars(ui_card('p-5')) ?>">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <div class="text-lg font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars((string)$tr['code']) ?></div>
          <div class="<?= htmlspecialchars(ui_muted()) ?>"><?= htmlspecialchars((string)$tr['name']) ?></div>
        </div>
      </div>
      <form class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" method="post" action="<?= htmlspecialchars(url('/admin.php?view=terms')) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
        <input type="hidden" name="action" value="term_registration_save" />
        <input type="hidden" name="term_id" value="<?= (int)$tr['term_id'] ?>" />
        <div class="flex items-center gap-2 sm:col-span-2">
          <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-200">
            <input type="checkbox" name="registration_open" value="1" <?= ((int)($tr['registration_open'] ?? 1) === 1) ? 'checked' : '' ?> />
            Registration open
          </label>
        </div>
        <div>
          <label class="<?= htmlspecialchars(ui_label()) ?>" for="rs-<?= (int)$tr['term_id'] ?>">Start date</label>
          <input id="rs-<?= (int)$tr['term_id'] ?>" type="date" name="registration_start" value="<?= htmlspecialchars((string)($tr['registration_start'] ?? '')) ?>" class="<?= htmlspecialchars(ui_input()) ?>" />
        </div>
        <div>
          <label class="<?= htmlspecialchars(ui_label()) ?>" for="re-<?= (int)$tr['term_id'] ?>">End date</label>
          <input id="re-<?= (int)$tr['term_id'] ?>" type="date" name="registration_end" value="<?= htmlspecialchars((string)($tr['registration_end'] ?? '')) ?>" class="<?= htmlspecialchars(ui_input()) ?>" />
        </div>
        <div class="sm:col-span-2 lg:col-span-4">
          <button type="submit" class="<?= htmlspecialchars(ui_btn_primary()) ?>">Save term</button>
        </div>
      </form>
      <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Leave dates blank for no date restriction (only the “open” flag applies).</p>
    </div>
  <?php endforeach; ?>
</div>
