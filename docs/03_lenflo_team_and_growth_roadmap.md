# Lenflo Team and Growth Roadmap

**Rule:** Hire against demonstrated bottlenecks, not impressive job
titles.

## Stage 0 --- Solo MVP

### Team

-   Founder / Product Lead
-   AI coding tools

### Founder responsibilities

-   Product decisions
-   MVP scope control
-   Content curation
-   Basic Laravel/React implementation
-   Testing
-   Early user interviews
-   Documentation
-   Recruiting future collaborators

### Do not hire yet

-   Algorithm engineer
-   DevOps team
-   Data science team
-   Sales department
-   Creator operations department
-   Large moderation team

## Stage 1 --- MVP Stable / Private Testing

Bring people in only where the founder is becoming the bottleneck.

### Priority roles

**1. Strong full-stack or Laravel backend engineer** - Backend quality -
Data model - Auth/authorization - APIs - performance - testing -
security fundamentals

**2. Frontend / React engineer** - Feed experience - mobile
responsiveness - player lifecycle - interaction quality -
accessibility - frontend performance

One excellent full-stack engineer can temporarily cover both roles.

**3. QA / Test contributor** - Regression testing - device/browser
testing - test cases - release validation

**4. Content/Moderation operator** - Find content - classify content -
review content - maintain quality - document moderation decisions

## Stage 2 --- Private Beta

### Add when justified

**Product/UI Designer** - Onboarding - feed UX - accessibility - design
system - child/teen usability

**Content & Community Lead** - Editorial standards - content
partnerships - creator pipeline - community operations

**Data/Product Analyst** - Event quality - retention - funnel analysis -
feed performance - experiments

**Child Safety / Trust & Safety Advisor** - Moderation design -
reporting - abuse cases - youth-safety review - escalation procedures

Legal counsel should already be involved before public child-facing
launch.

## Stage 3 --- Growing Public Beta

### Engineering

**Senior Backend Engineer** - scaling - database performance - queues -
APIs - reliability

**Frontend Engineer** - web/PWA - feed performance - experimentation -
accessibility

**Platform/DevOps Engineer** Bring this role in when deployment, uptime,
observability, infrastructure cost, incident response, and scaling
become material work.

Responsibilities: - CI/CD - environments - monitoring - backups -
secrets - infrastructure - cost control - incident response

Do not create a "DevOps team" because the architecture diagram looks
better with one.

### Safety

**Trust & Safety / Moderation Lead** - policy enforcement - moderation
quality - escalations - reporting - creator enforcement - safety metrics

**Moderators** Scale with content volume and user-generated content.

## Stage 4 --- Personalization at Real Scale

### Algorithm / Recommendation Engineer

Bring this role in only when Lenflo has enough behavioral data to
improve ranking scientifically.

Responsibilities: - ranking models - candidate generation -
recommendation quality - exploration/exploitation - diversity -
cold-start improvements - evaluation - experimentation

### Data Engineer

Bring in when analytics/event pipelines become too large or unreliable
for normal application infrastructure.

Responsibilities: - event pipelines - warehouse/lake - data quality -
feature pipelines - experimentation datasets

### ML Engineer / Data Scientist

Bring in when there are clear ML problems with measurable
business/safety outcomes.

Do not hire an ML team to make Lenflo sound like an AI company.

## Stage 5 --- Creator Platform

Before opening uploads widely, add capacity in:

-   Trust & Safety
-   Content moderation
-   Creator operations
-   Copyright/IP handling
-   Backend/media engineering
-   Storage/transcoding/CDN engineering
-   Support
-   Fraud/abuse prevention
-   Legal

### Creator Operations

-   Invite promising users/teachers
-   Vet creators
-   Onboard creators
-   Educate on policies
-   Track creator quality
-   Manage strikes/appeals
-   Support monetization later

## Stage 6 --- Monetization

Possible functions:

**Partnerships** - schools - NGOs - educational institutions - brands -
telecoms - governments where appropriate

**Sales / Institutional Growth** - school plans - organization plans -
learning partnerships

**Advertising Operations** Only after child-safety and advertising rules
are legally reviewed country by country.

**Finance** - payments - creator payouts - revenue reporting - taxes -
reconciliation

**Creator Monetization** - eligibility - fraud prevention - payout
rules - quality standards - policy enforcement

## Stage 7 --- Multi-Country / Global Expansion

Add regional capabilities rather than assuming one global operating
model.

Possible roles:

-   Regional General Manager
-   Localization lead
-   Local content partnerships
-   Regional moderation
-   Local legal counsel
-   Policy specialists
-   Local marketing/community
-   Customer support
-   Security/privacy specialists

## Hiring Order --- Default

A reasonable default sequence:

1.  Founder + AI
2.  Strong full-stack/backend engineering support
3.  Frontend/product engineering support
4.  QA
5.  Content/moderation operations
6.  Product designer
7.  Child safety / Trust & Safety
8.  Data/product analyst
9.  Platform/DevOps
10. Recommendation/algorithm engineer
11. Data engineer
12. Creator operations
13. Growth/partnerships
14. Finance/monetization specialists
15. Regional teams

The exact order changes when real bottlenecks change.

## When to Split the Modular Monolith

Do not split services based on user-count folklore.

Consider service extraction only when a domain has a real reason, such
as:

-   independent scaling characteristics
-   independent release cadence
-   failure isolation
-   specialist ownership
-   very high throughput
-   separate data/storage needs
-   security isolation

Potential future candidates:

-   Recommendation service
-   Media processing
-   Notifications
-   Search
-   Analytics/event ingestion
-   Payments
-   Moderation automation

Until then, keep the monolith modular.

## Operational Milestones

### Before Private Beta

-   Automated tests for critical flows
-   Backups
-   Error logging
-   Basic monitoring
-   Moderation workflow
-   Content quality checklist
-   Privacy/security review
-   User feedback channel

### Before Public Beta

-   Legal review
-   Child-safety review
-   Incident-response process
-   Reporting/escalation process
-   Privacy policy
-   Terms
-   Moderation/community rules
-   Data retention rules
-   Security review
-   Backup restore test
-   Rate limiting
-   Abuse controls
-   Analytics quality
-   Content operations staffing

### Before User Uploads

-   Upload moderation
-   Reporting
-   blocking
-   appeals
-   creator policy
-   copyright/IP process
-   media security
-   malware/file validation
-   moderation staffing
-   escalation procedures
-   repeat-offender policy

### Before DMs

Treat this as a major safety milestone, especially for minors.

Require specialist review for:

-   minor/adult interaction
-   grooming risk
-   unsolicited contact
-   reporting
-   blocking
-   link/file sharing
-   automated detection
-   parental controls
-   law-enforcement escalation
-   retention

DMs are not a "small feature."

## Founder Rule

The founder's job changes over time:

**MVP:** builder\
**Beta:** product operator\
**Growth:** recruiter and system designer\
**Scale:** leader who creates teams, accountability, capital strategy,
partnerships, and governance

Do not remain the approval bottleneck forever. Build systems,
permissions, metrics, and accountable owners.
