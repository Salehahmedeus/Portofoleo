# Backend Tasks — Laravel

> Personal Portfolio Website V1

---

## Milestone 1: Project Setup & Configuration

Set up the Laravel backend foundation — database, auth scaffolding, API structure, and environment configuration.

### Tasks

- [ ] Configure the database connection (SQLite for dev, MySQL/PostgreSQL for production)
- [ ] Set up environment variables (.env) for app URL, DB, mail, file storage
- [ ] Configure CORS settings for API requests from the React frontend
- [ ] Set up file storage driver (local for dev, S3/cloud for production)
- [ ] Install and configure Inertia.js server-side adapter for Laravel–React communication
- [ ] Set up route file organization (web.php for public, admin routes with middleware)
- [ ] Configure error handling and custom exception responses
- [ ] Set up logging configuration

---

## Milestone 2: Database Schema & Models

Design and implement the database schema that supports all portfolio content, analytics, and admin features.

### Tasks

- [x] Create `projects` migration — id, title, slug, type (enum: development/uiux), summary, featured (boolean), sort_order, thumbnail path, SEO fields (meta_title, meta_description), timestamps
- [x] Create `project_details` migration — id, project_id (FK), field_name, field_value (text/JSON), to support flexible fields per project type
- [x] Create `project_images` migration — id, project_id (FK), image_path, alt_text, sort_order, type (thumbnail/gallery/wireframe/screenshot), timestamps
- [x] Create `services` migration — id, title, description, icon, sort_order, timestamps
- [x] Create `skills` migration — id, name, category, icon/logo_path, sort_order, timestamps
- [x] Create `site_settings` migration — id, key (unique), value (text/JSON), group, timestamps — for hero content, contact info, social links, page SEO
- [x] Create `contact_submissions` migration — id, name, email, subject, message, read (boolean), ip_address, timestamps
- [x] Create `analytics_events` migration — id, event_type, event_data (JSON), page_url, referrer, device_type, country, ip_address, session_id, timestamps
- [x] Create Eloquent models for all tables with relationships, casts, and fillable/guarded attributes
- [x] Create database seeder with sample data for development and testing

---

## Milestone 3: Authentication & Admin Middleware

Secure the admin dashboard with authentication and protect all admin routes.

### Tasks

- [x] Configure Laravel authentication (Breeze / Fortify / Sanctum)
- [x] Set up admin user seeder (single admin account for V1)
- [x] Create admin authentication middleware to protect dashboard routes
- [x] Implement login endpoint with proper validation and error responses
- [x] Implement logout endpoint with session invalidation
- [x] Configure session management and CSRF protection
- [x] Add rate limiting on login attempts to prevent brute-force attacks
- [x] Set up two-factor authentication support (optional, if already scaffolded)

---

## Milestone 4: Public API — Portfolio Content

Build the API endpoints that serve portfolio data to the public-facing React frontend.

### Tasks

- [ ] Create `HomeController` — return hero content, featured projects, services, skills, contact info, social links
- [ ] Create `ProjectController@index` — return all published projects with pagination or full list (cards data: title, slug, type, summary, thumbnail)
- [ ] Create `ProjectController@show` — return full project detail by slug, including all detail fields, images, and links
- [ ] Create API Resource / Transformer classes for Project, Service, Skill to standardize JSON responses
- [ ] Implement graceful handling of missing optional fields (null fields excluded from response)
- [ ] Add response caching for public endpoints to improve performance
- [ ] Set proper SEO-friendly slugs for project URLs using route model binding

---

## Milestone 5: Contact Form Submission

Handle incoming contact form submissions with validation, storage, and notification.

### Tasks

- [ ] Create `ContactController@store` endpoint for form submissions
- [ ] Create `ContactFormRequest` with validation rules: name (required), email (required, valid format), subject (optional), message (required)
- [ ] Store submissions in `contact_submissions` table
- [ ] Send email notification to admin on new submission (configurable recipient)
- [ ] Implement rate limiting on contact submissions (e.g., max 3 per IP per hour)
- [ ] Add honeypot field validation for spam protection
- [ ] Return appropriate success/error JSON responses

---

## Milestone 6: Admin — Project CRUD API

Full project management endpoints for the admin dashboard.

### Tasks

