<?php
/** @var array<int, array<string, mixed>> $catalogCourses */
/** @var array<int, array<string, mixed>> $departments */
/** @var array<string, mixed>|null $editCourse */
/** @var list<string> $editPrereqs */
/** @var list<string> $allCourseIds */
/** @var string $csrf */
$catalogCourses = $catalogCourses ?? [];
$departments = $departments ?? [];
$editCourse = $editCourse ?? null;
$editPrereqs = $editPrereqs ?? [];
$allCourseIds = $allCourseIds ?? [];
?>
<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Course catalog</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">
  Create and edit catalog courses, departments, and prerequisites.
  To schedule sections for a term, open
  <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=courses')) ?>">Courses</a>
  (Add new section).
</p>

<div class="mt-6 <?= htmlspecialchars(ui_card('p-5')) ?>">
  <h2 class="<?= htmlspecialchars(ui_h2()) ?>"><?= $editCourse ? 'Edit course' : 'Add course' ?></h2>
  <form class="mt-4 grid gap-4 md:grid-cols-2" method="post" action="<?= htmlspecialchars(url('/admin.php?view=catalog')) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
    <input type="hidden" name="action" value="catalog_course_save" />
    <div>
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="cat-course-id">Course ID</label>
      <input id="cat-course-id" name="course_id" value="<?= htmlspecialchars((string)($editCourse['course_id'] ?? '')) ?>" class="<?= htmlspecialchars(ui_input('font-mono uppercase')) ?>" placeholder="CS101" <?= $editCourse ? 'readonly' : '' ?> required />
    </div>
    <div>
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="cat-name">Course name</label>
      <input id="cat-name" name="course_name" value="<?= htmlspecialchars((string)($editCourse['course_name'] ?? '')) ?>" class="<?= htmlspecialchars(ui_input()) ?>" required />
    </div>
    <div>
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="cat-cr">Credits</label>
      <input id="cat-cr" name="credits" type="number" min="0" max="30" value="<?= (int)($editCourse['credits'] ?? 3) ?>" class="<?= htmlspecialchars(ui_input()) ?>" required />
    </div>
    <div>
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="cat-dept">Department</label>
      <select id="cat-dept" name="dept_id" class="<?= htmlspecialchars(ui_select()) ?>">
        <option value="">— None —</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= htmlspecialchars((string)$d['dept_id']) ?>" <?= (($editCourse['dept_id'] ?? '') === (string)$d['dept_id']) ? 'selected' : '' ?>><?= htmlspecialchars((string)$d['dept_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="<?= htmlspecialchars(ui_label()) ?>" for="cat-desc">Description</label>
      <textarea id="cat-desc" name="description" rows="3" class="<?= htmlspecialchars(ui_textarea()) ?>" placeholder="Optional catalog description"><?= htmlspecialchars((string)($editCourse['description'] ?? '')) ?></textarea>
    </div>
    <div class="md:col-span-2 flex flex-wrap items-center gap-4">
      <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
        <input type="checkbox" name="is_active" value="1" <?= ((int)($editCourse['is_active'] ?? 1) === 1) ? 'checked' : '' ?> />
        Active in catalog
      </label>
      <button type="submit" class="<?= htmlspecialchars(ui_btn_primary()) ?>">Save course</button>
      <?php if ($editCourse): ?>
        <a class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" href="<?= htmlspecialchars(url('/admin.php?view=catalog')) ?>">Cancel edit</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php if ($editCourse): ?>
  <div class="mt-6 <?= htmlspecialchars(ui_card('p-5')) ?>">
    <h2 class="<?= htmlspecialchars(ui_h2()) ?>">Prerequisites for <?= htmlspecialchars((string)$editCourse['course_id']) ?></h2>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select courses that must be completed (with a passing grade) before enrolling in this course.</p>
    <form class="mt-4" method="post" action="<?= htmlspecialchars(url('/admin.php?view=catalog&edit=' . rawurlencode((string)$editCourse['course_id']))) ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
      <input type="hidden" name="action" value="catalog_prereqs_save" />
      <input type="hidden" name="course_id" value="<?= htmlspecialchars((string)$editCourse['course_id']) ?>" />
      <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-600">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($allCourseIds as $pcid): ?>
            <?php if ($pcid === (string)$editCourse['course_id']) {
                continue;
            } ?>
            <label class="inline-flex items-center gap-2 text-sm dark:text-slate-200">
              <input type="checkbox" name="prereq_ids[]" value="<?= htmlspecialchars($pcid) ?>" <?= in_array($pcid, $editPrereqs, true) ? 'checked' : '' ?> />
              <span class="font-mono text-xs"><?= htmlspecialchars($pcid) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="submit" class="mt-4 <?= htmlspecialchars(ui_btn_primary()) ?>">Save prerequisites</button>
    </form>
  </div>
<?php endif; ?>

<div class="mt-8 <?= htmlspecialchars(ui_table_wrap()) ?>">
  <table class="min-w-full text-left text-sm">
    <thead class="<?= htmlspecialchars(ui_thead()) ?>">
      <tr>
        <th class="px-4 py-3">ID</th>
        <th class="px-4 py-3">Name</th>
        <th class="px-4 py-3">Cr</th>
        <th class="px-4 py-3">Dept</th>
        <th class="px-4 py-3">Active</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
      <?php if ($catalogCourses === []): ?>
        <tr>
          <td class="px-4 py-8 text-center <?= htmlspecialchars(ui_muted()) ?>" colspan="6">
            No catalog courses yet. Use Add course above, then schedule sections under
            <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=courses')) ?>">Courses</a>.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($catalogCourses as $c): ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
            <td class="px-4 py-3 font-mono text-xs font-semibold">
              <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?' . http_build_query(['view' => 'course', 'course_id' => (string)$c['course_id']]))) ?>"><?= htmlspecialchars((string)$c['course_id']) ?></a>
            </td>
            <td class="px-4 py-3">
              <a class="text-indigo-700 hover:underline dark:text-indigo-300" href="<?= htmlspecialchars(url('/admin.php?' . http_build_query(['view' => 'course', 'course_id' => (string)$c['course_id']]))) ?>"><?= htmlspecialchars((string)$c['course_name']) ?></a>
            </td>
            <td class="px-4 py-3"><?= (int)$c['credits'] ?></td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)($c['dept_name'] ?? '—')) ?></td>
            <td class="px-4 py-3"><?= (int)($c['is_active'] ?? 1) === 1 ? 'Yes' : 'No' ?></td>
            <td class="px-4 py-3 text-right">
              <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?view=catalog&edit=' . rawurlencode((string)$c['course_id']))) ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
