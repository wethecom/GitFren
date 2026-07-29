# RepoVue

Self-hosted GitHub clone built on [Gitea](https://gitea.io) + WordPress. Create, browse, clone, push, and pull repositories through a full GitHub-like web UI. Works with GitHub Desktop.

## Quick Start

### Windows (XAMPP)

```powershell
# Run from an admin PowerShell
.\scripts\setup-windows.ps1
```

Then open:
- **Gitea (GitHub clone):** `http://localhost:3000` or `http://localhost/git/`
- **WordPress (landing page):** `http://localhost/githubwp`

Login with `admin` / `admin123`

### Linux (LAMP)

```bash
# Run on Ubuntu/Debian
chmod +x scripts/migrate-to-linux.sh
sudo ./scripts/migrate-to-linux.sh
```

## Creating Repositories

Create repos through Gitea's web UI at `http://localhost/git/repo/create`, or clone/push via git:

```bash
git clone http://localhost/git/admin/my-repo.git
git remote add origin http://localhost/git/admin/my-repo.git
git push -u origin main
```

## GitHub Desktop

1. Open GitHub Desktop
2. Go to File → Clone Repository → URL
3. Enter your repo URL: `http://localhost/git/admin/my-repo.git`
4. Enter your credentials: `admin` / `admin123`

## Structure

```
C:\githubwp\
├── repositories/     → Gitea-managed git repos (bare)
├── gitea/            → Gitea binary + config
├── repos/            → Old working copies (optional)
├── theme/            → RepoVue WordPress theme (symlinked into WordPress)
├── scripts/
│   ├── setup-windows.ps1    → Windows re-setup
│   └── migrate-to-linux.sh  → Migrate to Linux LAMP
├── gitea.exe         → Gitea binary (Go, single-file)
└── README.md
```

## Services

| Service | Type | URL |
|---------|------|-----|
| Gitea | Windows service (autostart) | `http://localhost:3000` or `/git/` |
| Apache | XAMPP service (autostart) | `http://localhost/githubwp` |
| MySQL | XAMPP service (autostart) | localhost:3306 (DB: `gitea`) |

## Features

- Full GitHub-like web UI (Gitea)
- Create/delete repos via web UI
- Code browsing with syntax highlighting
- Commit history, branches, tags
- Issues, pull requests, milestones
- Git HTTP(S) Smart Protocol
- SSH support
- Markdown rendering
- GitHub Desktop compatible
- Lightweight (single Go binary, ~120MB)
- Migratable to Linux
