import { useForm } from '@inertiajs/react';
import { Trash2Icon } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type {
    Project,
    ProjectDetail,
    ProjectImage,
    ProjectType,
} from '@/types';

type DetailRow = {
    id: string;
    field_name: string;
    label: string;
    value: string;
};

type NewImageRow = {
    id: string;
    file: File | null;
    alt_text: string;
    type: 'gallery' | 'wireframe' | 'screenshot' | 'thumbnail';
    sort_order: string;
    preview_url: string | null;
};

type ExistingImageRow = {
    id: number;
    image_url: string;
    alt_text: string;
    type: string;
    sort_order: number;
};

type ProjectFormData = {
    title: string;
    slug: string;
    type: ProjectType;
    summary: string;
    featured: boolean;
    sort_order: string;
    thumbnail: File | null;
    remove_thumbnail: boolean;
    meta_title: string;
    meta_description: string;
    details: Array<{
        field_name: string;
        field_value: {
            label: string;
            value: string;
        };
    }>;
    images: Array<{
        file: File;
        alt_text: string;
        type: string;
        sort_order: number;
    }>;
};

type ProjectFormProps = {
    project?: Project;
    method: 'post' | 'put';
    submitUrl: string;
    submitLabel?: string;
};

const urlFieldHints = new Set(['live_demo', 'github', 'prototype']);

function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function firstImageFile(files: FileList | null): File | null {
    if (!files || files.length === 0) {
        return null;
    }

    const imageFile = Array.from(files).find((file) =>
        file.type.startsWith('image/'),
    );

    return imageFile ?? null;
}

function toDetailRows(details: Project['details']): DetailRow[] {
    if (!details) {
        return [
            {
                id: crypto.randomUUID(),
                field_name: '',
                label: '',
                value: '',
            },
        ];
    }

    const rows = Object.values(details).map((detail: ProjectDetail) => {
        const fieldValue = detail.field_value;
        const normalized =
            fieldValue &&
            typeof fieldValue === 'object' &&
            !Array.isArray(fieldValue)
                ? (fieldValue as { label?: unknown; value?: unknown })
                : null;

        return {
            id: crypto.randomUUID(),
            field_name: detail.field_name,
            label:
                typeof normalized?.label === 'string' ? normalized.label : '',
            value:
                typeof normalized?.value === 'string'
                    ? normalized.value
                    : typeof fieldValue === 'string'
                      ? fieldValue
                      : '',
        };
    });

    return rows.length > 0
        ? rows
        : [
              {
                  id: crypto.randomUUID(),
                  field_name: '',
                  label: '',
                  value: '',
              },
          ];
}

function toExistingImages(
    images: ProjectImage[] | undefined,
): ExistingImageRow[] {
    if (!images) {
        return [];
    }

    return images
        .filter(
            (image) =>
                typeof image.id === 'number' &&
                typeof image.image_url === 'string',
        )
        .map((image) => ({
            id: image.id,
            image_url: image.image_url ?? '',
            alt_text: image.alt_text ?? '',
            type: image.type ?? 'gallery',
            sort_order: image.sort_order ?? 0,
        }));
}

function isValidUrl(value: string): boolean {
    try {
        const parsedUrl = new URL(value);

        return (
            parsedUrl.protocol === 'http:' || parsedUrl.protocol === 'https:'
        );
    } catch {
        return false;
    }
}

