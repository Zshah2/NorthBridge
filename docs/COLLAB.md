# Team collaboration

## Branch rules (read this first)

**Never push directly to `main`.** Always work on a feature branch and open a Pull Request.

```bash
git checkout main
git pull origin main
git checkout -b feature/your-name-short-topic   # e.g. feature/john-departments, fix/maria-holds
# ... edit, commit ...
git push -u origin feature/your-name-short-topic
```

Then open a PR on GitHub → get a review → merge into `main`.

| Do | Don't |
|----|-------|
| `git pull` on `main` before branching | `git push origin main` |
| One topic per branch | Long-lived branches with many unrelated changes |
| Descriptive branch names (`feature/`, `fix/`) | Pushing secrets or local config files |

Full workflow and PR checklist: **[CONTRIBUTING.md](../CONTRIBUTING.md)** at the repo root.

---

## What you need

| Tool | Purpose |
|------|---------|
| **GitHub** | Shared code (`git clone` / `push` / `pull`) |
| **PHP 8+** | App runtime |
| **MySQL 8+** | Database |
| **Cursor** (optional) | Shared team subscription + live collab sessions |

Cursor team invites **do not** share files. GitHub does.

---

## Quick setup (teammates)

After accepting the GitHub invite:

```bash
git clone https://github.com/Zshah2/NorthBridge.git
cd NorthBridge
```

### 1. Database config (pick one)

**Option A — env vars (good for one terminal session):**

```bash
export DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=collegeweb DB_USER=root DB_PASS='YOUR_PASSWORD'
```

**Option B — local config file (persists across sessions):**

```bash
cp app/config/database.local.php.example app/config/database.local.php
# Edit app/config/database.local.php with your MySQL user/password/database name
```

Create the database if it does not exist:

```bash
mysql -e "CREATE DATABASE IF NOT EXISTS collegeweb CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
```

### 2. Migrate, import, seed

**Automated (recommended):**

```bash
bash scripts/setup_teammate.sh
```

**Manual (same steps):**

```bash
php scripts/migrate.php
php scripts/import_all.php
php scripts/seed_demo_registration.php
php scripts/seed_superadmin.php your@email.com YourPassword
```

Copy login reference (optional, gitignored):

```bash
cp docs/LOGIN_CREDENTIALS.txt.example docs/LOGIN_CREDENTIALS.txt
```

### 3. Run the server

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

- Site: http://127.0.0.1:8000  
- Admin: http://127.0.0.1:8000/login.php  

Optional checks:

```bash
php scripts/smoke_check.php http://127.0.0.1:8000
```

Optional demo cleanup (removes placeholder `Prof*` faculty):

```bash
php scripts/cleanup_demo_faculty.php
```

Grader / demo walkthrough: **[docs/PROFESSOR_TEST_CHECKLIST.md](PROFESSOR_TEST_CHECKLIST.md)**

---

## For the repo owner

1. Push latest `main` to GitHub:
   ```bash
   git push origin main
   ```
2. Add collaborators: **Repo → Settings → Collaborators** → invite GitHub usernames.
3. Point teammates to this file and **[CONTRIBUTING.md](../CONTRIBUTING.md)**.

---

## Live editing in Cursor (optional)

Everyone installs the **Open Collaboration Tools** extension in Cursor.

**Host**

1. Open the project folder in Cursor.
2. `Cmd+Shift+P` → **Start Collaboration Session**.
3. Send the `cursor://collab/...` link to teammates.

**Guests**

1. `Cmd+Shift+P` → **Join Collaboration Session**.
2. Paste the host's link.

Tip: `git pull` before a session; commit + push your branch after.

---

## Do not commit

- `app/config/database.local.php` (passwords)
- `app/config/2fa_config.php` / `2fa_config.local.php`
- `docs/LOGIN_CREDENTIALS.txt`

These are gitignored. Use the `.example` files as templates.
