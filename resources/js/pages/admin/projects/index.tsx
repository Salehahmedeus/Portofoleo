import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowDownIcon,
    ArrowUpIcon,
    PencilIcon,
    PlusIcon,
    StarIcon,
    Trash2Icon,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Project } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type ProjectsPaginator = {
    data: Project[];
    links: PaginationLink[];
};

type ProjectsPageProps = {
    projects: ProjectsPaginator;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/admin/projects',
    },
];

function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

function formatDate(value: string | undefined): string {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
    }).format(date);
}

function readableLabel(label: string): string {
    return label
        .replaceAll('&laquo;', '<<')
        .replaceAll('&raquo;', '>>')
        .replaceAll('&amp;', '&');
}

export default function AdminProjectsIndex({ projects }: ProjectsPageProps) {
    const { flash } = usePage().props;
    const [rows, setRows] = useState<Project[]>(projects.data);
    const [reorderMessage, setReorderMessage] = useState<string | null>(null);
    const [reorderError, setReorderError] = useState<string | null>(null);
    const [isSavingOrder, setIsSavingOrder] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<Project | null>(null);

    useEffect(() => {
        setRows(projects.data);
    }, [projects.data]);

    const rowIdSignature = useMemo(
        () => rows.map((row) => row.id).join(','),
        [rows],
    );
    const initialIdSignature = useMemo(
        () => projects.data.map((row) => row.id).join(','),
        [projects.data],
    );
    const orderHasChanges = rowIdSignature !== initialIdSignature;

    const moveRow = (index: number, direction: 'up' | 'down'): void => {
        const toIndex = direction === 'up' ? index - 1 : index + 1;

        if (toIndex < 0 || toIndex >= rows.length) {
            return;
        }

        setRows((currentRows) => {
            const nextRows = [...currentRows];
            const current = nextRows[index];

            nextRows[index] = nextRows[toIndex];
            nextRows[toIndex] = current;

            return nextRows;
        });
        setReorderMessage(null);
    };

    const toggleFeatured = (project: Project): void => {
        setRows((currentRows) =>
            currentRows.map((row) =>
                row.id === project.id
                    ? { ...row, featured: !row.featured }
                    : row,
            ),
        );

        router.patch(
            `/admin/projects/${project.slug}/featured`,
            {
                featured: !project.featured,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    setRows((currentRows) =>
                        currentRows.map((row) =>
                            row.id === project.id
                                ? { ...row, featured: project.featured }
                                : row,
                        ),
                    );
                    setReorderError('Could not update featured state.');
                },
            },
        );
    };

    const saveOrder = async (): Promise<void> => {
        if (rows.length === 0) {
            return;
        }

        setIsSavingOrder(true);
        setReorderError(null);
        setReorderMessage(null);

        try {
            const csrfToken = getCsrfToken();
            const response = await fetch('/admin/projects/reorder', {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({
                    projects: rows.map((row) => row.id),
                }),
            });

            if (!response.ok) {
                throw new Error('Unable to save order right now.');
            }

            setReorderMessage('Order saved successfully.');
            router.reload({ only: ['projects'] });
        } catch {
            setReorderError('Unable to save project order right now.');
        } finally {
            setIsSavingOrder(false);
        }
    };

    const deleteProject = (): void => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/projects/${deleteTarget.slug}`, {
            preserveScroll: true,
            onSuccess: () => setDeleteTarget(null),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-card p-4">
                    <div>
                        <h1 className="text-xl font-semibold">Projects</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage project entries, featured state, and
                            ordering.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href="/admin/projects/create">
                            <PlusIcon className="size-4" />
                            Add project
                        </Link>
                    </Button>
                </div>

                {flash?.success ? (
                    <Alert>
                        <AlertTitle>Success</AlertTitle>
                        <AlertDescription>{flash?.success}</AlertDescription>
                    </Alert>
                ) : null}

                {flash?.error ? (
                    <Alert variant="destructive">
                        <AlertTitle>Error</AlertTitle>
                        <AlertDescription>{flash?.error}</AlertDescription>
                    </Alert>
                ) : null}

                {reorderMessage ? (
                    <Alert>
                        <AlertTitle>Saved</AlertTitle>
                        <AlertDescription>{reorderMessage}</AlertDescription>
                    </Alert>
                ) : null}

                {reorderError ? (
                    <Alert variant="destructive">
                        <AlertTitle>Action failed</AlertTitle>
                        <AlertDescription>{reorderError}</AlertDescription>
                    </Alert>
                ) : null}

                {rows.length === 0 ? (
                    <div className="rounded-xl border border-dashed bg-card p-10 text-center">
                        <h2 className="text-lg font-semibold">
                            No projects yet
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Create your first project to start filling the
                            portfolio.
                        </p>
                        <Button asChild className="mt-4">
                            <Link href="/admin/projects/create">
                                Create project
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <>
                        <div className="overflow-hidden rounded-xl border bg-card">
                            <div className="overflow-x-auto">
                                <table className="w-full table-auto text-sm">
                                    <thead className="bg-muted/50 text-left text-xs uppercase">
                                        <tr>
                                            <th className="px-4 py-3">Title</th>
                                            <th className="px-4 py-3">Type</th>
                                            <th className="px-4 py-3">
                                                Featured
                                            </th>
                                            <th className="px-4 py-3">
                                                Last updated
                                            </th>
                                            <th className="px-4 py-3">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.map((project, index) => (
                                            <tr
                                                key={project.id}
                                                className="border-t align-top"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    {project.title}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Badge variant="outline">
                                                        {project.type === 'uiux'
                                                            ? 'UI/UX'
                                                            : 'Development'}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        variant={
                                                            project.featured
                                                                ? 'default'
                                                                : 'outline'
                                                        }
                                                        onClick={() =>
                                                            toggleFeatured(
                                                                project,
                                                            )
                                                        }
                                                    >
                                                        <StarIcon className="size-4" />
                                                        {project.featured
                                                            ? 'Featured'
                                                            : 'Not featured'}
                                                    </Button>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {formatDate(
                                                        project.updated_at,
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-wrap gap-2">
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() =>
                                                                moveRow(
                                                                    index,
                                                                    'up',
                                                                )
                                                            }
                                                            disabled={
                                                                index === 0
                                                            }
                                                        >
                                                            <ArrowUpIcon className="size-4" />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() =>
                                                                moveRow(
                                                                    index,
                                                                    'down',
                                                                )
                                                            }
                                                            disabled={
                                                                index ===
                                                                rows.length - 1
                                                            }
                                                        >
                                                            <ArrowDownIcon className="size-4" />
                                                        </Button>
                                                        <Button
                                                            asChild
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                        >
                                                            <Link
                                                                href={`/admin/projects/${project.slug}/edit`}
                                                            >
                                                                <PencilIcon className="size-4" />
                                                                Edit
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() =>
                                                                setDeleteTarget(
                                                                    project,
                                                                )
                                                            }
                                                        >
                                                            <Trash2Icon className="size-4" />
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex flex-wrap gap-2">
                                {projects.links.map((link, index) => {
                                    if (!link.url) {
                                        return (
                                            <Button
                                                key={`${link.label}-${index}`}
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                disabled
                                            >
                                                {readableLabel(link.label)}
                                            </Button>
                                        );
                                    }

                                    return (
                                        <Button
                                            key={`${link.label}-${index}`}
                                            type="button"
                                            variant={
                                                link.active
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            size="sm"
                                            asChild
                                        >
                                            <Link href={link.url}>
                                                {readableLabel(link.label)}
                                            </Link>
                                        </Button>
                                    );
                                })}
                            </div>

                            <Button
                                type="button"
                                onClick={() => {
                                    void saveOrder();
                                }}
                                disabled={!orderHasChanges || isSavingOrder}
                            >
                                {isSavingOrder ? 'Saving...' : 'Save order'}
                            </Button>
                        </div>
                    </>
                )}
            </div>

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(isOpen) => {
                    if (!isOpen) {
                        setDeleteTarget(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete project?</DialogTitle>
                        <DialogDescription>
                            This action permanently removes
                            {deleteTarget ? ` "${deleteTarget.title}"` : ''}.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setDeleteTarget(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={deleteProject}
                        >
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
