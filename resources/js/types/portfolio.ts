export type SettingValue =
    | string
    | number
    | boolean
    | null
    | { [key: string]: SettingValue }
    | SettingValue[];

export type SiteSettings = Record<string, SettingValue>;

export type HeroSettings = {
    headline?: string;
    subheadline?: string;
    cta_label?: string;
    [key: string]: SettingValue | undefined;
};

export type ContactSettings = {
    email?: string;
    location?: string;
    whatsapp?: string;
    linkedin?: string;
    github?: string;
    [key: string]: SettingValue | undefined;
};

export type ProjectType = 'development' | 'uiux';

export type ProjectDetail = {
    field_name: string;
    field_value: SettingValue;
};

export type ProjectDetailsMap = Record<string, ProjectDetail>;

export type ProjectImage = {
    id: number;
    image_path: string;
    image_url?: string;
    alt_text?: string;
    sort_order?: number;
    type?: string;
    created_at?: string;
    updated_at?: string;
};

export type Project = {
    id: number;
    title: string;
    slug: string;
    type: ProjectType;
    summary: string;
    featured: boolean;
    sort_order: number;
    thumbnail_path?: string;
    thumbnail_url?: string;
    meta_title?: string;
    meta_description?: string;
    details?: ProjectDetailsMap;
    images?: ProjectImage[];
    created_at?: string;
    updated_at?: string;
};

export type AdjacentProjectLink = {
    slug: string;
    title: string;
};

export type Service = {
    id: number;
    title: string;
    description: string;
    icon?: string;
    sort_order: number;
    created_at?: string;
    updated_at?: string;
};

export type Skill = {
    id: number;
    name: string;
    category: string;
    logo_path?: string;
    sort_order: number;
    created_at?: string;
    updated_at?: string;
};

export type ContactSubmissionPayload = {
    name: string;
    email: string;
    subject?: string;
    message: string;
    company?: string;
};

export type AnalyticsEventPayload = {
    event_type: string;
    event_data?: Record<string, unknown>;
    page_url: string;
    referrer?: string;
    device_type?: 'desktop' | 'mobile' | 'tablet';
    country?: string;
    session_id?: string;
};
