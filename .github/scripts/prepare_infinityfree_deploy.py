#!/usr/bin/env python3
from __future__ import annotations

import os
import re
import shutil
import subprocess
from pathlib import Path

BASELINE = os.environ["DEPLOY_BASELINE"].strip()
HEAD = os.environ["DEPLOY_HEAD"].strip()

DEPLOY_DIR = Path(".deploy")
DELETE_FILE = Path(".deploy-deletions.lftp")

SAFE_PATH = re.compile(r"^[A-Za-z0-9_./@+,-]+$")

ALLOWED_EXACT = {
    "artisan",
}

ALLOWED_PREFIXES = (
    "app/",
    "bootstrap/",
    "config/",
    "public/",
    "resources/views/",
    "routes/",
    "lang/",
)

FRONTEND_INPUTS = (
    "resources/css/",
    "resources/js/",
)

FRONTEND_EXACT = {
    "package.json",
    "package-lock.json",
    "vite.config.js",
}

COMPOSER_EXACT = {
    "composer.json",
    "composer.lock",
}


def git_lines(*args: str) -> list[str]:
    output = subprocess.check_output(
        ["git", *args],
        text=True,
        encoding="utf-8",
    )
    return [line for line in output.splitlines() if line]


def is_deployable(path: str) -> bool:
    if path in ALLOWED_EXACT:
        return True
    return any(path.startswith(prefix) for prefix in ALLOWED_PREFIXES)


def ensure_safe(path: str) -> None:
    if not SAFE_PATH.fullmatch(path):
        raise SystemExit(
            f"Refusing deployment path with unsupported characters: {path!r}"
        )
    if path.startswith("/") or ".." in Path(path).parts:
        raise SystemExit(f"Refusing unsafe deployment path: {path!r}")


def copy_file(path: str) -> None:
    ensure_safe(path)
    source = Path(path)
    if not source.exists():
        raise SystemExit(f"Expected changed file does not exist: {path}")
    if source.is_symlink():
        raise SystemExit(
            f"Symlinks are not supported by the InfinityFree deployment: {path}"
        )
    if not source.is_file():
        return

    destination = DEPLOY_DIR / path
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, destination)


def copy_tree(source: Path, destination: Path) -> None:
    if not source.is_dir():
        raise SystemExit(f"Required generated directory is missing: {source}")
    shutil.copytree(source, destination, dirs_exist_ok=True)


subprocess.run(
    ["git", "merge-base", "--is-ancestor", BASELINE, HEAD],
    check=True,
)

changed = git_lines(
    "diff",
    "--no-renames",
    "--name-only",
    "--diff-filter=ACMTUXB",
    f"{BASELINE}..{HEAD}",
)

deleted = git_lines(
    "diff",
    "--no-renames",
    "--name-only",
    "--diff-filter=D",
    f"{BASELINE}..{HEAD}",
)

frontend_changed = any(
    path in FRONTEND_EXACT or path.startswith(FRONTEND_INPUTS)
    for path in changed + deleted
)

composer_changed = any(
    path in COMPOSER_EXACT
    for path in changed + deleted
)

if DEPLOY_DIR.exists():
    shutil.rmtree(DEPLOY_DIR)
DEPLOY_DIR.mkdir(parents=True)

for path in changed:
    if is_deployable(path):
        copy_file(path)

# Vite output is generated and ignored by Git, so deploy the complete build
# directory whenever its inputs changed.
if frontend_changed:
    copy_tree(Path("public/build"), DEPLOY_DIR / "public/build")

# Composer dependencies are generated and ignored by Git. They are only
# included when dependency metadata changed, keeping normal deployments small.
if composer_changed:
    copy_tree(Path("vendor"), DEPLOY_DIR / "vendor")

# InfinityFree serves /htdocs, while Laravel's public entry point is /public.
# Keep this tiny root rewrite in every payload so accidental removal is healed.
(DEPLOY_DIR / ".htaccess").write_text(
    "RewriteEngine On\nRewriteRule (.*) /public/$1 [L]\n",
    encoding="utf-8",
)

deletions: list[str] = []
for path in deleted:
    if is_deployable(path):
        ensure_safe(path)
        deletions.append(path)

with DELETE_FILE.open("w", encoding="utf-8", newline="\n") as handle:
    if not deletions:
        handle.write('echo "No tracked production-file deletions to apply."\n')
    else:
        for path in deletions:
            # SAFE_PATH deliberately excludes quotes and shell metacharacters.
            handle.write(f'rm -f "{os.environ.get("DEPLOY_ROOT", "/htdocs")}/{path}"\n')

payload_files = sum(1 for item in DEPLOY_DIR.rglob("*") if item.is_file())

print(f"Baseline: {BASELINE}")
print(f"Head: {HEAD}")
print(f"Tracked changed files considered: {len(changed)}")
print(f"Tracked deleted files considered: {len(deleted)}")
print(f"Frontend rebuild included: {frontend_changed}")
print(f"Composer vendor included: {composer_changed}")
print(f"Deployment payload files: {payload_files}")
print(f"Remote deletions queued: {len(deletions)}")
