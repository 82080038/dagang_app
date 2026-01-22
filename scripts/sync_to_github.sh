#!/usr/bin/env bash
set -e
REPO_URL="${REPO_URL:-https://github.com/82080038/dagang_app.git}"
BRANCH="${BRANCH:-main}"
if [ -n "$GIT_TOKEN" ]; then
  REPO_URL="${REPO_URL/https:\/\/github.com/https:\/\/$GIT_TOKEN@github.com}"
fi
cd "$(dirname "$0")/.."
git init
git add .
git commit -m "Initialize dagang app"
git branch -M "$BRANCH"
if git remote get-url origin >/dev/null 2>&1; then
  git remote set-url origin "$REPO_URL"
else
  git remote add origin "$REPO_URL"
fi
git push -u origin "$BRANCH" --force