- [ ] Create `Admin\ProjectController@index` — list all projects with search, filter by type, sort options
- [ ] Create `Admin\ProjectController@store` — create a new project with all fields and validation
- [ ] Create `Admin\ProjectController@show` — return single project for editing
- [ ] Create `Admin\ProjectController@update` — update project fields, images, SEO, featured status
- [ ] Create `Admin\ProjectController@destroy` — delete project with cascade (images, details)
- [ ] Create `StoreProjectRequest` and `UpdateProjectRequest` with comprehensive validation rules
- [ ] Implement image upload handling — validate file type/size, store in configured disk, generate thumbnails
- [ ] Create endpoint for reordering projects (`Admin\ProjectController@reorder`)
- [ ] Create endpoint for toggling featured status (`Admin\ProjectController@toggleFeatured`)
- [ ] Implement slug auto-generation from title with uniqueness check
- [ ] Add image deletion endpoint for removing individual gallery images
- [ ] Handle URL validation for external links (live demo, GitHub, prototype)

---

## Milestone 7: Admin — Content Management API

Endpoints for managing homepage content, services, skills, contact info, and site-wide settings.

### Tasks

- [ ] Create `Admin\SettingsController` — CRUD for site_settings (hero text, contact info, social links, page SEO)
- [ ] Create `Admin\ServiceController` — CRUD for services with reordering
- [ ] Create `Admin\SkillController` — CRUD for skills with category management and reordering
- [ ] Create validation requests for each content type
- [ ] Implement bulk update support for reordering (services, skills)
- [ ] Add validation for social/contact URLs (LinkedIn, GitHub, WhatsApp formats)
- [ ] Return structured JSON responses with before/after data on updates

---

## Milestone 8: Analytics Tracking & Reporting

Track visitor behavior and serve aggregated analytics data to the admin dashboard.

### Tasks

- [ ] Create analytics tracking middleware — capture page views, device type, referrer, country (via IP geolocation)
- [ ] Create `AnalyticsController@trackEvent` endpoint — log custom events (outbound clicks on email, WhatsApp, LinkedIn, GitHub, demo links)
- [ ] Create `Admin\AnalyticsController@overview` — return summary metrics: total visitors, page views, unique visitors, contact submissions count
- [ ] Create `Admin\AnalyticsController@topProjects` — return most-viewed projects ranked
- [ ] Create `Admin\AnalyticsController@sources` — return traffic source breakdown
- [ ] Create `Admin\AnalyticsController@devices` — return device type distribution
- [ ] Create `Admin\AnalyticsController@countries` — return visitor country distribution
- [ ] Create `Admin\AnalyticsController@clicks` — return outbound click counts grouped by channel
- [ ] Implement date range filtering for all analytics endpoints (7d, 30d, 90d, custom)
- [ ] Add session tracking logic to distinguish unique visitors from repeat page views
- [ ] Create a scheduled command to clean up old raw analytics data (retain aggregated data)

---

## Milestone 9: Image & File Management

Robust image handling for project assets — upload, storage, optimization, and cleanup.

### Tasks

- [ ] Configure file storage disk (local for dev, S3 for production)
- [ ] Implement image upload service — validate, resize, optimize, store
- [ ] Generate multiple image sizes (thumbnail, medium, large) for responsive delivery
- [ ] Convert uploaded images to WebP format for performance
- [ ] Implement image deletion with storage cleanup
- [ ] Add orphan image cleanup logic (images not linked to any project)
- [ ] Set maximum file size and allowed MIME type validation
- [ ] Return public URLs for stored images in API responses

---

## Milestone 10: SEO, Performance & Security Hardening

Final backend optimizations for production readiness.

### Tasks

- [ ] Implement server-side rendering meta tags via Inertia.js head management
- [ ] Generate dynamic `sitemap.xml` with all public pages and project URLs
- [ ] Add `robots.txt` configuration
- [ ] Implement response caching for public endpoints (projects, homepage data)
- [ ] Add database query optimization — eager loading, indexing on frequently queried columns (slug, type, featured, sort_order)
- [ ] Implement API rate limiting on all public endpoints
- [ ] Add input sanitization on all user-submitted content (contact form, admin inputs)
- [ ] Configure Content Security Policy (CSP) headers
- [ ] Add HTTPS redirect enforcement for production
- [ ] Review and harden admin route protection
- [ ] Set up database backups strategy documentation
- [ ] Run security audit on dependencies (`composer audit`)