export function ProjectForm({
    project,
    method,
    submitUrl,
    submitLabel = 'Save project',
}: ProjectFormProps) {
    const [slugTouched, setSlugTouched] = useState<boolean>(Boolean(project));
    const [detailRows, setDetailRows] = useState<DetailRow[]>(() =>
        toDetailRows(project?.details),
    );
    const [newImageRows, setNewImageRows] = useState<NewImageRow[]>([]);
    const [existingImages, setExistingImages] = useState<ExistingImageRow[]>(
        () => toExistingImages(project?.images),
    );
    const [isDeletingImage, setIsDeletingImage] = useState<
        Record<number, boolean>
    >({});
    const [imageDeleteError, setImageDeleteError] = useState<string | null>(
        null,
    );
    const [isThumbnailDragActive, setIsThumbnailDragActive] =
        useState<boolean>(false);
    const [imageDropActiveRows, setImageDropActiveRows] = useState<
        Record<string, boolean>
    >({});
    const thumbnailInputRef = useRef<HTMLInputElement | null>(null);

    const form = useForm<ProjectFormData>({
        title: project?.title ?? '',
        slug: project?.slug ?? '',
        type: project?.type ?? 'development',
        summary: project?.summary ?? '',
        featured: project?.featured ?? false,
        sort_order:
            typeof project?.sort_order === 'number'
                ? String(project.sort_order)
                : '0',
        thumbnail: null,
        remove_thumbnail: false,
        meta_title: project?.meta_title ?? '',
        meta_description: project?.meta_description ?? '',
        details: [],
        images: [],
    });

    useEffect(() => {
        return () => {
            for (const row of newImageRows) {
                if (row.preview_url) {
                    URL.revokeObjectURL(row.preview_url);
                }
            }
        };
    }, [newImageRows]);

    const detailUrlWarnings = useMemo(() => {
        return detailRows
            .map((row, index) => {
                const key = row.field_name.trim().toLowerCase();
                const value = row.value.trim();

                if (
                    !urlFieldHints.has(key) ||
                    value === '' ||
                    isValidUrl(value)
                ) {
                    return null;
                }

                return {
                    index,
                    message: `"${row.field_name}" usually expects a full URL starting with https://`,
                };
            })
            .filter(
                (warning): warning is { index: number; message: string } =>
                    warning !== null,
            );
    }, [detailRows]);

    const hasThumbnail = Boolean(project?.thumbnail_url);

    const updateThumbnail = (file: File | null): void => {
        form.setData('thumbnail', file);

        if (file) {
            form.setData('remove_thumbnail', false);
        }
    };

    const handleTitleChange = (value: string): void => {
        form.setData('title', value);

        if (!slugTouched) {
            form.setData('slug', slugify(value));
        }
    };

    const addDetailRow = (): void => {
        setDetailRows((currentRows) => [
            ...currentRows,
            {
                id: crypto.randomUUID(),
                field_name: '',
                label: '',
                value: '',
            },
        ]);
    };

    const updateDetailRow = (
        rowId: string,
        field: keyof Omit<DetailRow, 'id'>,
        value: string,
    ): void => {
        setDetailRows((currentRows) =>
            currentRows.map((row) =>
                row.id === rowId
                    ? {
                          ...row,
                          [field]: value,
                      }
                    : row,
            ),
        );
    };

    const removeDetailRow = (rowId: string): void => {
        setDetailRows((currentRows) => {
            const nextRows = currentRows.filter((row) => row.id !== rowId);

            return nextRows.length > 0
                ? nextRows
                : [
                      {
                          id: crypto.randomUUID(),
                          field_name: '',
                          label: '',
                          value: '',
                      },
                  ];
        });
    };

    const addImageRow = (): void => {
        setNewImageRows((currentRows) => [
            ...currentRows,
            {
                id: crypto.randomUUID(),
                file: null,
                alt_text: '',
                type: 'gallery',
                sort_order: String(currentRows.length),
                preview_url: null,
            },
        ]);
    };

    const updateImageRow = (
        rowId: string,
        field: keyof Omit<NewImageRow, 'id'>,
        value: string | File | null,
    ): void => {
        setNewImageRows((currentRows) =>
            currentRows.map((row) => {
                if (row.id !== rowId) {
                    return row;
                }

                if (field === 'file') {
                    const file = value instanceof File ? value : null;
                    const nextPreviewUrl = file
                        ? URL.createObjectURL(file)
                        : null;

                    if (row.preview_url) {
                        URL.revokeObjectURL(row.preview_url);
                    }

                    return {
                        ...row,
                        file,
                        preview_url: nextPreviewUrl,
                    };
                }

                return {
                    ...row,
                    [field]: value,
                };
            }),
        );
    };

    const removeImageRow = (rowId: string): void => {
        setNewImageRows((currentRows) =>
            currentRows.filter((row) => {
                if (row.id !== rowId) {
                    return true;
                }

                if (row.preview_url) {
                    URL.revokeObjectURL(row.preview_url);
                }

                return false;
            }),
        );
    };

    const deleteExistingImage = async (imageId: number): Promise<void> => {
        if (!project) {
            return;
        }

        setImageDeleteError(null);
        setIsDeletingImage((current) => ({ ...current, [imageId]: true }));

        try {
            const csrfToken = getCsrfToken();
            const response = await fetch(
                `/admin/projects/${project.slug}/images/${imageId}`,
                {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                },
            );

            if (!response.ok) {
                throw new Error('Unable to delete image right now.');
            }

            setExistingImages((currentImages) =>
                currentImages.filter((image) => image.id !== imageId),
            );
        } catch {
            setImageDeleteError('Unable to delete image right now.');
        } finally {
            setIsDeletingImage((current) => ({ ...current, [imageId]: false }));
        }
    };

    const submit = (event: React.FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        const payloadDetails = detailRows
            .map((row) => {
                return {
                    field_name: row.field_name.trim(),
                    field_value: {
                        label: row.label.trim(),
                        value: row.value.trim(),
                    },
                };
            })
            .filter(
                (row) => row.field_name !== '' || row.field_value.value !== '',
            );

        const payloadImages = newImageRows
            .filter((row) => row.file !== null)
            .map((row) => ({
                file: row.file as File,
                alt_text: row.alt_text.trim(),
                type: row.type,
                sort_order: Number(row.sort_order) || 0,
            }));

        form.transform((data) => ({
            ...data,
            details: payloadDetails,
            images: payloadImages,
        }));

        form.submit(method, submitUrl, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-8">
            {imageDeleteError ? (
                <Alert variant="destructive">
                    <AlertTitle>Unable to delete image</AlertTitle>
                    <AlertDescription>{imageDeleteError}</AlertDescription>
                </Alert>
            ) : null}

            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2 md:col-span-2">
                    <Label htmlFor="title">Title</Label>
                    <Input
                        id="title"
                        value={form.data.title}
                        onChange={(event) =>
                            handleTitleChange(event.target.value)
                        }
                        placeholder="Project title"
                    />
                    <InputError message={form.errors.title} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input
                        id="slug"
                        value={form.data.slug}
                        onChange={(event) => {
                            setSlugTouched(true);
                            form.setData('slug', slugify(event.target.value));
                        }}
                        placeholder="project-slug"
                    />
                    <InputError message={form.errors.slug} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="type">Type</Label>
                    <Select
                        value={form.data.type}
                        onValueChange={(value: ProjectType) =>
                            form.setData('type', value)
                        }
                    >
                        <SelectTrigger id="type" className="w-full">
                            <SelectValue placeholder="Select a type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="development">
                                Development
                            </SelectItem>
                            <SelectItem value="uiux">UI/UX</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={form.errors.type} />
                </div>

                <div className="space-y-2 md:col-span-2">
                    <Label htmlFor="summary">Summary</Label>
                    <Textarea
                        id="summary"
                        value={form.data.summary}
                        onChange={(event) =>
                            form.setData('summary', event.target.value)
                        }
                        placeholder="Describe this project"
                    />
                    <InputError message={form.errors.summary} />
                </div>

                <div className="flex items-center gap-3">
                    <Checkbox
                        id="featured"
                        checked={form.data.featured}
                        onCheckedChange={(checked) =>
                            form.setData('featured', checked === true)
                        }
                    />
                    <Label htmlFor="featured">Featured project</Label>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input
                        id="sort_order"
                        type="number"
                        min={0}
                        value={form.data.sort_order}
                        onChange={(event) =>
                            form.setData('sort_order', event.target.value)
                        }
                    />
                    <InputError message={form.errors.sort_order} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="thumbnail">Thumbnail</Label>
                    <div
                        className={`rounded-lg border border-dashed p-4 transition-colors ${
                            isThumbnailDragActive
                                ? 'border-primary bg-primary/5'
                                : 'border-border'
                        }`}
                        onDragEnter={(event) => {
                            event.preventDefault();
                            setIsThumbnailDragActive(true);
                        }}
                        onDragOver={(event) => {
                            event.preventDefault();
                        }}
                        onDragLeave={(event) => {
                            event.preventDefault();
                            setIsThumbnailDragActive(false);
                        }}
                        onDrop={(event) => {
                            event.preventDefault();
                            setIsThumbnailDragActive(false);
                            updateThumbnail(
                                firstImageFile(event.dataTransfer.files),
                            );
                        }}
                    >
                        <p className="text-sm text-muted-foreground">
                            Drag and drop an image here, or choose a file.
                        </p>
                        <div className="mt-3 flex items-center gap-2">
                            <Input
                                ref={thumbnailInputRef}
                                id="thumbnail"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                className="hidden"
                                onChange={(event) =>
                                    updateThumbnail(
                                        firstImageFile(event.target.files),
                                    )
                                }
                            />
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    thumbnailInputRef.current?.click()
                                }
                            >
                                Choose thumbnail
                            </Button>
                            {form.data.thumbnail ? (
                                <p className="text-xs text-muted-foreground">
                                    {form.data.thumbnail.name}
                                </p>
                            ) : null}
                        </div>
                    </div>
                    {hasThumbnail ? (
                        <div className="space-y-2 rounded-lg border p-3 text-sm">
                            <p className="text-muted-foreground">
                                Current thumbnail:
                            </p>
                            <img
                                src={project?.thumbnail_url}
                                alt={`${project?.title} thumbnail`}
                                className="h-24 w-40 rounded-md object-cover"
                            />
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="remove_thumbnail"
                                    checked={form.data.remove_thumbnail}
                                    onCheckedChange={(checked) =>
                                        form.setData(
                                            'remove_thumbnail',
                                            checked === true,
                                        )
                                    }
                                />
                                <Label htmlFor="remove_thumbnail">
                                    Remove thumbnail
                                </Label>
                            </div>
                        </div>
                    ) : null}
                    <InputError message={form.errors.thumbnail} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="meta_title">Meta title</Label>
                    <Input
                        id="meta_title"
                        value={form.data.meta_title}
                        onChange={(event) =>
                            form.setData('meta_title', event.target.value)
                        }
                    />
                    <InputError message={form.errors.meta_title} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="meta_description">Meta description</Label>
                    <Textarea
                        id="meta_description"
                        className="min-h-24"
                        value={form.data.meta_description}
                        onChange={(event) =>
                            form.setData('meta_description', event.target.value)
                        }
                    />
                    <InputError message={form.errors.meta_description} />
                </div>
            </div>

            <section className="space-y-4 rounded-xl border p-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Project details</h2>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={addDetailRow}
                    >
                        Add detail row
                    </Button>
                </div>

                <div className="space-y-3">
                    {detailRows.map((row, index) => (
                        <div
                            key={row.id}
                            className="grid gap-3 rounded-lg border p-3 md:grid-cols-[1fr_1fr_1.2fr_auto]"
                        >
                            <Input
                                value={row.field_name}
                                onChange={(event) =>
                                    updateDetailRow(
                                        row.id,
                                        'field_name',
                                        event.target.value,
                                    )
                                }
                                placeholder="field_name"
                            />
                            <Input
                                value={row.label}
                                onChange={(event) =>
                                    updateDetailRow(
                                        row.id,
                                        'label',
                                        event.target.value,
                                    )
                                }
                                placeholder="Label"
                            />
                            <Input
                                value={row.value}
                                onChange={(event) =>
                                    updateDetailRow(
                                        row.id,
                                        'value',
                                        event.target.value,
                                    )
                                }
                                placeholder="Value"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={() => removeDetailRow(row.id)}
                            >
                                Remove
                            </Button>
                            <InputError
                                className="md:col-span-2"
                                message={
                                    form.errors[
                                        `details.${index}.field_name`
                                    ] as string | undefined
                                }
                            />
                        </div>
                    ))}
                </div>

                {detailUrlWarnings.length > 0 ? (
                    <Alert>
                        <AlertTitle>URL hint</AlertTitle>
                        <AlertDescription>
                            {detailUrlWarnings.map((warning) => (
                                <p key={warning.index}>{warning.message}</p>
                            ))}
                        </AlertDescription>
                    </Alert>
                ) : null}
            </section>

            <section className="space-y-4 rounded-xl border p-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Project images</h2>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={addImageRow}
                    >
                        Add image
                    </Button>
                </div>

                {existingImages.length > 0 ? (
                    <div className="space-y-2">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Existing images
                        </h3>
                        <div className="grid gap-3 md:grid-cols-2">
                            {existingImages.map((image) => (
                                <div
                                    key={image.id}
                                    className="rounded-lg border p-3"
                                >
                                    <img
                                        src={image.image_url}
                                        alt={image.alt_text || 'Project image'}
                                        className="h-36 w-full rounded-md object-cover"
                                    />
                                    <div className="mt-2 text-xs text-muted-foreground">
                                        <p>Type: {image.type}</p>
                                        <p>Sort: {image.sort_order}</p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        className="mt-3"
                                        disabled={isDeletingImage[image.id]}
                                        onClick={() => {
                                            void deleteExistingImage(image.id);
                                        }}
                                    >
                                        <Trash2Icon className="size-4" />
                                        {isDeletingImage[image.id]
                                            ? 'Deleting...'
                                            : 'Delete image'}
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : null}

                <div className="space-y-3">
                    {newImageRows.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No new images selected yet.
                        </p>
                    ) : null}

                    {newImageRows.map((row) => (
                        <div
                            key={row.id}
                            className="grid gap-3 rounded-lg border p-3 md:grid-cols-2"
                        >
                            <div className="space-y-2 md:col-span-2">
                                <Label htmlFor={`new-image-${row.id}`}>
                                    Image file
                                </Label>
                                <div
                                    className={`rounded-lg border border-dashed p-3 transition-colors ${
                                        imageDropActiveRows[row.id]
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border'
                                    }`}
                                    onDragEnter={(event) => {
                                        event.preventDefault();
                                        setImageDropActiveRows((current) => ({
                                            ...current,
                                            [row.id]: true,
                                        }));
                                    }}
                                    onDragOver={(event) => {
                                        event.preventDefault();
                                    }}
                                    onDragLeave={(event) => {
                                        event.preventDefault();
                                        setImageDropActiveRows((current) => ({
                                            ...current,
                                            [row.id]: false,
                                        }));
                                    }}
                                    onDrop={(event) => {
                                        event.preventDefault();
                                        setImageDropActiveRows((current) => ({
                                            ...current,
                                            [row.id]: false,
                                        }));
                                        updateImageRow(
                                            row.id,
                                            'file',
                                            firstImageFile(
                                                event.dataTransfer.files,
                                            ),
                                        );
                                    }}
                                >
                                    <p className="mb-2 text-sm text-muted-foreground">
                                        Drag and drop an image, or choose one.
                                    </p>
                                    <Input
                                        id={`new-image-${row.id}`}
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp"
                                        className="file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5"
                                        onChange={(event) =>
                                            updateImageRow(
                                                row.id,
                                                'file',
                                                firstImageFile(
                                                    event.target.files,
                                                ),
                                            )
                                        }
                                    />
                                </div>
                                {row.preview_url ? (
                                    <img
                                        src={row.preview_url}
                                        alt="Selected file preview"
                                        className="h-40 w-full rounded-md object-cover"
                                    />
                                ) : null}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor={`alt-text-${row.id}`}>
                                    Alt text
                                </Label>
                                <Input
                                    id={`alt-text-${row.id}`}
                                    value={row.alt_text}
                                    onChange={(event) =>
                                        updateImageRow(
                                            row.id,
                                            'alt_text',
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Describe this image"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor={`type-${row.id}`}>
                                    Image type
                                </Label>
                                <Select
                                    value={row.type}
                                    onValueChange={(value) =>
                                        updateImageRow(row.id, 'type', value)
                                    }
                                >
                                    <SelectTrigger
                                        id={`type-${row.id}`}
                                        className="w-full"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="gallery">
                                            Gallery
                                        </SelectItem>
                                        <SelectItem value="wireframe">
                                            Wireframe
                                        </SelectItem>
                                        <SelectItem value="screenshot">
                                            Screenshot
                                        </SelectItem>
                                        <SelectItem value="thumbnail">
                                            Thumbnail
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor={`sort-order-${row.id}`}>
                                    Sort order
                                </Label>
                                <Input
                                    id={`sort-order-${row.id}`}
                                    type="number"
                                    min={0}
                                    value={row.sort_order}
                                    onChange={(event) =>
                                        updateImageRow(
                                            row.id,
                                            'sort_order',
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>

                            <div className="flex items-end">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => removeImageRow(row.id)}
                                >
                                    Remove row
                                </Button>
                            </div>
                        </div>
                    ))}

                    <InputError message={form.errors.images} />
                </div>
            </section>

            <div className="flex items-center gap-3">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : submitLabel}
                </Button>
                {form.processing ? (
                    <p className="text-sm text-muted-foreground">
                        Submitting...
                    </p>
                ) : null}
            </div>
        </form>
    );
}
