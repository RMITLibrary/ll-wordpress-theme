# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**RMIT students, self-directed.** The primary audience. They arrive by search, from the
library, or from a link, and work through a resource alone — often while an assignment is
already due. They are not browsing; they came for one thing.

**RMIT teaching staff.** Academics and learning designers who find resources to embed or
link into their own Canvas courses.

**Library and learning advisors.** Staff who point students at specific pages during
consultations and workshops.

Students met the site inside a Canvas course shell as well, but that is a delivery route
rather than a distinct audience — the same resources, embedded.

## Product Purpose

Provides the foundation knowledge and skills students need to get through their studies:
academic writing, referencing, assessment types, maths and statistics, chemistry,
engineering mechanics, health sciences, digital skills, and art and design. Success is a
student finding the specific thing they were stuck on and being able to act on it.

## Positioning

Foundation-level material that carries several things a generic study-skills site cannot
claim at once:

- **Discipline-specific depth** — real maths, chemistry, engineering and health-science
  content written for RMIT's own programs, not generic study advice.
- **Authored by RMIT advisors** — written and maintained by the university's own learning
  and academic-skills staff, tied to how RMIT actually assesses.
- **Built to embed into teaching** — resources drop into Canvas as interactive components
  of a course, not just pages to read.
- **Indigenous knowledges woven through** — integrated across the discipline content
  rather than siloed into a single section.

## Operating Context

Content is authored in WordPress (child theme on Picostrap5 / Bootstrap 5.3.3) at
`wp-content/themes/rmit-learning-lab`. A static snapshot is taken from that install,
cleaned by `~/Sites/ll-python/clean_static_pages.py`, committed to `learning-lab-gh` and
deployed by CodeBuild to S3 behind CloudFront. The public site is therefore always a
build artifact, never live WordPress.

Resources are consumed in two places: directly on the site, and embedded in Canvas course
shells via iframe, where the page chrome is stripped and the frame is resized by the host.

## Capabilities and Constraints

Non-negotiable, confirmed:

- **Canvas iframe embedding.** Pages must work inside an iframe with chrome stripped —
  `?iframe=true`, `hide-title`, `hide-intro`, and LTI resize messaging. Nothing may break
  this, including redirects, which must forward query strings.
- **Fully static output.** No server-side code, no logged-in state, no forms that post
  back. Anything dynamic is client-side or baked at export.
- **RMIT brand and design system.** Must stay visually consistent with rmit.edu.au — its
  colours, type and component conventions are binding.
- **WCAG 2.1 AA.** Applies to all content, including H5P interactives and maths notation.

Existing functionality worth preserving: Fuse.js client-side search over exported JSON
datasets, H5P interactive content, MathJax notation, a generated redirect layer for
legacy Drupal URLs, and breadcrumb navigation derived from page hierarchy.

## Brand Commitments

Name: RMIT Learning Lab. Visual identity follows RMIT University, including the shared
CDN assets at `rmitlibrary.github.io/cdn`.

## Evidence on Hand

Real content across maths-statistics, chemistry, assessments, referencing,
writing-fundamentals, art-and-design, digital-skills and university-essentials, plus
a `work-in-progress` tree of drafts that is deliberately noindexed. Legacy Drupal and
`dlsweb` microsite content still lives in the export.

No testimonials, usage figures, learning-outcome data or case studies are on hand —
future work must not invent them.

## Product Principles

1. **The student is mid-task, not browsing.** They arrived with a specific problem and
   limited time; the fastest path to the answer beats the richer experience.
2. **Every page is potentially a fragment.** Any resource may be embedded in a course
   shell with its chrome removed, so pages must stand alone without site furniture.
3. **Foundation, not remediation.** The tone treats students as capable people missing a
   specific piece, never as deficient.
4. **Discipline content is the product.** Structure and navigation serve the resource;
   they are not the attraction.
5. **The output is static.** Anything that assumes a server, a session, or a live database
   is not an option, however good it would be.

## Accessibility & Inclusion

WCAG 2.1 AA across all content, including third-party H5P interactives and MathJax
notation. Indigenous perspectives are integrated across the discipline content as a
content commitment, not a section.
