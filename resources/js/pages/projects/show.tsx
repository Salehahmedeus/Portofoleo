import { Link } from '@inertiajs/react';
import { ArrowLeftIcon, ArrowRightIcon, ArrowUpRightIcon } from 'lucide-react';
import { Container } from '@/components/layout/container';
import { Section } from '@/components/layout/section';
import { LightboxGallery } from '@/components/lightbox-gallery';
import { SeoHead } from '@/components/seo-head';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import type {
    AdjacentProjectLink,
    Project,
    ProjectDetail,
    SettingValue,
} from '@/types';

type ProjectShowProps = {
    project: unknown;
    previous_project?: AdjacentProjectLink | null;
    next_project?: AdjacentProjectLink | null;
};

const detailLabels: Record<string, string> = {
    challenge: 'Challenge',
    solution: 'Solution',
    architecture: 'Architecture',
    implementation: 'Implementation',
    result: 'Result',
    outcome: 'Outcome',
    problem: 'Problem',
    research: 'Research',
    wireframes: 'Wireframes',
    design_system: 'Design System',
    testing: 'Testing',
    stack: 'Stack',
    role: 'Role',
    duration: 'Duration',
    client: 'Client',
};

const templateSections: Record<Project['type'], string[]> = {
    development: [
        'challenge',
        'solution',
        'architecture',
        'implementation',
        'result',
        'outcome',
    ],
    uiux: [
        'problem',
        'research',
        'wireframes',
        'design_system',
        'prototype',
        'testing',
        'outcome',
    ],
};

function isProject(value: unknown): value is Project {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const project = value as Partial<Project>;

    return (
        typeof project.id === 'number' &&
        typeof project.title === 'string' &&
        typeof project.slug === 'string' &&
        typeof project.summary === 'string' &&
        (project.type === 'development' || project.type === 'uiux')
    );
}

function getDetail(project: Project, key: string): ProjectDetail | undefined {
    return project.details?.[key];
}

function resolveDetailValue(value: SettingValue): SettingValue {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        const objectValue = value as Record<string, SettingValue>;

        if ('value' in objectValue) {
            return objectValue.value;
        }
    }

    return value;
}

function renderDetailContent(value: SettingValue): string {
    const normalizedValue = resolveDetailValue(value);

    if (
        typeof normalizedValue === 'string' ||
        typeof normalizedValue === 'number' ||
        typeof normalizedValue === 'boolean'
    ) {
        return String(normalizedValue);
    }

    if (Array.isArray(normalizedValue)) {
        return normalizedValue
            .map((item) => {
                if (
                    typeof item === 'string' ||
                    typeof item === 'number' ||
                    typeof item === 'boolean'
                ) {
                    return String(item);
                }

                return '';
            })
            .filter(Boolean)
            .join(', ');
    }

    if (normalizedValue && typeof normalizedValue === 'object') {
        return JSON.stringify(normalizedValue, null, 2);
    }

    return '';
}

function getActionLink(project: Project, keys: string[]): string | undefined {
    for (const key of keys) {
        const detail = getDetail(project, key);

        if (!detail) {
            continue;
        }

        const value = renderDetailContent(detail.field_value);

        if (value.startsWith('http://') || value.startsWith('https://')) {
            return value;
        }
    }

    return undefined;
}

function isAdjacentProjectLink(value: unknown): value is AdjacentProjectLink {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const link = value as Partial<AdjacentProjectLink>;

    return typeof link.slug === 'string' && typeof link.title === 'string';
}

