rzx-me (remake)

This repository contains a modernized remake of the original rzx.me site.

Notes:
- Public static assets are served from `public/assets/`.
- Source CSS (development) previously lived in `includes/css/`. During this update those files were copied into `public/assets/css/` and `includes/css/` deletions were staged.
- A backup of `includes/css` was temporarily created at `includes_css_backup.zip` and is excluded by `.gitignore`.

Recommended workflow:
1. Edit CSS in `includes/css/` during development, then run the publish script to copy to `public/assets/css/` before committing (or edit `public/assets/css/` directly if you prefer).
2. Use `git status` to verify staged changes before commit.

If you need me to remove the backup zip from the repo or to revert the staged deletions, tell me which option to perform.
