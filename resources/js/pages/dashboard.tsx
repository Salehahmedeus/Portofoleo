import { Head, Link } from '@inertiajs/react';
import { BarChart3Icon, FolderKanbanIcon, SquarePenIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="rounded-xl border bg-card p-5">
                    <h1 className="text-xl font-semibold">Admin home</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Quick shortcuts to manage projects, content, and
                        analytics.
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-xl border bg-card p-5">
                        <div className="mb-3 flex items-center gap-2 text-sm text-muted-foreground">
                            <FolderKanbanIcon className="size-4" />
                            Projects
                        </div>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Add, edit, reorder, and feature portfolio projects.
                        </p>
                        <Button asChild variant="outline" size="sm">
                            <Link href="/admin/projects">Manage projects</Link>
                        </Button>
                    </div>

                    <div className="rounded-xl border bg-card p-5">
                        <div className="mb-3 flex items-center gap-2 text-sm text-muted-foreground">
                            <SquarePenIcon className="size-4" />
                            Content
                        </div>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Update hero text, contact details, and homepage SEO.
                        </p>
                        <Button asChild variant="outline" size="sm">
                            <Link href="/admin/content">Edit content</Link>
                        </Button>
                    </div>

                    <div className="rounded-xl border bg-card p-5">
                        <div className="mb-3 flex items-center gap-2 text-sm text-muted-foreground">
                            <BarChart3Icon className="size-4" />
                            Analytics
                        </div>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Review activity trends and top tracked events.
                        </p>
                        <Button asChild variant="outline" size="sm">
                            <Link href="/admin/analytics">View analytics</Link>
                        </Button>
                    </div>
                </div>

                <div className="rounded-xl border bg-card p-5">
                    <h2 className="text-base font-semibold">Quick actions</h2>
                    <div className="mt-3 flex flex-wrap gap-2">
                        <Button asChild>
                            <Link href="/admin/projects/create">
                                Add new project
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href="/settings/profile">Open settings</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
