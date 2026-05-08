# Design Brief: Student Interface Overhaul

## Problem

The Edutrack LMS student interface is functional but feels utilitarian and lacks the warmth and clarity that would help students feel motivated and confident. Key pain points:

- Completed courses disappear from the dashboard, leaving graduates with an empty page
- The learning interface always starts at lesson 1, forcing students to hunt for where they left off
- Progress is shown as a simple percentage bar with no sense of accomplishment
- No visual celebration when completing lessons or courses
- Navigation is scattered between dashboard, student hub, and my-courses with no clear hierarchy
- Empty states feel like dead ends rather than invitations to explore

## Solution

Redesign the student experience to feel like a personal learning companion — warm, encouraging, and structurally clear. Every interaction should reinforce progress and make the next step obvious.

## Experience Principles

1. **Progress is celebration** — Every step forward should feel rewarding. Progress bars become journey maps. Completions trigger visual delight.

2. **Resume, don't restart** — The interface should always know where the student left off and bring them back there instantly.

3. **Clarity over density** — Information should be layered. The most important thing (what to do next) is immediately visible. Details are one click away.

## Aesthetic Direction

- **Philosophy**: Scandinavian — Warmth plus restraint. Functional beauty. Accessible by default.
- **Tone**: Encouraging, clean, trustworthy. Like a well-organized study space with good lighting.
- **Reference points**: Duolingo's progress rings, Notion's clean information hierarchy, Headspace's warm simplicity.
- **Anti-references**: Corporate dashboard overload. Cold, clinical interfaces. Purple-blue gradients.

## Existing Patterns

- **Typography**: Inter (weights 300-700) — Keep, but add more intentional scale contrast.
- **Colors**: Primary blue `#2E70DA`, Secondary gold `#F6B745` — Keep brand colors, extend with warmer neutrals.
- **Spacing**: 4px/8px base — Extend to a more generous scale for breathing room.
- **Components**: Cards with `rounded-xl`, `shadow-sm`, hover lifts — Evolve with softer shadows and warmer backgrounds.

## Component Inventory

| Component | Status | Notes |
|-----------|--------|-------|
| Progress ring | New | Circular SVG progress indicator for courses |
| Onboarding checklist | Exists | Already built, refine styling |
| Course card | Modify | Add progress ring, better hover states |
| Stat card | Modify | Cleaner layout, softer shadows |
| Lesson sidebar | Modify | Better active/hover states, completion icons |
| Completed course card | New | Distinct from active cards, shows certificate CTA |
| Empty state | Modify | Add illustration feel, clearer CTAs |

## Key Interactions

- **Dashboard load**: Show active courses first, completed below, onboarding for new students
- **Click Continue**: Opens course at last accessed lesson (not lesson 1)
- **Mark lesson complete**: Brief success toast, progress updates, auto-advance to next lesson
- **Course complete**: Confetti/celebration animation, certificate prompt
- **Mobile**: Sidebar collapses, lesson navigation becomes bottom sheet

## Responsive Behavior

- **Mobile (375px+)**: Single column, hamburger nav, touch-friendly lesson list
- **Tablet (768px+)**: Two-column dashboard, sidebar visible
- **Desktop (1024px+)**: Full layout, persistent sidebar on learning pages

## Accessibility Requirements

- WCAG AA contrast ratios on all text
- Focus rings visible and consistent
- All interactive elements keyboard accessible
- Screen reader labels on progress indicators
- `prefers-reduced-motion` respected for all animations

## Out of Scope

- Admin/instructor interface changes
- Payment/checkout flow redesign
- Course creation interface
- Live session interface (Jitsi)
