Prompt 01 --- Project Foundation and Scaffolding

Read docs/AI_MASTER_CONTEXT.md completely.

We are starting Lenflo Milestone 1.

TASK
Inspect the repository and establish the production-capable MVP foundation.

Use the current stable Laravel 13 React starter-kit architecture with TypeScript, Inertia, Tailwind, and the project's supported tooling.

GOALS
- Confirm framework/runtime versions.
- Configure PostgreSQL-ready environment conventions.
- Preserve local compatibility with Laravel Herd.
- Establish sensible linting, formatting, and testing.
- Establish the modular-monolith domain structure without fighting Laravel conventions.
- Create only the folders that are actually needed now.
- Add project documentation for local setup.
- Confirm dark-mode foundation.
- Add a simple authenticated app shell and public feed route placeholder.
- Do not implement feed functionality yet.

DEPENDENCY RULE
Do not blindly install a package list.
First inspect what the starter kit already provides.
For each proposed new dependency, state:
- purpose,
- why built-in Laravel/React capability is insufficient,
- maintenance/security cost.
Install only justified dependencies.

QUALITY GATE
- Application boots.
- Existing tests pass.
- New foundation tests pass.
- TypeScript check passes.
- Lint/format checks pass.
- No secrets are committed.

Before coding, show the plan and expected files.
After coding, summarize changes, commands, tests, and any decisions I must make.

Prompt 02 --- Core Data Model

Read docs/AI_MASTER_CONTEXT.md.

TASK
Design and implement the minimum database model required for Lenflo MVP.

MODEL THE MVP AROUND
- users
- profiles / onboarding data
- age or birth-date-derived age eligibility
- content items
- content sources
- content categories
- content age bands
- moderation status
- admin review metadata
- likes
- saves
- shares/events where appropriate
- watch/engagement events

REQUIREMENTS
- PostgreSQL-friendly schema.
- Use foreign keys and indexes deliberately.
- Do not store redundant data without reason.
- Content must have a moderation lifecycle such as candidate, pending_review, approved, rejected, published/disabled as appropriate.
- Only approved/published content may become feed-eligible.
- Support multiple countries and languages without building a localization platform.
- Avoid storing unnecessary personal data.
- Make age eligibility queryable.
- Use factories/seeders for development.
- Add model/schema tests.

Do not build UI.
Do not build recommendation ML.

First present the proposed entities and relationships in concise form. Then implement after verifying they fit existing repository conventions.

Prompt 03 --- Authentication, Guest Mode and Onboarding

Read docs/AI_MASTER_CONTEXT.md.

TASK
Implement Lenflo's MVP identity flow.

BEHAVIOR
- A guest can enter the public feed and watch a limited approved feed.
- Account-only actions require authentication.
- Email authentication is supported through the Laravel starter foundation.
- Add Google sign-in using the safest maintainable approach compatible with the current stack.
- Do not expose OAuth secrets client-side.
- After authentication, collect required onboarding information.
- Age collection must be neutral and must not encourage users to lie about age.
- Structure onboarding so child/parental-consent rules can be added by jurisdiction before public launch.
- Do not pretend legal consent is solved merely by adding a checkbox.
- Keep data collection minimal.

INTERACTION
If a guest attempts Like or Save:
- preserve context where practical,
- ask the user to sign in,
- return them cleanly to the experience.

TEST
Guest access, protected actions, authentication flow boundaries, onboarding validation, authorization.

Do not implement a fake parental-consent system.
Flag legal decisions that require counsel.

Prompt 04 --- Admin Roles and Authorization

Read docs/AI_MASTER_CONTEXT.md.

TASK
Implement MVP admin authorization.

MVP
- Super Admin is the primary administrator.
- Architecture must permit later role-based admins.
- Do not build finance/marketing dashboards now.
- Use least privilege.
- Protect admin routes server-side.
- Never rely only on hiding React components.

ADMIN MVP CAPABILITIES
- access admin area
- manage content candidates
- review content
- approve/reject
- publish/unpublish or disable content
- view basic content metadata

