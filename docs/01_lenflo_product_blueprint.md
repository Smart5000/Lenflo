# Lenflo Product Blueprint

**Product:** Lenflo\
**Name meaning:** Learning Flow\
**Status:** Working product blueprint\
**Primary launch market:** Africa first; global expansion later\
**Core principle:** Build the smallest safe, useful learning feed first.
Do not build the full social network during MVP.

## 1. Vision

Lenflo is an age-aware learning and creativity platform built around a
short-form vertical feed.

The long-term vision is a social platform that grows with the user. A
child, teenager, student, young adult, creator, teacher, or adult should
receive content appropriate to their age, interests, learning stage, and
safety profile.

Lenflo should make educational, creative, skill-building, and inspiring
content as engaging to consume as mainstream short-form social media.

## 2. Problem

Mainstream social platforms optimize heavily for engagement. Parents,
schools, and young users have limited control over whether a feed
consistently produces safe, educational, creative, or developmentally
appropriate content.

Lenflo's opportunity is not to become "another TikTok." Its opportunity
is to own a narrower promise:

> A compelling feed where learning, creativity, inspiration, and
> age-appropriate discovery are the default.

## 3. Product Positioning

### Core positioning

**An age-aware learning and creativity feed for Africa, designed to
scale globally.**

### Differentiators

-   Education and creativity first.
-   Age-aware content delivery.
-   Human-approved content during early stages.
-   Africa-first relevance without restricting the brand to Africa.
-   Short-form, swipe-first interaction.
-   Designed to evolve from curated content into a creator ecosystem.
-   Safety and trust treated as product architecture, not an
    afterthought.

## 4. Target Users

Lenflo is ultimately multi-age, but content and features must be
segmented.

Suggested internal age bands:

-   Early learners
-   Older children
-   Teenagers
-   Young adults / students
-   Adults

Exact age boundaries must be finalized with legal and child-safety
counsel before public launch in each country.

Age must never be treated as a cosmetic profile field. It affects:

-   Content eligibility
-   Data collection
-   Account capabilities
-   Engagement features
-   Personalization
-   Advertising eligibility
-   Creator capabilities
-   Moderation rules
-   Parental consent requirements

## 5. MVP Scope

The MVP proves one thing: **Lenflo can deliver a smooth, age-relevant,
approved learning feed that users can interact with.**

### MVP includes

-   Responsive web application
-   Mobile-first vertical video feed
-   Touch swipe / scroll navigation
-   Up/down keyboard navigation on desktop
-   YouTube embedded videos
-   Admin-managed content
-   YouTube API-assisted content discovery for admins
-   Manual YouTube URL submission by admins
-   Approval workflow before content becomes visible
-   Guest browsing before registration
-   Email authentication
-   Google authentication
-   Age-aware onboarding
-   Like
-   Save
-   Share
-   Basic engagement tracking
-   Super Admin
-   Foundation for role-based admin accounts
-   Dark mode
-   Responsive behavior suitable for phone testing
-   PWA-friendly architecture

### Explicitly excluded from MVP

-   User uploads
-   Creator monetization
-   Comments
-   Direct messages
-   Live streaming
-   Native iOS application
-   Native Android application
-   Complex machine-learning recommendation engine
-   Advertising marketplace
-   School management system
-   Creator payouts
-   Public creator onboarding
-   Full parental dashboard
-   Microservices

If a feature does not help prove the feed, age relevance, content
safety, or basic engagement, it should not enter MVP.

## 6. MVP User Journey

1.  User opens Lenflo.
2.  User can immediately view a limited approved feed as a guest.
3.  User scrolls/swipes vertically through videos.
4.  Lenflo encourages onboarding after initial exploration.
5.  User provides age information through a neutral
    age-screening/onboarding flow.
6.  User signs in with Google or email when account features are needed.
7.  Feed becomes age-relevant based on approved content metadata.
8.  User can watch, like, save, and share.
9.  Engagement is recorded for future personalization.
10. User returns to a clean feed rather than a dashboard-heavy
    interface.

For minors, the actual registration and consent flow must be adjusted by
jurisdiction before production launch.

## 7. Content Strategy

### MVP content source

YouTube is the primary external content source.

Two admin-side ingestion methods:

1.  **YouTube API suggestions/search**
    -   Admin searches or receives candidate content.
    -   Candidate is not automatically published.
    -   Admin reviews it.
    -   Admin assigns age band, topic, language, country relevance, and
        safety metadata.
    -   Admin approves or rejects it.
