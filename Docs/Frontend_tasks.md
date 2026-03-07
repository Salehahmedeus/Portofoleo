# Frontend Tasks — React

> Personal Portfolio Website V1

---

## Milestone 1: Project Setup & Foundation

Set up the React frontend tooling, design system, folder structure, and shared utilities that every other feature will build on.

### Tasks

- [ ] Configure Vite + React project structure (pages, components, layouts, hooks, types)
- [ ] Set up React Router / Inertia.js routing with proper page resolution
- [ ] Define the global CSS design system — color palette, typography scale, spacing tokens, breakpoints
- [ ] Import and configure premium web fonts (e.g., Inter, Outfit) via Google Fonts
- [ ] Create reusable base UI components: Button, Input, Textarea, Card, Badge, Modal, Tooltip
- [ ] Build responsive layout primitives: Container, Grid, Section wrapper
- [ ] Set up a shared Axios/Fetch API service layer for backend communication
- [ ] Configure TypeScript types for all shared data models (Project, Service, Skill, ContactSubmission, Analytics)
- [ ] Set up global error boundary and 404 fallback page
- [ ] Add SEO helper component for managing meta tags per page (title, description, OG tags)

---

## Milestone 2: Public Homepage

Build the main landing page — the first thing visitors see. It must feel premium, credible, and immediately communicate who the owner is.

### Tasks

- [ ] Build Hero section — bold headline, subtitle (Backend Developer & UI/UX Designer), CTA buttons (View Projects, Contact Me)
- [ ] Add animated background or subtle motion effects to the hero for visual impact
- [ ] Build Featured Projects section — display 3 featured project cards with thumbnail, title, type badge, and summary
- [ ] Create ProjectCard component with hover effects, image lazy-loading, and link to detail page
- [ ] Add a "View All Projects" CTA linking to the projects listing page
- [ ] Build Services section — display service offerings in a visually appealing card/grid layout
- [ ] Build Skills / Tech Stack section — categorized skill display with icons or logos
- [ ] Build Contact section — contact form + sidebar with email, WhatsApp, LinkedIn, GitHub links
- [ ] Build Footer — social links, copyright, and optional navigation links
- [ ] Implement smooth scroll navigation between homepage sections
- [ ] Ensure full responsive design across mobile, tablet, and desktop breakpoints
- [ ] Add scroll-triggered reveal animations for each section

---

## Milestone 3: Projects Listing Page

A dedicated page where visitors can browse all published projects at a glance.

### Tasks

- [ ] Build Projects listing page layout with responsive grid
- [ ] Fetch and display all published projects from the backend API
- [ ] Reuse ProjectCard component with consistent styling
- [ ] Display project type badge (Development / UI/UX Design) on each card
- [ ] Handle empty state gracefully when no projects exist
- [ ] Handle loading and error states with skeleton loaders or spinners
- [ ] Ensure SEO meta tags are set for the projects listing page

---

## Milestone 4: Project Detail Pages

Dynamic detail pages that tell the story of each project. Structure differs based on project type (Development vs. UI/UX Design).

### Tasks

- [ ] Build shared Project Detail page layout (hero banner, content sections, sidebar/metadata)
- [ ] Implement Development project template with sections: Summary, Problem, Process, Screenshots gallery, Tech Stack, Role, Outcomes, Challenges, Timeline
- [ ] Implement UI/UX Design project template with sections: Overview, Problem, Goals, Role, Design Process, Wireframes/Mockups, Final Screens, Design Decisions, Tools Used, Outcomes
- [ ] Build an image gallery / lightbox component for screenshots, wireframes, and final screens
- [ ] Render action links conditionally — Live Demo, GitHub, Prototype (hide if empty)
- [ ] Gracefully hide any section where the data is empty or null
- [ ] Add "Back to Projects" navigation and previous/next project links
- [ ] Add project-specific SEO meta tags (title, description, OG image)
- [ ] Add CTA at the bottom (e.g., "Interested in working together? Get in touch")
- [ ] Ensure responsive layout for all detail page variants

---

## Milestone 5: Contact Form & Outbound Links

Enable visitors to reach out via a form or direct channels. Track engagement on outbound links.

### Tasks