Provide a clean authorization strategy using Laravel-native policies/gates or a justified package if genuinely needed.

Seed a development Super Admin safely without hard-coded production credentials.

Add authorization tests proving normal users cannot access admin operations.

Prompt 05 --- YouTube Content Discovery and Import

Read docs/AI_MASTER_CONTEXT.md.

TASK
Build admin-only YouTube content ingestion.

TWO PATHS
A. Search/discover candidates through YouTube Data API.
B. Paste a YouTube URL manually.

RULES
- API keys/secrets remain server-side.
- Respect API quota and errors.
- Do not download/re-upload YouTube video files.
- Store only metadata Lenflo needs and is permitted to store.
- Preserve external source/video identifiers.
- Candidate content is never automatically public.
- Duplicate imports should be prevented.
- Validate URLs and identifiers.
- Handle unavailable/private/deleted content gracefully.
- Design provider code behind an interface/service boundary so another provider could be added later.
- YouTube is the only provider to implement now.

ADMIN OUTPUT
Candidate item with title, source metadata, thumbnail/reference where permitted, duration if available, and classification fields.

Add mocked/fake API tests. Tests must not depend on live YouTube API calls.

Prompt 06 --- Content Review and Classification

Read docs/AI_MASTER_CONTEXT.md.

TASK
Build the Super Admin content review workflow.

ADMIN MUST BE ABLE TO
- open candidate content,
- preview the embed,
- assign category,
- assign age eligibility,
- assign language,
- assign country/region relevance if needed,
- add internal moderation notes,
- approve,
- reject,
- publish,
- disable.

SAFETY
A content item without valid approval and age eligibility must not be feed-eligible.

Add audit fields:
- reviewed_by
- reviewed_at
- approval status
- rejection reason where relevant

Do not create a giant enterprise moderation system.
Build a clean MVP workflow with a data model that can grow.

Add tests for every moderation state transition and feed eligibility.

Prompt 07 --- Vertical Feed Experience

Read docs/AI_MASTER_CONTEXT.md.

TASK
Build Lenflo's core vertical video feed.

EXPERIENCE
- One primary content item per viewport.
- Mobile-first.
- CSS scroll snap or an equally simple browser-native approach.
- Natural touch scrolling/swiping.
- Up/down arrow keyboard navigation on desktop.
- Avoid hijacking normal accessibility behavior unnecessarily.
- Load the current video efficiently.
- Do not autoplay multiple videos simultaneously.
- Respect browser autoplay restrictions.
- Pause/stop videos that leave the active viewport where technically appropriate.
- Responsive desktop and mobile layouts.
- Smooth transitions without excessive animation.
- Dark Aurora Flow design.

DATA
Only server-approved, published, age-eligible content may enter the feed.

PERFORMANCE
Do not render hundreds of live YouTube players at once.
Use a bounded/windowed strategy or lightweight placeholders where appropriate.

ACCESSIBILITY
- keyboard usable
- visible focus
- semantic buttons
- readable contrast
- reduced-motion consideration

Add component and feature tests where practical.

Prompt 08 --- Like, Save and Share

Read docs/AI_MASTER_CONTEXT.md.

TASK
Implement MVP engagement actions.

ACTIONS
- Like/unlike
- Save/unsave
- Share

RULES
- Like and Save require authentication.
- Guest attempts trigger authentication flow.
- Use optimistic UI only if rollback/error behavior is correct.
- Prevent duplicate likes/saves at database level.
- Share should use Web Share API where supported with a safe fallback.
- Do not implement comments.
- Do not implement DMs.
- Do not expose private user data.

OPTIONAL UX
Support double-tap like only if it does not damage accessibility or create accidental engagement. Standard Like button remains authoritative.

Add server authorization/validation and tests.

Prompt 09 --- Engagement Tracking and Feed Scoring

Read docs/AI_MASTER_CONTEXT.md.

TASK
Implement minimal analytics events and a deterministic MVP feed-ranking service.

TRACK ONLY WHAT WE NEED
Examples:
- impression
- play/start
- meaningful watch milestone
- completion
- skip
- like
- save
- share

