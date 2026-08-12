<?php
/**
 * Terminal-style hover panel wrapping one or more DB previews.
 *
 * @var list<array<string, mixed>> $previews
 * @var string $panelClass extra classes for positioning/sizing
 */
$previews = is_array($previews ?? null) ? $previews : [];
$panelClass = (string)($panelClass ?? '');
if ($previews === []) {
    return;
}
?>
<div class="<?= htmlspecialchars($panelClass) ?> invisible absolute z-50 pt-2 opacity-0 pointer-events-none transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-hover:pointer-events-auto group-focus-within:visible group-focus-within:opacity-100 group-focus-within:pointer-events-auto group-[.notif-open]:visible group-[.notif-open]:opacity-100 group-[.notif-open]:pointer-events-auto">
  <div class="overflow-hidden rounded-xl border border-slate-600 bg-slate-900 shadow-2xl ring-1 ring-black/20" role="tooltip">
    <div class="flex items-center gap-2 border-b border-slate-700 bg-slate-950 px-3 py-2">
      <span class="inline-flex gap-1">
        <span class="h-2.5 w-2.5 rounded-full bg-rose-500/90"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-amber-400/90"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500/90"></span>
      </span>
      <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">mysql · live preview</span>
    </div>
    <?php foreach ($previews as $preview): ?>
      <?php require __DIR__ . '/admin_notif_db_preview.php'; ?>
    <?php endforeach; ?>
  </div>
</div>
