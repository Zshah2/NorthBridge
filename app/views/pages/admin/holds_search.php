<?php
/** @var array $app */
/** @var string $student_id */
/** @var bool $can_manage_holds */
$can_manage_holds = $can_manage_holds ?? false;
$err = $_GET['error'] ?? '';
$msg = match ($err) {
    'invalid' => 'Invalid hold form submission.',
    'nostudent' => 'That student ID does not exist.',
    default => '',
};
?>

<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Holds</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">
  Look up a student by ID<?= $can_manage_holds ? ', then add or clear holds.' : ' to view holds (read-only for your role).' ?>
</p>

<?php if ($msg !== ''): ?>
  <div class="mt-6 <?= htmlspecialchars(ui_flash('warn')) ?>">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<div class="mt-8 max-w-xl <?= htmlspecialchars(ui_card('p-6')) ?>">
  <form class="flex flex-col gap-3 sm:flex-row sm:items-end" method="get" action="<?= htmlspecialchars(url('/admin/holds/show')) ?>">
    <div class="flex-1">
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="student_id">Student ID</label>
      <input
        class="<?= htmlspecialchars(ui_input('px-4 py-3')) ?>"
        id="student_id"
        name="student_id"
        inputmode="numeric"
        pattern="[0-9]+"
        value="<?= htmlspecialchars($student_id) ?>"
        placeholder="e.g. 123123"
        required
      />
    </div>
    <button class="<?= htmlspecialchars(ui_btn_primary()) ?>" type="submit">
      Open holds
    </button>
  </form>
</div>