Avoid noisy event spam.

FEED SCORING
Use understandable rules based on:
- age eligibility
- approval/published status
- category/interest affinity when known
- quality/editorial score
- recent engagement
- watch behavior
- diversity/anti-repetition

Do not call this machine learning.
Do not add vector databases.
Do not add Kafka.
Do not add a separate recommendation service.

Place ranking logic behind a clear application/domain boundary so it can be replaced later.

Add tests proving:
- ineligible content never ranks,
- unapproved content never ranks,
- diversity logic works,
- scoring is deterministic for controlled test data.

Prompt 10 --- Brand UI, Dark Mode and Responsive Polish

Read docs/AI_MASTER_CONTEXT.md.

TASK
Apply the Lenflo visual system consistently.

BRAND
- Lenflo
- learning-flow concept
- dark-first
- deep navy/slate
- indigo + teal
- controlled violet glow
- minimal warm-gold accent
- Aurora Flow gradients
- modern, premium, safe, youthful but not childish

REQUIREMENTS
- system dark-mode support
- strong contrast
- accessible controls
- consistent spacing and typography
- responsive phone/tablet/desktop behavior
- feed remains the hero
- gradients enhance hierarchy rather than reduce readability

Do not spend the sprint creating a giant design system.
Create reusable tokens/components only where they reduce inconsistency.

Audit all MVP pages for responsive and dark-mode behavior.

Prompt 11 --- Admin Analytics and Operational Visibility

Read docs/AI_MASTER_CONTEXT.md.

TASK
Create a minimal Super Admin operational dashboard.

SHOW USEFUL MVP SIGNALS
- approved content count
- pending review count
- rejected/disabled count
- users/signups
- feed impressions
- watch/completion indicators
- likes
- saves
- shares
- recent moderation activity

Do not build a BI platform.
Do not build finance or marketing modules.

Queries must be reasonably efficient.
Avoid exposing sensitive personal data.
Add tests for authorization and aggregation logic.

Prompt 12 --- MVP Hardening and Release Candidate

Read docs/AI_MASTER_CONTEXT.md.

TASK
Treat the current application as a release candidate and perform an MVP hardening pass.

DO NOT ADD NEW PRODUCT FEATURES.

AUDIT
- authentication
- authorization
- admin route protection
- input validation
- CSRF/session security
- OAuth handling
- secret management
- API error handling
- YouTube quota/error behavior
- moderation bypass risks
- age eligibility leakage
- database indexes
- N+1 queries
- feed performance
- mobile behavior
- keyboard behavior
- accessibility basics
- dark mode
- responsive layouts
- error pages
- logs
- queue failure handling if queues are used
- test coverage of critical flows

PROVE
- unapproved content cannot enter feed,
- wrong-age content cannot enter an age-restricted feed,
- normal user cannot perform admin actions,
- guest cannot perform account-only engagement without auth,
- duplicate engagement is prevented,
- application handles unavailable external videos gracefully.

OUTPUT
1. Release-blocking issues.
2. Fixed issues.
3. Remaining known risks.
4. Test results.
5. Deployment checklist.
6. Legal/safety items that must be completed before a real child-facing public launch.

Do not claim production readiness if critical safety, security, or legal items remain unresolved.

Agent Control Prompt --- Use Before Any Risky Refactor

Read docs/AI_MASTER_CONTEXT.md.

Before changing architecture, dependencies, authentication, authorization, age handling, moderation, feed ranking, or data ownership:

1. Explain the problem.
2. Explain why the current approach is insufficient.
3. Give the smallest viable change.
4. Give one alternative.
5. State migration/rollback risk.
6. List files/data affected.
7. Wait for approval before implementing if the change is materially architectural.

Definition of Done for Every Prompt

A task is not complete because the UI "looks right."

It is complete only when:

Required behavior works.

Authorization is correct.

Validation exists.

Relevant tests pass.

Type/lint checks pass.

No unrelated regressions are introduced.

No secret is exposed.

Documentation is updated when necessary.

The agent reports what changed.

A Git checkpoint can safely be created.