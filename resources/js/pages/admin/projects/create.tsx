import { Head } from '@inertiajs/react';
import { ProjectForm } from '@/components/admin/project-form';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/admin/projects',
    },
    {
        title: 'Create',
        href: '/admin/projects/create',
    },
];

export default function AdminProjectCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create project" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="rounded-xl border bg-card p-4">
                    <h1 className="text-xl font-semibold">Create project</h1>
                    <p className="text-sm text-muted-foreground">
                        Add a new project with details, metadata, and images.
                    </p>
                </div>

                <div className="rounded-xl border bg-card p-4">
                    <ProjectForm
                        method="post"
                        submitUrl="/admin/projects"
                        submitLabel="Create project"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
