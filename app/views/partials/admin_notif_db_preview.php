<?php
/**
 * Live MySQL row preview for notification hovers.
 *
 * @var array{
 *   table: string,
 *   title: string,
 *   total: int,
 *   columns: array<string, string>,
 *   rows: list<array<string, mixed>>,
 *   href: string,
 *   empty?: string,
 * } $preview
 */
$table = (string)($preview['table'] ?? '');
$title = (string)($preview['title'] ?? '');
$total = (int)($preview['total'] ?? 0);
$columns = is_array($preview['columns'] ?? null) ? $preview['columns'] : [];
$rows = is_array($preview['rows'] ?? null) ? $preview['rows'] : [];
$href = (string)($preview['href'] ?? '#');
$empty = (string)($preview['empty'] ?? 'No matching rows.');
$shown = count($rows);
?>
<div class="border-b border-slate-700/80 last:border-b-0">
  <div class="flex items-center justify-between gap-2 border-b border-slate-700/60 bg-slate-800/90 px-3 py-2">
    <div class="min-w-0">
      <div class="truncate text-[11px] font-semibold text-slate-100"><?= htmlspecialchars($title) ?></div>
      <div class="mt-0.5 truncate font-mono text-[10px] text-emerald-400/90"><?= htmlspecialchars($table) ?></div>
    </div>
    <span class="shrink-0 rounded-md bg-slate-700/80 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-amber-300"><?= $total ?></span>
  </div>
  <?php if ($shown === 0): ?>
    <div class="px-3 py-3 font-mono text-[10px] text-slate-400"><?= htmlspecialchars($empty) ?></div>
  <?php else: ?>
    <div class="max-h-36 overflow-auto">
      <table class="min-w-full text-left font-mono text-[10px]">
        <thead class="sticky top-0 bg-slate-900/95 text-slate-400">
          <tr>
            <?php foreach ($columns as $colKey => $colLabel): ?>
              <th class="whitespace-nowrap px-2 py-1.5 font-semibold uppercase tracking-wide"><?= htmlspecialchars($colLabel) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800 text-slate-200">
          <?php foreach ($rows as $row): ?>
            <tr class="hover:bg-slate-800/60">
              <?php foreach ($columns as $colKey => $colLabel): ?>
                <?php
                  $val = $row[$colKey] ?? null;
                  $display = ($val === null || $val === '') ? 'NULL' : (string)$val;
                  $isNull = ($val === null || $val === '');
                ?>
                <td class="max-w-[8rem] truncate px-2 py-1.5 <?= $isNull ? 'text-rose-400/90' : '' ?>" title="<?= htmlspecialchars($display) ?>"><?= htmlspecialchars($display) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="border-t border-slate-700/60 px-3 py-1.5 font-mono text-[9px] text-slate-500">
      <?= $shown < $total ? ('Showing ' . $shown . ' of ' . $total . ' rows') : ($shown . ' row' . ($shown === 1 ? '' : 's')) ?> · live MySQL
    </div>
  <?php endif; ?>
  <a class="block border-t border-slate-700/60 px-3 py-2 text-[10px] font-semibold text-sky-400 hover:bg-slate-800/80 hover:text-sky-300" href="<?= htmlspecialchars($href) ?>">Open full list →</a>
</div>