2.  **Manual YouTube URL**
    -   Admin pastes a YouTube URL.
    -   Lenflo fetches permitted metadata.
    -   Admin classifies and reviews.
    -   Video enters the feed only after approval.

### Non-negotiable rule

**External API results never go directly into a child's feed merely
because an API returned them.**

The MVP is a curated library with API-assisted discovery, not an
uncontrolled external content firehose.

### Initial content categories

-   STEM
-   Coding and technology
-   Mathematics
-   Science
-   Languages
-   Music
-   Art and design
-   Creativity
-   Entrepreneurship
-   Financial literacy
-   Study skills
-   Career exploration
-   History and culture
-   Personal development
-   Problem solving
-   Making/building
-   Educational games
-   Inspiration

Categories must be refined by age.

## 8. Feed and Recommendation Strategy

### MVP recommendation system

Do not build "AI recommendation" first.

Use a deterministic scoring system based on:

-   Age eligibility
-   Approved status
-   Topic/category
-   Language
-   Country/region relevance
-   Recency
-   Content quality score
-   User likes
-   User saves
-   User skips
-   Watch completion
-   Repeated topic engagement
-   Diversity rules

The feed should deliberately avoid showing endless repetitions of one
topic.

### Later recommendation evolution

**Stage 1:** Rules and curated ranking\
**Stage 2:** Behavioral scoring\
**Stage 3:** Collaborative/content-based recommendation\
**Stage 4:** Dedicated recommendation/data platform\
**Stage 5:** ML ranking, experimentation, embeddings, creator/content
quality models

An algorithm engineer becomes valuable after Lenflo has enough real
behavioral data. Hiring one before that is premature.

## 9. MVP Success Definition

MVP is functionally successful when:

-   Feed loads reliably.
-   Video playback works.
-   Mobile swipe/scroll feels smooth.
-   Keyboard navigation works.
-   Age-eligible users receive correct approved content.
-   Guest-to-account flow works.
-   Like works.
-   Save works.
-   Share works.
-   Admin can discover/import, classify, approve, reject, and publish
    content.
-   Engagement events are captured correctly.
-   Application works on desktop and mobile browser.
-   No unapproved content can leak into the public feed.

After functional success, beta success metrics should include:

-   Feed session length
-   Video completion rate
-   Skip rate
-   Return rate
-   Sign-up conversion
-   Likes per session
-   Saves per session
-   Shares per session
-   Content approval throughput
-   Safety/moderation incidents

## 10. Technical Architecture

### Architecture choice

**Modular monolith.**

This is one deployable Laravel application with clear domain boundaries.
"Modular" is an architectural discipline, not a separate Laravel product
that must be installed.

Suggested domains:

-   Identity
-   Users
-   Profiles
-   Content
-   Feed
-   Engagement
-   Moderation
-   Admin
-   Analytics
-   Notifications
-   Creator (later)
-   Monetization (later)

Do not introduce microservices until actual scaling pressure proves they
are needed.

### Recommended stack

-   Laravel 13
-   PHP 8.3+
-   React 19
-   TypeScript
-   Inertia 3
-   Tailwind CSS 4
-   shadcn/ui where useful
-   Vite
-   PostgreSQL
-   Redis/Valkey when caching/queues justify it
-   Laravel queues
-   Laravel Horizon when Redis-backed queue monitoring is introduced
-   Pest or PHPUnit for automated testing
-   Git + GitHub
-   Laravel Herd for local PHP development
-   VS Code
-   OpenCode / AI coding assistant
-   YouTube Data API + embedded player
-   Object storage later for Lenflo-owned media
-   CDN later for owned static/media assets

### Why this stack

It keeps one codebase and one deployment while allowing a rich React
interface. It is strong enough for a serious production system without
forcing a solo MVP builder into distributed-system complexity.

## 11. Suggested Project Structure

Keep Laravel conventions wherever possible. Add domain organization only
where it improves clarity.

Example conceptual structure:

``` text
app/
  Domain/
    Identity/
    Users/
    Content/
    Feed/
    Engagement/
    Moderation/
  Http/
  Models/
  Policies/
  Jobs/
  Services/

resources/js/
  components/
  features/
    feed/
    auth/
    engagement/
    admin/
  pages/
  layouts/
  types/

docs/
  AI_MASTER_CONTEXT.md
  prompts/
  architecture/
  decisions/
```

