---
name: content-qa
description: Verify course content (lessons, quizzes, assignments, diagrams) before declaring it done. Use after authoring or editing any content seeder or course content. Catches thin/empty notes, broken diagram image references, quizzes with no questions, multiple-choice questions with no correct option, modules with no lessons, and courses with no assignments.
---

# Verify course content before finishing

After authoring or changing course content, run the content gate and fix every
issue it reports. Content is authored blind, so empty quizzes, broken diagram
links, and missing answers slip through easily.

## Run the gate
```
php artisan content:qa            # all courses
php artisan content:qa <courseId> # one course
```
It reports, per course:
- `thin notes` → expand the lesson to substantial HTML (see DIAGRAM/style norms
  and existing good lessons; aim 400–900 words of real teaching).
- `references missing diagram` → the lesson points to an SVG that doesn't exist.
  Author the SVG (use the **svg-diagram-qa** skill) or fix the src path.
- `quiz "…" has no questions` → add real questions (mix Multiple Choice with
  options + a correct one, and Short Answer with correct_answer) via the seeder.
- `a Multiple Choice/True/False question has no correct option` → mark one option
  `is_correct => true`.
- `Short Answer … has no correct_answer` → set `correct_answer`.
- `module … has no lessons` / `course has no assignments` → add them.

## Rules
- Match the existing seeder patterns in `database/seeders/Content/`.
- Re-run `php artisan content:qa <id>` until it prints `✓` for the course.
- After editing seeders, also apply the **laravel-verify** skill (php -l etc.).
- NEVER run migrate:fresh/refresh/reset. Use `db:seed --class=...` only.
