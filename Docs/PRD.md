# Personal Portfolio Website — V1 PRD

## 1. Summary

Build a modern personal portfolio website for a solo professional who is both a backend developer and a UI/UX designer. The product has two parts:

1. A public website that presents the owner professionally, showcases selected work, and drives inbound contact.
2. A private admin dashboard for managing portfolio content and reviewing visitor analytics without editing code.

V1 should prioritize credibility, clean presentation, project storytelling, easy content management, responsive performance, and practical analytics.

---

## 2. Problem / Goals

### Problem

The user needs a professional web presence that clearly communicates skills and quality of work, makes projects easy to explore, and converts visitors into inbound work opportunities. Managing content manually in code is inefficient and creates friction for keeping the portfolio current.

### Goals

- Present the owner as credible, technical, creative, and premium.
- Make project browsing and case study viewing the core experience.
- Help visitors quickly understand who the owner is, what they do, and how to contact them.
- Allow the owner to add, edit, reorder, and manage portfolio content through a private dashboard.
- Provide useful traffic and engagement analytics to understand portfolio performance.
- Keep V1 fast, responsive, maintainable, and SEO-friendly.

---

## 3. Target Users

### Primary User

- The portfolio owner/admin

### External Visitors

- Potential clients
- Hiring managers / recruiters
- Collaborators
- Other professionals evaluating quality and credibility

### Visitor Intent

- Assess professionalism and skill level
- Review past work and case studies
- Understand services and capabilities
- Reach out for work opportunities

---

## 4. User Roles & Permissions

### 1. Visitor

Permissions:

- View public pages
- Browse featured projects
- Browse all projects
- View project detail pages
- Use contact form
- Click outbound links such as email, WhatsApp, LinkedIn, GitHub, live demo, prototype, GitHub repo

### 2. Admin

Permissions:

- Secure login to dashboard
- Create, edit, delete projects
- Upload and manage project images
- Reorder projects
- Mark projects as featured
- Manage homepage content
- Manage services section
- Manage skills / tech stack
- Manage contact information and social links
- Edit SEO fields
- View visitor analytics
- View contact form submissions or notifications

Note: V1 supports one admin only.

---

## 5. Key Workflows

### A. Visitor browsing portfolio

1. Visitor lands on homepage.
2. Visitor sees strong hero section with identity, positioning, and CTA.
3. Visitor scrolls through featured projects, services, and skills/tech stack.
4. Visitor clicks into a project or contact option.
5. Visitor either continues exploring or submits a contact inquiry.

### B. Visitor viewing project details

1. Visitor opens a project card from homepage or projects listing page.
2. Visitor reads the project overview and case study details.
3. Visitor reviews visuals, process, tools/stack, outcomes, and links.
4. Visitor clicks live demo, prototype, GitHub, or contact CTA.

### C. Admin adding/editing/removing projects

1. Admin logs into dashboard.
2. Admin opens project management.
3. Admin creates a new project or edits an existing one.
4. Admin selects project type.
5. Admin fills project fields, uploads images, adds links, and sets featured status/order.
6. Admin saves changes.
7. Updated content appears on public site.

### D. Admin viewing analytics

1. Admin logs into dashboard.
2. Admin opens analytics page.
3. Admin reviews high-level metrics and project engagement.
4. Admin uses data to understand traffic, top project interest, and visitor geography.

---

## 6. Scope

### V1

- Public homepage
- Projects listing page
- Project detail pages
- Contact section and contact form
- Services section
- Skills / tech stack section
- Admin authentication
- Admin dashboard for content management
- Project CRUD
- Homepage content management
- Image upload and management
- Reordering and featuring projects
- Basic SEO fields
- Visitor analytics dashboard
- Responsive design
- Performance and SEO baseline

### Out of Scope

- Blog / article publishing
- Multi-admin roles
- Scheduling / publish dates
- Draft or hidden project states
- Testimonials CMS
- Multilingual support
- Dark mode toggle
- Advanced funnel analytics
- A/B testing
- Comments or visitor accounts
- CMS for every micro-section of the site
- File storage/version history beyond basic asset management

---

## 7. Requirements

### Functional Requirements

#### Public Website

**FR-1** The system must display a homepage with at minimum: hero section, featured projects section, services section, skills/tech stack section, and contact section.

**FR-2** The hero section must communicate the owner’s identity as a backend developer and UI/UX designer and include at least one CTA to contact or view projects.

**FR-3** The homepage must show featured projects only, with a clear link to a full projects page.

**FR-4** The system must provide a projects listing page that shows all published projects.

**FR-5** Each project must belong to a project type:

- Development project
- UI/UX design project

