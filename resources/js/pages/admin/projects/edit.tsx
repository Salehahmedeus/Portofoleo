import { Head } from '@inertiajs/react';
import { ProjectForm } from '@/components/admin/project-form';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Project } from '@/types';

type AdminProjectEditProps = {
    project: Project;
};

export default function AdminProjectEdit({ project }: AdminProjectEditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Projects',
            href: '/admin/projects',
        },
        {
            title: project.title,
            href: `/admin/projects/${project.slug}/edit`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${project.title}`} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="rounded-xl border bg-card p-4">
                    <h1 className="text-xl font-semibold">Edit project</h1>
                    <p className="text-sm text-muted-foreground">
                        Update content, media, and metadata for this project.
                    </p>
                </div>

                <div className="rounded-xl border bg-card p-4">
                    <ProjectForm
                        project={project}
                        method="put"
                        submitUrl={`/admin/projects/${project.slug}`}
                        submitLabel="Update project"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