export default function ProjectsShow({
    project,
    previous_project,
    next_project,
}: ProjectShowProps) {
    if (!isProject(project)) {
        return (
            <Section>
                <SeoHead
                    title="Project"
                    description="Project details could not be displayed."
                />
                <Container className="space-y-4">
                    <h1 className="font-display text-3xl font-semibold">
                        Project
                    </h1>
                    <div className="rounded-xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
                        Invalid project payload received.
                    </div>
                    <Link
                        href="/projects"
                        className="inline-flex items-center gap-2 rounded-sm text-sm font-medium underline-offset-4 hover:underline focus-visible:ring-2 focus-visible:ring-portfolio-accent focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none"
                    >
                        <ArrowLeftIcon className="size-4" />
                        Back to projects
                    </Link>
                </Container>
            </Section>
        );
    }

    const isUiUx = project.type === 'uiux';
    const badgeLabel = isUiUx ? 'UI/UX' : 'Development';

    const liveDemoUrl = getActionLink(project, [
        'live_demo',
        'live_demo_url',
        'live_url',
    ]);
    const githubUrl = getActionLink(project, [
        'github',
        'github_url',
        'repository_url',
    ]);
    const prototypeUrl = getActionLink(project, ['prototype', 'prototype_url']);

    const metadataKeys = ['client', 'role', 'duration', 'stack'];
    const contentKeys = templateSections[project.type];
    const previousProject = isAdjacentProjectLink(previous_project)
        ? previous_project
        : null;
    const nextProject = isAdjacentProjectLink(next_project)
        ? next_project
        : null;

    const trackOutboundClick = (target: string, url: string): void => {
        void postJson<{ id: number; message: string }>('/analytics-events', {
            event_type: 'outbound_click',
            event_data: {
                target,
                url,
                project_slug: project.slug,
            },
            page_url:
                typeof window !== 'undefined'
                    ? window.location.href
                    : `/projects/${project.slug}`,
            referrer:
                typeof document !== 'undefined' ? document.referrer : undefined,
        }).catch(() => {
            return null;
        });
    };

    return (
        <Section>
            <SeoHead
                title={project.meta_title ?? project.title}
                description={project.meta_description ?? project.summary}
                image={project.thumbnail_url}
                url={`/projects/${project.slug}`}
            />

            <Container className="space-y-10">
                <div className="space-y-5">
                    <Link
                        href="/projects"
                        className="inline-flex items-center gap-2 rounded-sm text-sm font-medium text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-portfolio-accent focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none"
                    >
                        <ArrowLeftIcon className="size-4" />
                        Back to projects
                    </Link>

                    <div className="space-y-3">
                        <Badge variant="outline">{badgeLabel}</Badge>
                        <h1 className="font-display text-4xl font-semibold text-balance sm:text-5xl">
                            {project.title}
                        </h1>
                        <p className="max-w-3xl text-base text-muted-foreground sm:text-lg">
                            {project.summary}
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {liveDemoUrl ? (
                            <Button asChild>
                                <a
                                    href={liveDemoUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    onClick={() => {
                                        trackOutboundClick(
                                            'live_demo',
                                            liveDemoUrl,
                                        );
                                    }}
                                >
                                    Live demo
                                    <ArrowUpRightIcon className="size-4" />
                                </a>
                            </Button>
                        ) : null}

                        {githubUrl ? (
                            <Button asChild variant="outline">
                                <a
                                    href={githubUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    onClick={() => {
                                        trackOutboundClick('github', githubUrl);
                                    }}
                                >
                                    GitHub
                                    <ArrowUpRightIcon className="size-4" />
                                </a>
                            </Button>
                        ) : null}

                        {prototypeUrl ? (
                            <Button asChild variant="outline">
                                <a
                                    href={prototypeUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    onClick={() => {
                                        trackOutboundClick(
                                            'prototype',
                                            prototypeUrl,
                                        );
                                    }}
                                >
                                    Prototype
                                    <ArrowUpRightIcon className="size-4" />
                                </a>
                            </Button>
                        ) : null}
                    </div>
                </div>

                <div className="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        {contentKeys.map((key) => {
                            const detail = getDetail(project, key);

                            if (!detail) {
                                return null;
                            }

                            const content = renderDetailContent(
                                detail.field_value,
                            );

                            if (!content) {
                                return null;
                            }

                            return (
                                <article
                                    key={key}
                                    className="rounded-2xl border bg-card p-6"
                                >
                                    <h2 className="mb-3 font-display text-2xl font-semibold">
                                        {detailLabels[key] ?? key}
                                    </h2>
                                    <p className="text-sm leading-relaxed whitespace-pre-wrap text-muted-foreground">
                                        {content}
                                    </p>
                                </article>
                            );
                        })}

                        <div className="space-y-3">
                            <h2 className="font-display text-2xl font-semibold">
                                Gallery
                            </h2>
                            <LightboxGallery
                                images={project.images ?? []}
                                title={project.title}
                            />
                        </div>
                    </div>

                    <aside className="h-max rounded-2xl border bg-card p-6">
                        <h2 className="mb-4 font-display text-2xl font-semibold">
                            Project metadata
                        </h2>
                        <dl className="space-y-3 text-sm">
                            <div className="flex items-start justify-between gap-3">
                                <dt className="text-muted-foreground">Type</dt>
                                <dd>{badgeLabel}</dd>
                            </div>
                            <div className="flex items-start justify-between gap-3">
                                <dt className="text-muted-foreground">
                                    Featured
                                </dt>
                                <dd>{project.featured ? 'Yes' : 'No'}</dd>
                            </div>
                            <div className="flex items-start justify-between gap-3">
                                <dt className="text-muted-foreground">
                                    Sort order
                                </dt>
                                <dd>{project.sort_order}</dd>
                            </div>

                            {metadataKeys.map((key) => {
                                const detail = getDetail(project, key);

                                if (!detail) {
                                    return null;
                                }

                                const value = renderDetailContent(
                                    detail.field_value,
                                );

                                if (!value) {
                                    return null;
                                }

                                return (
                                    <div
                                        key={key}
                                        className="flex items-start justify-between gap-3"
                                    >
                                        <dt className="text-muted-foreground">
                                            {detailLabels[key] ?? key}
                                        </dt>
                                        <dd className="text-right">{value}</dd>
                                    </div>
                                );
                            })}
                        </dl>
                    </aside>
                </div>

                {previousProject || nextProject ? (
                    <div className="grid gap-3 sm:grid-cols-2">
                        {previousProject ? (
                            <Link
                                href={`/projects/${previousProject.slug}`}
                                className="group rounded-2xl border bg-card p-4 text-left transition-colors hover:border-portfolio-accent/40 focus-visible:ring-2 focus-visible:ring-portfolio-accent focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none"
                            >
                                <p className="mb-1 inline-flex items-center gap-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    <ArrowLeftIcon className="size-4" />
                                    Previous project
                                </p>
                                <p className="font-medium text-foreground group-hover:text-portfolio-accent-strong">
                                    {previousProject.title}
                                </p>
                            </Link>
                        ) : (
                            <div />
                        )}

                        {nextProject ? (
                            <Link
                                href={`/projects/${nextProject.slug}`}
                                className="group rounded-2xl border bg-card p-4 text-right transition-colors hover:border-portfolio-accent/40 focus-visible:ring-2 focus-visible:ring-portfolio-accent focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none"
                            >
                                <p className="mb-1 inline-flex items-center gap-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Next project
                                    <ArrowRightIcon className="size-4" />
                                </p>
                                <p className="font-medium text-foreground group-hover:text-portfolio-accent-strong">
                                    {nextProject.title}
                                </p>
                            </Link>
                        ) : null}
                    </div>
                ) : null}

                <div className="rounded-2xl border bg-portfolio-gradient p-8 text-center">
                    <h2 className="font-display text-2xl font-semibold">
                        Have a similar project in mind?
                    </h2>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Let&apos;s discuss your goals and build something great
                        together.
                    </p>
                    <Button asChild className="mt-5">
                        <a href="/#contact">Contact me</a>
                    </Button>
                </div>
            </Container>
        </Section>
    );
}
