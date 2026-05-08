# Build Tasks: Student Interface Overhaul

## Foundation
- [x] Create design brief, IA, and tokens
- [ ] **Add design tokens CSS**: Create `public/assets/css/tokens.css` with semantic variables. _New file._
- [ ] **Link tokens in header**: Add `<link>` to tokens.css in `header.php`. _Modifies existing._

## Core UI Components
- [ ] **Progress ring component**: Create `public/assets/css/progress-ring.css` with SVG-based circular progress. _New component._
- [ ] **Redesign stat cards**: Update dashboard stat cards with warmer backgrounds, softer shadows, better icon containers. _Modifies dashboard.php._
- [ ] **Redesign course cards**: Add progress ring to course cards, improve hover states, better typography hierarchy. _Modifies my-courses.php._
- [ ] **Completed course card**: Distinct green-tinted card with certificate CTA. _Modifies dashboard.php._

## Learning Interface
- [ ] **Improve lesson sidebar**: Better active/hover states, completion icons, module headers. _Modifies learn.php._
- [ ] **Lesson progress indicator**: Add "Lesson X of Y" counter in course header. _Already done in previous commit._
- [ ] **Completion celebration**: Confetti animation when marking last lesson complete. _New JS file._

## Student Hub
- [ ] **Redesign hub cards**: Better hover animations, cleaner icon containers. _Modifies student/index.php._

## Polish
- [ ] **Empty state improvements**: Add warm backgrounds, clearer CTAs, illustration feel. _Modifies multiple files._
- [ ] **Mobile responsiveness**: Ensure all new components work at 375px. _Cross-cutting._