Do not create hundreds of abstraction layers just to look "enterprise."
Clean boundaries beat ceremony.

## 12. Admin Architecture

### MVP

-   Super Admin
-   Content discovery
-   Manual URL import
-   Candidate queue
-   Content classification
-   Approval/rejection
-   Publishing controls
-   Basic user overview
-   Basic engagement analytics

### Later role-based admin examples

-   Content Moderator
-   Child Safety
-   Editorial
-   Marketing
-   Partnerships
-   Finance
-   Support
-   Analytics
-   Creator Operations
-   Legal/Compliance

Every admin role should use least-privilege permissions.

## 13. Brand Direction

### Name

**Lenflo**

Derived from "learning flow."

### Brand personality

-   Modern
-   Intelligent
-   Energetic
-   Safe
-   Creative
-   Youthful without being childish
-   African-first without relying on stereotypical visual language
-   Globally credible

### Visual direction

**Signature style:** Aurora Flow

-   Deep navy/dark base
-   Indigo
-   Teal
-   Controlled violet glow
-   Warm gold used sparingly for progress/reward/accent
-   Soft gradients rather than loud rainbow surfaces
-   Strong contrast and accessibility
-   Motion should feel smooth, not distracting

### Dark mode

Dark mode is first-class, not an afterthought.

-   Deep navy background
-   Slate surfaces
-   Soft-white primary text
-   Muted secondary text
-   Indigo/teal interactive highlights
-   Small gold accents
-   System theme detection
-   User-selectable theme later

## 14. Scaling Principles

-   Scale product complexity only after usage proves the need.
-   Keep database design clean from day one.
-   Use queues for slow work.
-   Cache hot data when measurements justify it.
-   Keep feed ranking behind a service boundary so it can evolve.
-   Track meaningful events from MVP.
-   Add observability before large public growth.
-   Separate owned media from embedded third-party media.
-   Design country/language metadata early.
-   Never hard-code Nigeria-only assumptions.
-   Never assume all African countries share one legal regime.
-   Add feature flags before risky rollouts.
-   Maintain API/version boundaries for future mobile clients.

## 15. Long-Term Roadmap

### Milestone 1 --- MVP

Curated age-aware learning feed.

### Milestone 2 --- Private Beta

Real users, better analytics, moderation workflow, content operations,
stronger onboarding, performance fixes.

### Milestone 3 --- Personalization

Behavioral ranking, interest profiles, feed experiments, improved
search/discovery.

### Milestone 4 --- Creator Foundation

Selected creators, creator profiles, controlled uploads, creator review,
content moderation, analytics.

### Milestone 5 --- Social Layer

Comments/replies only after safety architecture is mature. Follow graph,
creator discovery, notifications, reporting, blocking, parental/safety
controls.

### Milestone 6 --- Monetization

Potential channels:

-   Advertising
-   Sponsorships
-   Premium subscriptions
-   School/institution plans
-   Partnerships
-   Creator revenue share
-   Paid learning products
-   Marketplace opportunities

Monetization must never compromise child-safety obligations.

### Milestone 7 --- Global Expansion

Country-specific legal review, localization, moderation capacity,
language support, regional content partnerships, infrastructure
expansion.

## 16. Critical Risks

### Safety

One serious child-safety failure can damage the product permanently.

### Content supply

A feed dies if it repeatedly shows mediocre content.

### Retention

"Educational" does not automatically mean engaging.

### Data cost and connectivity

African markets vary widely in bandwidth, device capability, and data
affordability. Lenflo should eventually support low-data behavior,
aggressive performance optimization, and graceful degradation.

### Platform dependency

YouTube is useful for MVP but is not Lenflo's moat. API terms, quotas,
player behavior, or access can change.

### Legal fragmentation

Africa is not one legal jurisdiction.

### Cold start

There is no useful ML recommendation model without data. Human curation
and rules solve the early cold-start problem.

## 17. Product Rules

1.  Safety beats engagement.
2.  Approved content beats unlimited content.
3.  Measure before optimizing.
4.  No microservices for prestige.
5.  No creator uploads until moderation is ready.
6.  No DMs for minors until a serious safety design exists.
7.  No targeted advertising to children without specialist legal review.
8.  No feature enters MVP merely because another social platform has it.
9.  Every feature must have a measurable purpose.
10. Lenflo's moat must eventually become trust + content quality + local
    relevance + personalization + creator ecosystem, not simply a
    YouTube wrapper.
