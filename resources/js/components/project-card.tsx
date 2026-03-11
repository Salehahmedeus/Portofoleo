import { Link } from '@inertiajs/react';
import { ArrowUpRightIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Project, ProjectDetail, SettingValue } from '@/types';

type ProjectCardProps = {
    project: Project;
    className?: string;
    onOutboundClick?: (url: string, project: Project) => void;
};

function getDetailString(detail?: ProjectDetail): string | undefined {
    if (!detail) {
        return undefined;
    }

    const value = detail.field_value;

    if (typeof value === 'string' && value.length > 0) {
        return value;
    }

    if (value && typeof value === 'object' && !Array.isArray(value)) {
        const valueField = (value as Record<string, SettingValue>).value;

        if (typeof valueField === 'string' && valueField.length > 0) {
            return valueField;
        }
    }

    return undefined;
}

function getExternalCta(project: Project): string | undefined {
    const details = project.details;

    if (!details) {
        return undefined;
    }

    const candidates = [
        'live_demo',
        'live_demo_url',
        'live_url',
        'website_url',
        'prototype_url',
    ];

    for (const key of candidates) {
        const value = getDetailString(details[key]);

        if (value?.startsWith('http://') || value?.startsWith('https://')) {
            return value;
        }
    }

    return undefined;
}

export function ProjectCard({
    project,
    className,
    onOutboundClick,
}: ProjectCardProps) {
    const projectType = project.type === 'uiux' ? 'UI/UX' : 'Development';
    const projectUrl = `/projects/${project.slug}`;
    const externalCtaUrl = getExternalCta(project);

    return (
        <article
            className={cn(
                'group overflow-hidden rounded-2xl border bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-lg',
                className,
            )}
        >
            <Link href={projectUrl} className="block">
                <div className="aspect-[16/10] overflow-hidden bg-muted">
                    {project.thumbnail_url ? (
                        <img
                            src={project.thumbnail_url}
                            alt={project.title}
                            loading="lazy"
                            className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center bg-portfolio-gradient text-sm text-muted-foreground">
                            Preview unavailable
                        </div>
                    )}
                </div>
            </Link>

            <div className="space-y-4 p-5">
                <div className="flex items-center justify-between gap-3">
                    <Badge variant="outline">{projectType}</Badge>
                    {project.featured ? (
                        <span className="text-xs font-medium text-muted-foreground">
                            Featured
                        </span>
                    ) : null}
                </div>

                <div className="space-y-2">
                    <h3 className="font-display text-xl font-semibold tracking-tight">
                        {project.title}
                    </h3>
                    <p className="line-clamp-3 text-sm text-muted-foreground">
                        {project.summary}
                    </p>
                </div>

                <div className="flex items-center justify-between gap-2">
                    <Link
                        href={projectUrl}
                        className="text-sm font-medium text-portfolio-accent-strong underline-offset-4 hover:underline"
                    >
                        View details
                    </Link>

                    {externalCtaUrl ? (
                        <Button
                            asChild
                            size="sm"
                            variant="outline"
                            className="h-8"
                        >
                            <a
                                href={externalCtaUrl}
                                target="_blank"
                                rel="noreferrer"
                                onClick={() => {
                                    onOutboundClick?.(externalCtaUrl, project);
                                }}
                            >
                                Visit
                                <ArrowUpRightIcon className="size-4" />
                            </a>
                        </Button>
                    ) : null}
                </div>
            </div>
        </article>
    );
}
