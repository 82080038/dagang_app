@echo off
setlocal enabledelayedexpansion
set "REPO_URL=%REPO_URL%"
if "%REPO_URL%"=="" set "REPO_URL=https://github.com/82080038/dagang_app.git"
set "BRANCH=%BRANCH%"
if "%BRANCH%"=="" set "BRANCH=main"
if not "%GIT_TOKEN%"=="" (
  set "REPO_URL=%REPO_URL:https://github.com=%GIT_TOKEN%@github.com%"
  set "REPO_URL=https://%REPO_URL%"
)
pushd "%~dp0.."
git init
git add .
git commit -m "Initialize dagang app"
git branch -M "%BRANCH%"
git remote set-url origin "%REPO_URL%" 2>nul
git remote add origin "%REPO_URL%" 2>nul
git push -u origin "%BRANCH%" --force
popd
