<?php
/** @var array $app */
/** @var string $student_id */
?>

<h1 class="<?= htmlspecialchars(ui_h1()) ?>">ID lookup</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">Enter an exact student ID to view live data from MySQL.</p>

<div class="mt-8 max-w-xl <?= htmlspecialchars(ui_card('p-6')) ?>">
  <form class="flex flex-col gap-3 sm:flex-row sm:items-end" method="get" action="<?= htmlspecialchars(url('/admin/students/show')) ?>">
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
      Search
    </button>
  </form>
</div>
