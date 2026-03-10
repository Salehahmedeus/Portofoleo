<?php

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\ContactSubmission;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class PortfolioContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::query()->create([
            'title' => 'Web Application Development',
            'description' => 'Build scalable Laravel and React applications with maintainable architecture and clear API contracts.',
            'icon' => 'code',
            'sort_order' => 1,
        ]);

        Service::query()->create([
            'title' => 'UI/UX Design Systems',
            'description' => 'Design cohesive interfaces and reusable component systems that improve usability and speed up delivery.',
            'icon' => 'layout',
            'sort_order' => 2,
        ]);

        Service::query()->create([
            'title' => 'Performance Optimization',
            'description' => 'Audit and optimize frontend and backend performance for improved Core Web Vitals and API response times.',
            'icon' => 'gauge',
            'sort_order' => 3,
        ]);

        Skill::query()->create([
            'name' => 'Laravel',
            'category' => 'backend',
            'logo_path' => 'skills/laravel.svg',
            'sort_order' => 1,
        ]);

        Skill::query()->create([
            'name' => 'React',
            'category' => 'frontend',
            'logo_path' => 'skills/react.svg',
            'sort_order' => 2,
        ]);

        Skill::query()->create([
            'name' => 'Tailwind CSS',
            'category' => 'frontend',
            'logo_path' => 'skills/tailwind.svg',
            'sort_order' => 3,
        ]);

        Skill::query()->create([
            'name' => 'Figma',
            'category' => 'design',
            'logo_path' => 'skills/figma.svg',
            'sort_order' => 4,
        ]);

        $commerceProject = Project::query()->create([
            'title' => 'Nova Commerce Platform',
            'slug' => 'nova-commerce-platform',
            'type' => 'development',
            'summary' => 'A multi-vendor commerce platform with role-based dashboards, analytics, and optimized checkout flow.',
            'featured' => true,
            'sort_order' => 1,
            'thumbnail_path' => 'projects/nova-commerce/thumbnail.jpg',
            'meta_title' => 'Nova Commerce Platform Case Study',
            'meta_description' => 'A full-stack Laravel and React commerce platform with measurable conversion improvements.',
        ]);

        $commerceProject->details()->createMany([
            [
                'field_name' => 'client',
                'field_value' => ['label' => 'Client', 'value' => 'Nova Retail Group'],
            ],
            [
                'field_name' => 'duration',
                'field_value' => ['label' => 'Duration', 'value' => '14 weeks'],
            ],
            [
                'field_name' => 'stack',
                'field_value' => ['label' => 'Stack', 'value' => ['Laravel 12', 'Inertia', 'React']],
            ],
        ]);

        $commerceProject->images()->createMany([
            [
                'image_path' => 'projects/nova-commerce/cover.jpg',
                'alt_text' => 'Nova Commerce dashboard overview',
                'sort_order' => 0,
                'type' => 'thumbnail',
            ],
            [
                'image_path' => 'projects/nova-commerce/gallery-1.jpg',
                'alt_text' => 'Vendor analytics dashboard',
                'sort_order' => 1,
                'type' => 'gallery',
            ],
            [
                'image_path' => 'projects/nova-commerce/wireframe-1.jpg',
                'alt_text' => 'Checkout flow wireframe',
                'sort_order' => 2,
                'type' => 'wireframe',
            ],
        ]);

        $financeProject = Project::query()->create([
            'title' => 'Pulse Finance App Redesign',
            'slug' => 'pulse-finance-app-redesign',
            'type' => 'uiux',
            'summary' => 'A UX redesign for a personal finance mobile app focused on onboarding clarity and retention.',
            'featured' => false,
            'sort_order' => 2,
            'thumbnail_path' => 'projects/pulse-finance/thumbnail.jpg',
            'meta_title' => 'Pulse Finance UX Redesign',
            'meta_description' => 'UI/UX redesign project that improved onboarding completion and monthly active usage.',
        ]);

        $financeProject->details()->createMany([
            [
                'field_name' => 'challenge',
                'field_value' => ['label' => 'Challenge', 'value' => 'High drop-off during onboarding'],
            ],
            [
                'field_name' => 'outcome',
                'field_value' => ['label' => 'Outcome', 'value' => 'Onboarding completion increased by 32%'],
            ],
        ]);

        $financeProject->images()->createMany([
            [
                'image_path' => 'projects/pulse-finance/cover.jpg',
                'alt_text' => 'Pulse Finance app preview',
                'sort_order' => 0,
                'type' => 'thumbnail',
            ],
            [
                'image_path' => 'projects/pulse-finance/screen-1.jpg',
                'alt_text' => 'Savings goal screen',
                'sort_order' => 1,
                'type' => 'screenshot',
            ],
        ]);

        SiteSetting::query()->create([
            'key' => 'hero_content',
            'group' => 'hero',
            'value' => [
                'headline' => 'Building thoughtful digital products',
                'subheadline' => 'Laravel developer and product designer crafting performant portfolio-grade experiences.',
                'cta_label' => 'View Projects',
            ],
        ]);

        SiteSetting::query()->create([
            'key' => 'contact_information',
            'group' => 'contact',
            'value' => [
                'email' => 'hello@portfolio.test',
                'location' => 'Cairo, Egypt',
                'linkedin' => 'https://linkedin.com/in/portfolio',
            ],
        ]);

        ContactSubmission::query()->create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@example.com',
            'subject' => 'Project Collaboration',
            'message' => 'I would like to discuss redesigning our product dashboard. Are you available this month?',
            'read' => false,
            'ip_address' => '203.0.113.24',
        ]);

        ContactSubmission::query()->create([
            'name' => 'Mohamed Ali',
            'email' => 'mohamed@example.com',
            'subject' => null,
            'message' => 'Great work on your portfolio. Can you share your availability for freelance backend work?',
            'read' => true,
            'ip_address' => '203.0.113.41',
        ]);

        AnalyticsEvent::query()->create([
            'event_type' => 'page_view',
            'event_data' => ['section' => 'home'],
            'page_url' => '/',
            'referrer' => 'https://google.com',
            'device_type' => 'desktop',
            'country' => 'EG',
            'ip_address' => '203.0.113.77',
            'session_id' => 'sess_home_001',
        ]);

        AnalyticsEvent::query()->create([
            'event_type' => 'project_view',
            'event_data' => ['project_slug' => 'nova-commerce-platform'],
            'page_url' => '/projects/nova-commerce-platform',
            'referrer' => '/',
            'device_type' => 'mobile',
            'country' => 'EG',
            'ip_address' => '203.0.113.88',
            'session_id' => 'sess_project_014',
        ]);
    }
}