**FR-6** Each project must have a dedicated detail page.

**FR-7** Development project pages must support the following fields:

- Title
- Summary
- Problem
- Process
- Screenshots
- Tech stack
- Role
- Outcomes
- Live demo link
- GitHub link
- Timeline
- Challenges

**FR-8** UI/UX design project pages must support a design-oriented case study structure. Recommended V1 fields:

- Title
- Overview
- Problem
- Goals
- Role
- Design process
- Wireframes / mockups
- Final screens
- Design decisions
- Tools used
- Outcomes
- Prototype link

**FR-9** The system must gracefully hide any optional empty field on project pages rather than showing broken or empty sections.

**FR-10** The projects listing page must support project cards with at minimum title, type, summary/short description, thumbnail, and link to detail page.

**FR-11** The contact section must display:

- Contact form
- Gmail/email link
- WhatsApp button
- LinkedIn button
- GitHub button

**FR-12** The contact form must allow visitors to submit name, email, message, and optional subject.

**FR-13** The system must confirm successful contact form submission and handle failure states clearly.

#### Admin Dashboard

**FR-14** The system must provide a secure admin login page.

**FR-15** The dashboard must allow the admin to create, edit, and delete projects.

**FR-16** The dashboard must allow the admin to assign project type.

**FR-17** The dashboard must support image upload for project thumbnails and project galleries/screenshots.

**FR-18** The dashboard must allow the admin to mark projects as featured or not featured.

**FR-19** The dashboard must allow the admin to reorder projects for homepage and/or listing display.

**FR-20** The dashboard must allow the admin to manage homepage content, including hero text and supporting section content.

**FR-21** The dashboard must allow the admin to manage services section content.

**FR-22** The dashboard must allow the admin to manage skills / tech stack content.

**FR-23** The dashboard must allow the admin to manage contact details and social links.

**FR-24** The dashboard must allow the admin to edit per-page or per-project SEO fields, including at minimum meta title, meta description, and slug.

**FR-25** The dashboard must provide a project preview-oriented editing experience sufficient to reduce content mistakes.

**FR-26** The dashboard must show analytics including:

- Total visitors
- Page views
- Top projects viewed
- Traffic sources
- Contact form submissions
- Button/link clicks
- Device type
- Country

**FR-27** The dashboard must present analytics in a simple readable format, including date range filtering for standard periods such as 7, 30, and 90 days.

**FR-28** The system must track outbound clicks for key actions including email, WhatsApp, LinkedIn, GitHub, live demo, and project links.

#### Content and Data Handling

**FR-29** The system must support at least 3 projects at launch without empty-layout issues.

**FR-30** The system must continue to work when some projects do not have demo links, GitHub links, or screenshots.

**FR-31** The system must prevent broken links from being shown if a URL field is empty.

**FR-32** The system must store project content in a way that the admin can manage without changing source code.

**FR-33** The system must allow deletion of projects with confirmation to avoid accidental removal.

---

### Non-functional Requirements

**NFR-1 Performance**
Public pages should load quickly on mobile and desktop. Core content should render efficiently, and image-heavy project pages should use optimized assets.

**NFR-2 Responsiveness**
The website and dashboard must work across common mobile, tablet, and desktop screen sizes.

**NFR-3 SEO**
Public pages must support crawlable semantic content, metadata, clean URLs, and project-specific SEO fields.

**NFR-4 Accessibility**
V1 should follow practical accessibility basics: semantic HTML, alt text support, keyboard accessibility for key interactions, readable contrast, and labeled form fields.

**NFR-5 Security**
Dashboard access must require authentication. Admin routes must be protected. Contact form and admin inputs must include validation and abuse protection.

**NFR-6 Maintainability**
The codebase and content model should be simple to extend later with sections like testimonials or blog.

**NFR-7 Reliability**
Broken images, missing optional fields, or absent social/demo links should not break page layout.

**NFR-8 Content Editing Simplicity**
Common content edits should be possible in the dashboard without developer support.

---

## 8. Information Architecture / Pages

### Public Pages

#### 1. Homepage

Sections:

- Hero
- Featured Projects
- Services
- Skills / Tech Stack
- Contact
- Optional footer with social links and copyright

#### 2. Projects Page

- Projects grid/list
- Project cards
- Optional filters by project type in future, not required for V1

#### 3. Project Detail Page

- Dynamic per project
- Structure varies by project type but follows a consistent layout system

### Private Pages

#### 4. Admin Login

- Authentication form

#### 5. Dashboard Home

- Summary cards
- Quick actions
- High-level analytics snapshot

#### 6. Project Management

- Project list
- Add project
- Edit project
- Delete project
- Reorder projects
- Toggle featured