- [ ] Build Contact Form component with fields: Name, Email, Subject (optional), Message
- [ ] Add client-side validation (required fields, email format)
- [ ] Integrate form submission with backend API endpoint
- [ ] Show success confirmation message after submission
- [ ] Show clear error messages on failure (network, validation)
- [ ] Build contact channel buttons: Email (mailto), WhatsApp (wa.me), LinkedIn, GitHub
- [ ] Add click tracking for outbound links (email, WhatsApp, LinkedIn, GitHub, Live Demo)
- [ ] Implement anti-spam measures (honeypot field or simple rate-limit UX)

---

## Milestone 6: Authentication & Admin Layout

Secure login flow and the dashboard shell that wraps all admin pages.

### Tasks

- [ ] Build Admin Login page with email/password form, validation, and error handling
- [ ] Style the login page with premium dark-mode design
- [ ] Integrate login with Laravel backend auth (session / Sanctum)
- [ ] Build authenticated Dashboard layout — sidebar navigation, top bar with user info, content area
- [ ] Add navigation items: Dashboard Home, Projects, Content, Analytics, Settings
- [ ] Implement auth guard / middleware to redirect unauthenticated users
- [ ] Add logout functionality
- [ ] Ensure the dashboard layout is responsive (collapsible sidebar on mobile)

---

## Milestone 7: Admin — Project Management (CRUD)

The core admin feature: create, edit, delete, reorder, and feature projects.

### Tasks

- [ ] Build Projects list view in dashboard — table/card view with title, type, featured status, actions
- [ ] Build Add Project form — all fields for both project types, project type selector
- [ ] Build Edit Project form — pre-populated with existing data
- [ ] Implement image upload for project thumbnail and gallery (drag & drop + file picker)
- [ ] Show image previews and allow removal of uploaded images
- [ ] Add "Featured" toggle per project
- [ ] Implement drag-and-drop or manual reordering for project display order
- [ ] Add Delete project with confirmation modal
- [ ] Implement form validation for required fields and URL formats
- [ ] Add slug editing with auto-generation from title
- [ ] Add SEO fields (meta title, meta description) per project
- [ ] Show "last updated" timestamp on each project
- [ ] Handle save success / error feedback with toast notifications
- [ ] Add empty state prompt when no projects exist yet

---

## Milestone 8: Admin — Content & Settings Management

Allow the admin to update homepage content, services, skills, contact info, and social links without touching code.

### Tasks

- [ ] Build Hero Content editor — edit headline, subtitle, CTA text
- [ ] Build Services editor — add, edit, remove, reorder services
- [ ] Build Skills / Tech Stack editor — add, edit, remove, categorize skills
- [ ] Build Contact & Social Links editor — update email, phone, WhatsApp, LinkedIn, GitHub URLs
- [ ] Add URL validation and warn on malformed links
- [ ] Implement save/update with feedback (toast notifications)
- [ ] Add per-page SEO fields editor (homepage meta title, description)

---

## Milestone 9: Admin — Analytics Dashboard

Give the admin a clear view of how the portfolio is performing.

### Tasks

- [ ] Build Analytics overview page with summary cards: Total Visitors, Page Views, Contact Submissions, Top Project
- [ ] Add date range filter (7 days, 30 days, 90 days)
- [ ] Build "Top Projects Viewed" chart or ranked list
- [ ] Build "Traffic Sources" breakdown (direct, social, search, referral)
- [ ] Build "Device Type" pie/donut chart (mobile, desktop, tablet)
- [ ] Build "Country Distribution" list or map visualization
- [ ] Build "Contact Submissions" list view
- [ ] Build "Outbound Click" tracking view (clicks per channel)
- [ ] Handle empty analytics state gracefully with helpful messaging

---

## Milestone 10: Polish, Performance & Accessibility

Final quality pass to ensure the site feels premium, loads fast, and is usable by everyone.

### Tasks

- [ ] Audit and optimize image loading (lazy loading, WebP/AVIF, responsive sizes)
- [ ] Add page transition animations between routes
- [ ] Review and refine hover states, focus states, micro-animations across all components
- [ ] Ensure keyboard navigation works for all interactive elements
- [ ] Add proper `alt` text support for all images
- [ ] Verify semantic HTML structure (single `<h1>` per page, heading hierarchy)
- [ ] Ensure form fields have proper labels and `aria` attributes
- [ ] Test and fix responsive layout issues across breakpoints (320px–1920px)
- [ ] Run Lighthouse audit and address performance, accessibility, and SEO findings
- [ ] Cross-browser testing (Chrome, Safari, Firefox, Edge)
- [ ] Verify all empty/missing field states render gracefully without layout breaks
