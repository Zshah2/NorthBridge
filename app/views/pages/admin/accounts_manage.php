<?php
/** @var array<int, array<string, mixed>> $authRows */
/** @var string $csrf */
/** @var int $currentAuthId */
$authRows = $authRows ?? [];
$currentAuthId = (int)($currentAuthId ?? 0);
?>
<h1 class="<?= htmlspecialchars(ui_h1()) ?>">Accounts</h1>
<p class="mt-2 <?= htmlspecialchars(ui_muted()) ?>">Manage admin logins: name, email, password, and access.</p>

<div class="mt-6 space-y-4">
  <?php if ($authRows === []): ?>
    <div class="<?= htmlspecialchars(ui_empty()) ?>">No accounts found.</div>
  <?php endif; ?>
  <?php foreach ($authRows as $a): ?>
    <?php
      $aid = (int)($a['id'] ?? 0);
      $isSelf = $aid === $currentAuthId;
      $label = trim((string)($a['display_name'] ?? ''));
      if ($label === '') {
          $label = (string)($a['username'] ?? 'User');
      }
    ?>
    <article class="<?= htmlspecialchars(ui_card('p-5')) ?>">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3 dark:border-slate-700">
        <div>
          <h2 class="font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($label) ?></h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Role: <?= htmlspecialchars((string)($a['role'] ?? '')) ?>
            · <?= ((int)($a['is_active'] ?? 1) === 1) ? 'Active' : 'Inactive' ?>
            <?php if ($isSelf): ?><span class="text-indigo-600 dark:text-indigo-300">(you)</span><?php endif; ?>
          </p>
        </div>
        <?php if (!$isSelf): ?>
          <form method="post" action="<?= htmlspecialchars(url('/admin.php?view=accounts')) ?>" onsubmit="return confirm('Change account access?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
            <input type="hidden" name="action" value="auth_user_active" />
            <input type="hidden" name="auth_id" value="<?= $aid ?>" />
            <input type="hidden" name="is_active" value="<?= ((int)($a['is_active'] ?? 1) === 1) ? '0' : '1' ?>" />
            <button type="submit" class="<?= htmlspecialchars(ui_btn_secondary()) ?>">
              <?= ((int)($a['is_active'] ?? 1) === 1) ? 'Deactivate' : 'Reactivate' ?>
            </button>
          </form>
        <?php endif; ?>
      </div>

      <form class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end" method="post" action="<?= htmlspecialchars(url('/admin.php?view=accounts')) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" />
        <input type="hidden" name="action" value="auth_login_save" />
        <input type="hidden" name="auth_id" value="<?= $aid ?>" />
        <div>
          <label class="<?= htmlspecialchars(ui_label()) ?>" for="name-<?= $aid ?>">Display name</label>
          <input
            id="name-<?= $aid ?>"
            name="display_name"
            type="text"
            required
            value="<?= htmlspecialchars(trim((string)($a['display_name'] ?? '')) !== '' ? (string)$a['display_name'] : $label) ?>"
            class="<?= htmlspecialchars(ui_input()) ?>"
          />
        </div>
        <div class="sm:col-span-2">
          <label class="<?= htmlspecialchars(ui_label()) ?>" for="email-<?= $aid ?>">Sign-in email</label>
          <input
            id="email-<?= $aid ?>"
            name="email"
            type="email"
            required
            value="<?= htmlspecialchars((string)($a['email'] ?? '')) ?>"
            placeholder="name@example.edu"
            autocomplete="email"
            class="<?= htmlspecialchars(ui_input()) ?>"
          />
        </div>
        <div>
          <label class="<?= htmlspecialchars(ui_label()) ?>" for="pw-<?= $aid ?>">New password</label>
          <input
            id="pw-<?= $aid ?>"
            name="new_password"
            type="password"
            autocomplete="new-password"
            minlength="8"
            placeholder="Optional"
            class="<?= htmlspecialchars(ui_input()) ?>"
          />
        </div>
        <div>
          <button type="submit" class="w-full <?= htmlspecialchars(ui_btn_primary()) ?>">Update login</button>
        </div>
      </form>
    </article>
  <?php endforeach; ?>
</div>

<p class="mt-4 text-xs text-slate-500 dark:text-slate-400">New admins: sign out → <strong>Create account</strong> on the sign-in page.</p>
