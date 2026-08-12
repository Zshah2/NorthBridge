<?php
/** @var array<int, array<string, mixed>> $holdRows */
$holdRows = $holdRows ?? [];
?>
<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Active holds</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">
  Students with at least one active hold — registration may be blocked until cleared.
  Look up one student under
  <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=people')) ?>">People</a>.
</p>

<div id="admin-active-holds-list" class="scroll-mt-28 mt-6 <?= htmlspecialchars(ui_table_wrap()) ?>">
  <table class="min-w-full text-left text-sm">
    <thead class="<?= htmlspecialchars(ui_thead()) ?>">
      <tr>
        <th class="px-4 py-3">Student ID</th>
        <th class="px-4 py-3">Name</th>
        <th class="px-4 py-3">Hold type</th>
        <th class="px-4 py-3">Note</th>
        <th class="px-4 py-3">Since</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
      <?php foreach ($holdRows as $h): ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
          <td class="px-4 py-3 font-mono text-xs">
            <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=people&id=' . (int)$h['student_id'] . '&people_panel=hold')) ?>"><?= (int)$h['student_id'] ?></a>
          </td>
          <?php $holdName = trim((string)($h['first_name'] ?? '') . ' ' . (string)($h['last_name'] ?? '')); ?>
          <td class="px-4 py-3"><?= $holdName !== '' ? htmlspecialchars($holdName) : '<span class="text-slate-400">—</span>' ?></td>
          <td class="px-4 py-3 font-medium"><?= htmlspecialchars((string)($h['hold_type'] ?? '')) ?></td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)($h['note'] ?? '')) ?></td>
          <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars((string)($h['created_at'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$holdRows): ?>
        <tr>
          <td class="px-4 py-8 text-center <?= htmlspecialchars(ui_muted()) ?>" colspan="5">
            No active holds. Use
            <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=people')) ?>">People</a>
            to look up a student.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