#### 7. Content Management

- Hero content
- Services
- Skills / tech stack
- Contact/social links
- SEO fields

#### 8. Analytics

- Overview metrics
- Project performance
- Country/device/source breakdown
- Click and submission tracking

---

## 9. Dashboard Features

Recommended realistic V1 dashboard features:

### Core Content Management

- Add/edit/delete projects
- Set project type
- Mark featured projects
- Reorder projects
- Upload/update project cover image and gallery images
- Edit hero text and supporting homepage text
- Update services content
- Update skills/tech stack list
- Update contact details and social links

### Useful Admin Features

- SEO fields for pages/projects
- Slug editing
- Confirm-before-delete
- Form submission view or notification support
- Basic analytics overview cards
- Asset preview for uploaded images

### Recommended But Keep Simple

- “Last updated” timestamp for projects
- Link validation warning for malformed URLs
- Empty-state prompts when no projects exist

Not recommended for V1:

- Draft workflow
- Publish scheduling
- Version history
- Multi-user permissions
- Complex CMS schemas

---

## 10. Analytics & Success Metrics

### Tracked Metrics

- Total visitors
- Page views
- Unique visitors if available
- Top project pages viewed
- Traffic sources
- Visitor country
- Device type
- Contact form submissions
- Clicks on:
    - Email
    - WhatsApp
    - LinkedIn
    - GitHub
    - Live demo
    - Project links

### Success Metrics for V1

- Portfolio is live and manageable without code edits
- Visitors can reach any project and contact method without friction
- Admin can add and update projects in minutes
- Contact conversion starts happening from site traffic
- Analytics reveal which projects attract the most attention
- Site performs well on mobile and feels premium/credible

### Suggested KPIs

- Number of contact form submissions per month
- Number of WhatsApp/email/link clicks
- Views per featured project
- Percentage of traffic reaching project detail pages
- Country distribution of visitors
- Bounce proxy via short session / single-page behavior if analytics tool supports it

---

## 11. UX Notes

### Screen List

- Homepage
- Projects listing page
- Development project detail page
- UI/UX design project detail page
- Contact form state
- Admin login
- Dashboard overview
- Projects list in dashboard
- Add/edit project form
- Content management screens
- Analytics screen

### Important UI States

#### Public Site

- Homepage with only 3 projects
- No live demo link on a project
- No GitHub link on a project
- Missing screenshots/gallery
- Contact form success
- Contact form error
- Slow image load / fallback image
- Empty featured projects fallback

#### Admin

- No projects yet
- Save success
- Validation errors
- Delete confirmation
- Empty analytics state
- Broken/malformed URL warning
- Image upload failure
- Unauthorized access to dashboard route

### UX Direction

- Clean, premium layout
- Strong typography and spacing
- Technical + creative balance
- Project cards should feel polished and clickable
- Project detail pages should prioritize readability and visual storytelling
- Contact actions should stay visible and easy to reach
- Mobile UX should keep CTAs and project exploration simple

---

## 12. Risks, Assumptions, Open Questions

### Risks

- Mixed portfolio types may create inconsistent project pages unless a clear template system is used.
- Image-heavy case studies may hurt load speed if assets are not optimized.
- Analytics can become noisy or hard to act on if too detailed for V1.
- No draft/hidden workflow means editing live content may require care.

### Assumptions

- Only one admin will manage the site.
- V1 launches with about 3 projects.
- The user wants a custom private dashboard rather than editing code manually.
- Featured projects are selected manually.
- Missing optional project fields should be hidden gracefully.
- Testimonials are not required for V1.

### Open Questions

- Final structure for UI/UX case studies can still be refined during design.
- Whether a dedicated About page is needed later.
- Whether contact submissions should be stored in dashboard, emailed, or both.
- Whether filtering by project type should be added in projects listing after launch.

---

## 13. Launch Plan

### Phase A — Foundation

- Finalize content model
- Finalize page structure
- Define project templates for development and UI/UX work
- Set analytics events and data requirements

### Phase B — Build

- Build public website pages
- Build admin auth and dashboard
- Build project CRUD and content management
- Implement analytics tracking
- Add SEO and performance baseline

### Phase C — Content Entry

- Add hero content
- Add services and skills
- Add 3 launch projects
- Review screenshots, links, and metadata

### Phase D — QA

- Test responsive layouts
- Test contact flow
- Test analytics events
- Test missing-field handling
- Test admin CRUD flows
- Verify performance and SEO basics

### Phase E — Launch

- Deploy site
- Verify analytics collection
- Submit/test first contact flow
- Review early traffic and top project engagement
- Refine featured projects and CTAs based on actual visitor behavior
