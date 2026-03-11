import { Link } from '@inertiajs/react';
import { Container } from '@/components/layout/container';
import { Section } from '@/components/layout/section';
import { ProjectCard } from '@/components/project-card';
import { SeoHead } from '@/components/seo-head';
import { Skeleton } from '@/components/ui/skeleton';
import type { Project } from '@/types';

type ProjectsIndexProps = {
    projects?: unknown;
};

function isProjectList(value: unknown): value is Project[] {
    if (!Array.isArray(value)) {
        return false;
    }

    return value.every((item) => {
        if (!item || typeof item !== 'object') {
            return false;
        }

        const project = item as Partial<Project>;

        return (
            typeof project.id === 'number' &&
            typeof project.title === 'string' &&
            typeof project.slug === 'string' &&
            typeof project.summary === 'string' &&
            (project.type === 'development' || project.type === 'uiux')
        );
    });
}

export default function ProjectsIndex({ projects }: ProjectsIndexProps) {
    if (projects == null) {
        return (
            <Section>
                <SeoHead
                    title="Projects"
                    description="Browse all portfolio projects and case studies."
                />

                <Container className="space-y-8">
                    <div className="space-y-2">
                        <Skeleton className="h-5 w-28" />
                        <Skeleton className="h-10 w-64" />
                    </div>
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {Array.from({ length: 6 }).map((_, index) => (
                            <div
                                key={`project-loading-${index}`}
                                className="overflow-hidden rounded-2xl border bg-card"
                            >
                                <Skeleton className="aspect-[16/10] rounded-none" />
                                <div className="space-y-3 p-5">
                                    <Skeleton className="h-4 w-20" />
                                    <Skeleton className="h-6 w-3/4" />
                                    <Skeleton className="h-4 w-full" />
                                    <Skeleton className="h-4 w-4/5" />
                                </div>
                            </div>
                        ))}
                    </div>
                </Container>
            </Section>
        );
    }

    if (!isProjectList(projects)) {
        return (
            <Section>
                <SeoHead
                    title="Projects"
                    description="Browse all portfolio projects and case studies."
                />

                <Container className="space-y-4">
                    <h1 className="font-display text-3xl font-semibold">
                        Projects
                    </h1>
                    <div className="rounded-xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
                        Unable to render projects because the incoming data
                        format is invalid.
                    </div>
                    <Link
                        href="/"
                        className="text-sm font-medium underline-offset-4 hover:underline"
                    >
                        Return to home
                    </Link>
                </Container>
            </Section>
        );
    }

    return (
        <Section>
            <SeoHead
                title="Projects"
                description="Browse all portfolio projects and case studies."
            />

            <Container className="space-y-8">
                <div className="space-y-2">
                    <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                        Portfolio
                    </p>
                    <h1 className="font-display text-4xl font-semibold">
                        All Projects
                    </h1>
                </div>

                {projects.length === 0 ? (
                    <div className="rounded-2xl border bg-card p-8 text-center">
                        <h2 className="text-lg font-semibold">
                            No projects yet
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            New case studies will appear here soon.
                        </p>
                        <Link
                            href="/"
                            className="mt-4 inline-block text-sm font-medium underline-offset-4 hover:underline"
                        >
                            Back to home
                        </Link>
                    </div>
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {projects.map((project) => (
                            <ProjectCard key={project.id} project={project} />
                        ))}
                    </div>
                )}
            </Container>
        </Section>
    );
}
