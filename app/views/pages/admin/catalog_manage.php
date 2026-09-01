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
$courseNameById = [];
foreach ($catalogCourses as $c) {
    $cid = (string)($c['course_id'] ?? '');
    if ($cid !== '') {
        $courseNameById[$cid] = (string)($c['course_name'] ?? '');
    }
}
$prereqPickerCourses = [];
foreach ($allCourseIds as $pcid) {
    if ($editCourse && $pcid === (string)$editCourse['course_id']) {
        continue;
    }
    $prereqPickerCourses[] = [
        'course_id' => $pcid,
        'course_name' => $courseNameById[$pcid] ?? '',
    ];
}
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
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">All selected courses must be completed before registration.</p>
    <form id="prereq-form" class="mt-4" method="post" action="<?= htmlspecialchars(url('/admin.php?view=catalog&edit=' . rawurlencode((string)$editCourse['course_id']))) ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
      <input type="hidden" name="action" value="catalog_prereqs_save" />
      <input type="hidden" name="course_id" value="<?= htmlspecialchars((string)$editCourse['course_id']) ?>" />
      <div>
        <div class="<?= htmlspecialchars(ui_label()) ?>">Selected prerequisites</div>
        <div id="prereq-chips" class="mt-2 flex min-h-[2.5rem] flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-800/50">
          <?php if ($editPrereqs === []): ?>
            <span id="prereq-chips-empty" class="self-center px-1 text-xs text-slate-500 dark:text-slate-400">No prerequisites selected — add courses below.</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="mt-4">
        <label class="<?= htmlspecialchars(ui_label()) ?>" for="prereq-search">Search catalog courses</label>
        <input id="prereq-search" type="search" autocomplete="off" class="<?= htmlspecialchars(ui_input()) ?>" placeholder="Filter by course ID or name…" />
      </div>
      <div class="mt-3 max-h-64 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-600">
        <div id="prereq-picker" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($prereqPickerCourses as $pc): ?>
            <?php
            $pcid = (string)($pc['course_id'] ?? '');
            $pname = (string)($pc['course_name'] ?? '');
            $checked = in_array($pcid, $editPrereqs, true);
            ?>
            <label class="prereq-option inline-flex items-start gap-2 rounded-lg px-1 py-1 text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 dark:text-slate-200" data-course-id="<?= htmlspecialchars(strtolower($pcid)) ?>" data-course-label="<?= htmlspecialchars(strtolower($pcid . ' ' . $pname)) ?>">
              <input type="checkbox" class="prereq-checkbox mt-0.5" name="prereq_ids[]" value="<?= htmlspecialchars($pcid) ?>" data-course-id="<?= htmlspecialchars($pcid) ?>" data-course-name="<?= htmlspecialchars($pname) ?>" <?= $checked ? 'checked' : '' ?> />
              <span>
                <span class="font-mono text-xs font-semibold"><?= htmlspecialchars($pcid) ?></span>
                <?php if ($pname !== ''): ?>
                  <span class="block text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($pname) ?></span>
                <?php endif; ?>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
        <p id="prereq-picker-empty" class="hidden py-4 text-center text-xs text-slate-500 dark:text-slate-400">No courses match your search.</p>
      </div>
      <button type="submit" class="mt-4 <?= htmlspecialchars(ui_btn_primary()) ?>">Save prerequisites</button>
    </form>
    <script>
    (function () {
      const form = document.getElementById('prereq-form');
      if (!form) return;
      const chips = document.getElementById('prereq-chips');
      const emptyHint = document.getElementById('prereq-chips-empty');
      const search = document.getElementById('prereq-search');
      const pickerEmpty = document.getElementById('prereq-picker-empty');
      const options = Array.from(form.querySelectorAll('.prereq-option'));
      const checkboxes = Array.from(form.querySelectorAll('.prereq-checkbox'));

      function syncEmptyHint() {
        const hasChip = chips.querySelector('[data-chip-id]');
        if (emptyHint) emptyHint.classList.toggle('hidden', !!hasChip);
      }

      function addChip(id, name) {
        if (chips.querySelector('[data-chip-id="' + id + '"]')) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.chipId = id;
        btn.className = 'inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-900 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-100 dark:hover:bg-indigo-900/60';
        btn.setAttribute('aria-label', 'Remove prerequisite ' + id);
        const idSpan = document.createElement('span');
        idSpan.className = 'font-mono';
        idSpan.textContent = id;
        btn.appendChild(idSpan);
        if (name) {
          const nameSpan = document.createElement('span');
          nameSpan.className = 'max-w-[10rem] truncate font-normal text-indigo-800/80 dark:text-indigo-200/80';
          nameSpan.textContent = name;
          btn.appendChild(nameSpan);
        }
        const xSpan = document.createElement('span');
        xSpan.className = 'ml-0.5 text-indigo-500';
        xSpan.setAttribute('aria-hidden', 'true');
        xSpan.textContent = '×';
        btn.appendChild(xSpan);
        btn.addEventListener('click', function () {
          const cb = form.querySelector('.prereq-checkbox[data-course-id="' + id + '"]');
          if (cb) cb.checked = false;
          btn.remove();
          syncEmptyHint();
        });
        chips.appendChild(btn);
        syncEmptyHint();
      }

      function syncChipsFromCheckboxes() {
        chips.querySelectorAll('[data-chip-id]').forEach(function (el) { el.remove(); });
        checkboxes.forEach(function (cb) {
          if (cb.checked) addChip(cb.dataset.courseId || cb.value, cb.dataset.courseName || '');
        });
        syncEmptyHint();
      }

      checkboxes.forEach(function (cb) {
        cb.addEventListener('change', syncChipsFromCheckboxes);
      });

      function applySearch() {
        const q = (search.value || '').trim().toLowerCase();
        let visible = 0;
        options.forEach(function (opt) {
          const label = opt.dataset.courseLabel || '';
          const show = q === '' || label.indexOf(q) !== -1;
          opt.classList.toggle('hidden', !show);
          if (show) visible++;
        });
        if (pickerEmpty) pickerEmpty.classList.toggle('hidden', visible > 0);
      }

      if (search) search.addEventListener('input', applySearch);

      syncChipsFromCheckboxes();
      applySearch();
    })();
    </script>
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
        <th class="px-4 py-3">Prereqs</th>
        <th class="px-4 py-3">Active</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
      <?php if ($catalogCourses === []): ?>
        <tr>
          <td class="px-4 py-8 text-center <?= htmlspecialchars(ui_muted()) ?>" colspan="7">
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
            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
              <?php
                $cDeptId = trim((string)($c['dept_id'] ?? ''));
                $cDeptName = trim((string)($c['dept_name'] ?? ''));
              ?>
              <?php if ($cDeptId !== ''): ?>
                <a class="<?= htmlspecialchars(ui_link()) ?>" href="<?= htmlspecialchars(url('/admin.php?' . http_build_query(['view' => 'department', 'dept_id' => $cDeptId]))) ?>">
                  <?= htmlspecialchars($cDeptName !== '' ? $cDeptName : $cDeptId) ?>
                </a>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-400">
              <?php $pc = (int)($c['prereq_count'] ?? 0); ?>
              <?= $pc === 0 ? '—' : ($pc === 1 ? '1 prereq' : $pc . ' prereqs') ?>
            </td>
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
