import { Head, useForm, usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Service, SettingValue, Skill } from '@/types';

type ContentSettings = Record<string, SettingValue>;

type ContentSectionPayload<TValue extends Record<string, unknown>> = {
    key: string;
    group: string;
    value: TValue;
};

type SimpleContentValue = Record<string, string>;

type ServiceEditorItem = {
    title: string;
    description: string;
    icon: string;
    sort_order: number;
};

type SkillEditorItem = {
    name: string;
    category: string;
    logo_path: string;
    sort_order: number;
};

type AdminContentEditProps = {
    settings: ContentSettings;
    services: Service[];
    skills: Skill[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Content',
        href: '/admin/content',
    },
];

function asRecord(
    value: SettingValue | undefined,
): Record<string, SettingValue> | null {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        return value as Record<string, SettingValue>;
    }

    return null;
}

function asString(value: SettingValue | undefined): string {
    return typeof value === 'string' ? value : '';
}

function isValidUrl(value: string): boolean {
    if (!value) {
        return true;
    }

    try {
        const parsedUrl = new URL(value);

        return (
            parsedUrl.protocol === 'http:' || parsedUrl.protocol === 'https:'
        );
    } catch {
        return false;
    }
}

function normalizeSortOrder<T extends { sort_order: number }>(items: T[]): T[] {
    return items.map((item, index) => ({
        ...item,
        sort_order: index,
    }));
}

function moveItem<T>(items: T[], index: number, direction: -1 | 1): T[] {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= items.length) {
        return items;
    }

    const next = [...items];
    const current = next[index];

    next[index] = next[targetIndex];
    next[targetIndex] = current;

    return next;
}

export default function AdminContentEdit({
    settings,
    services,
    skills,
}: AdminContentEditProps) {
    const { flash } = usePage().props;

    const heroContent = asRecord(settings.hero_content);
    const contactInformation = asRecord(settings.contact_information);
    const homepageSeo = asRecord(settings.homepage_seo);

    const heroForm = useForm<ContentSectionPayload<SimpleContentValue>>({
        key: 'hero_content',
        group: 'homepage',
        value: {
            headline: asString(heroContent?.headline),
            subheadline: asString(heroContent?.subheadline),
            cta_label: asString(heroContent?.cta_label),
        },
    });

    const contactForm = useForm<ContentSectionPayload<SimpleContentValue>>({
        key: 'contact_information',
        group: 'contact',
        value: {
            email: asString(contactInformation?.email),
            location: asString(contactInformation?.location),
            whatsapp: asString(contactInformation?.whatsapp),
            linkedin: asString(contactInformation?.linkedin),
            github: asString(contactInformation?.github),
        },
    });

    const servicesForm = useForm<
        ContentSectionPayload<{ items: ServiceEditorItem[] }>
    >({
        key: 'services',
        group: 'homepage',
        value: {
            items: normalizeSortOrder(
                services.map((service, index) => ({
                    title: service.title,
                    description: service.description,
                    icon: service.icon ?? '',
                    sort_order:
                        typeof service.sort_order === 'number'
                            ? service.sort_order
                            : index,
                })),
            ),
        },
    });

    const skillsForm = useForm<
        ContentSectionPayload<{ items: SkillEditorItem[] }>
    >({
        key: 'skills',
        group: 'homepage',
        value: {
            items: normalizeSortOrder(
                skills.map((skill, index) => ({
                    name: skill.name,
                    category: skill.category,
                    logo_path: skill.logo_path ?? '',
                    sort_order:
                        typeof skill.sort_order === 'number'
                            ? skill.sort_order
                            : index,
                })),
            ),
        },
    });

    const seoForm = useForm<ContentSectionPayload<SimpleContentValue>>({
        key: 'homepage_seo',
        group: 'seo',
        value: {
            meta_title: asString(homepageSeo?.meta_title),
            meta_description: asString(homepageSeo?.meta_description),
        },
    });

    const socialWarnings = useMemo(() => {
        const warnings: string[] = [];

        if (!isValidUrl(contactForm.data.value.linkedin)) {
            warnings.push('LinkedIn should be a full URL (https://...).');
        }

        if (!isValidUrl(contactForm.data.value.github)) {
            warnings.push('GitHub should be a full URL (https://...).');
        }

        if (
            contactForm.data.value.whatsapp &&
            !isValidUrl(contactForm.data.value.whatsapp)
        ) {
            warnings.push('WhatsApp should be a valid URL when provided.');
        }

        return warnings;
    }, [
        contactForm.data.value.github,
        contactForm.data.value.linkedin,
        contactForm.data.value.whatsapp,
    ]);

    const updateServiceItem = (
        index: number,
        field: keyof Omit<ServiceEditorItem, 'sort_order'>,
        value: string,
    ): void => {
        const nextItems = servicesForm.data.value.items.map(
            (item, itemIndex) =>
                itemIndex === index
                    ? {
                          ...item,
                          [field]: value,
                      }
                    : item,
        );

        servicesForm.setData('value', {
            items: nextItems,
        });
    };

    const addServiceItem = (): void => {
        servicesForm.setData('value', {
            items: [
                ...servicesForm.data.value.items,
                {
                    title: '',
                    description: '',
                    icon: '',
                    sort_order: servicesForm.data.value.items.length,
                },
            ],
        });
    };

    const removeServiceItem = (index: number): void => {
        servicesForm.setData('value', {
            items: normalizeSortOrder(
                servicesForm.data.value.items.filter(
                    (_, itemIndex) => itemIndex !== index,
                ),
            ),
        });
    };

    const moveServiceItem = (index: number, direction: -1 | 1): void => {
        servicesForm.setData('value', {
            items: normalizeSortOrder(
                moveItem(servicesForm.data.value.items, index, direction),
            ),
        });
    };

    const updateSkillItem = (
        index: number,
        field: keyof Omit<SkillEditorItem, 'sort_order'>,
        value: string,
    ): void => {
        const nextItems = skillsForm.data.value.items.map((item, itemIndex) =>
            itemIndex === index
                ? {
                      ...item,
                      [field]: value,
                  }
                : item,
        );

        skillsForm.setData('value', {
            items: nextItems,
        });
    };

    const addSkillItem = (): void => {
        skillsForm.setData('value', {
            items: [
                ...skillsForm.data.value.items,
                {
                    name: '',
                    category: '',
                    logo_path: '',
                    sort_order: skillsForm.data.value.items.length,
                },
            ],
        });
    };

    const removeSkillItem = (index: number): void => {
        skillsForm.setData('value', {
            items: normalizeSortOrder(
                skillsForm.data.value.items.filter(
                    (_, itemIndex) => itemIndex !== index,
                ),
            ),
        });
    };

    const moveSkillItem = (index: number, direction: -1 | 1): void => {
        skillsForm.setData('value', {
            items: normalizeSortOrder(
                moveItem(skillsForm.data.value.items, index, direction),
            ),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Content" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="rounded-xl border bg-card p-4">
                    <h1 className="text-xl font-semibold">Content editor</h1>
                    <p className="text-sm text-muted-foreground">
                        Update homepage copy, contact details, services, skills,
                        and SEO settings.
                    </p>
                </div>

                {flash?.success ? (
                    <Alert>
                        <AlertTitle>Success</AlertTitle>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                {flash?.error ? (
                    <Alert variant="destructive">
                        <AlertTitle>Error</AlertTitle>
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                ) : null}

                <section className="rounded-xl border bg-card p-4">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-semibold">
                                Hero content
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Headline area shown on the homepage.
                            </p>
                        </div>
                        <Button
                            type="button"
                            onClick={() =>
                                heroForm.put('/admin/content', {
                                    preserveScroll: true,
                                })
                            }
                            disabled={heroForm.processing}
                        >
                            {heroForm.processing
                                ? 'Saving...'
                                : 'Save hero content'}
                        </Button>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2 md:col-span-2">
                            <Label htmlFor="headline">Headline</Label>
                            <Input
                                id="headline"
                                value={heroForm.data.value.headline}
                                onChange={(event) =>
                                    heroForm.setData('value', {
                                        ...heroForm.data.value,
                                        headline: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2 md:col-span-2">
                            <Label htmlFor="subheadline">Subheadline</Label>
                            <Textarea
                                id="subheadline"
                                value={heroForm.data.value.subheadline}
                                onChange={(event) =>
                                    heroForm.setData('value', {
                                        ...heroForm.data.value,
                                        subheadline: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="cta_label">CTA label</Label>
                            <Input
                                id="cta_label"
                                value={heroForm.data.value.cta_label}
                                onChange={(event) =>
                                    heroForm.setData('value', {
                                        ...heroForm.data.value,
                                        cta_label: event.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                    <InputError
                        message={heroForm.errors.value}
                        className="mt-3"
                    />
                    {heroForm.recentlySuccessful ? (
                        <p className="mt-2 text-sm text-emerald-600">
                            Hero content saved.
                        </p>
                    ) : null}
                </section>

                <section className="rounded-xl border bg-card p-4">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-semibold">
                                Contact and social
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Contact info and profile links shown to
                                visitors.
                            </p>
                        </div>
                        <Button
                            type="button"
                            onClick={() =>
                                contactForm.put('/admin/content', {
                                    preserveScroll: true,
                                })
                            }
                            disabled={contactForm.processing}
                        >
                            {contactForm.processing
                                ? 'Saving...'
                                : 'Save contact details'}
                        </Button>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="contact_email">Email</Label>
                            <Input
                                id="contact_email"
                                type="email"
                                value={contactForm.data.value.email}
                                onChange={(event) =>
                                    contactForm.setData('value', {
                                        ...contactForm.data.value,
                                        email: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="contact_location">Location</Label>
                            <Input
                                id="contact_location"
                                value={contactForm.data.value.location}
                                onChange={(event) =>
                                    contactForm.setData('value', {
                                        ...contactForm.data.value,
                                        location: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="contact_whatsapp">WhatsApp</Label>
                            <Input
                                id="contact_whatsapp"
                                placeholder="https://wa.me/..."
                                value={contactForm.data.value.whatsapp}
                                onChange={(event) =>
                                    contactForm.setData('value', {
                                        ...contactForm.data.value,
                                        whatsapp: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="contact_linkedin">LinkedIn</Label>
                            <Input
                                id="contact_linkedin"
                                placeholder="https://linkedin.com/in/..."
                                value={contactForm.data.value.linkedin}
                                onChange={(event) =>
                                    contactForm.setData('value', {
                                        ...contactForm.data.value,
                                        linkedin: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2 md:col-span-2">
                            <Label htmlFor="contact_github">GitHub</Label>
                            <Input
                                id="contact_github"
                                placeholder="https://github.com/username"
                                value={contactForm.data.value.github}
                                onChange={(event) =>
                                    contactForm.setData('value', {
                                        ...contactForm.data.value,
                                        github: event.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                    <InputError
                        message={contactForm.errors.value}
                        className="mt-3"
                    />
                    {socialWarnings.length > 0 ? (
                        <Alert className="mt-3">
                            <AlertTitle>URL validation hint</AlertTitle>
                            <AlertDescription>
                                {socialWarnings.map((warning) => (
                                    <p key={warning}>{warning}</p>
                                ))}
                            </AlertDescription>
                        </Alert>
                    ) : null}
                    {contactForm.recentlySuccessful ? (
                        <p className="mt-2 text-sm text-emerald-600">
                            Contact section saved.
                        </p>
                    ) : null}
                </section>

                <section className="rounded-xl border bg-card p-4">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-semibold">Services</h2>
                            <p className="text-sm text-muted-foreground">
                                Manage homepage service rows and ordering.
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={addServiceItem}
                            >
                                Add row
                            </Button>
                            <Button
                                type="button"
                                onClick={() =>
                                    servicesForm.put('/admin/content', {
                                        preserveScroll: true,
                                    })
                                }
                                disabled={servicesForm.processing}
                            >
                                {servicesForm.processing
                                    ? 'Saving...'
                                    : 'Save services'}
                            </Button>
                        </div>
                    </div>

                    {servicesForm.data.value.items.length === 0 ? (
                        <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                            No services yet. Add a row to get started.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {servicesForm.data.value.items.map(
                                (item, index) => (
                                    <div
                                        key={`service-${index}`}
                                        className="grid gap-3 rounded-lg border p-3 md:grid-cols-2"
                                    >
                                        <div className="space-y-2 md:col-span-2">
                                            <Label
                                                htmlFor={`service-title-${index}`}
                                            >
                                                Title
                                            </Label>
                                            <Input
                                                id={`service-title-${index}`}
                                                value={item.title}
                                                onChange={(event) =>
                                                    updateServiceItem(
                                                        index,
                                                        'title',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={
                                                    servicesForm.errors[
                                                        `value.items.${index}.title`
                                                    ] as string | undefined
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2 md:col-span-2">
                                            <Label
                                                htmlFor={`service-description-${index}`}
                                            >
                                                Description
                                            </Label>
                                            <Textarea
                                                id={`service-description-${index}`}
                                                value={item.description}
                                                onChange={(event) =>
                                                    updateServiceItem(
                                                        index,
                                                        'description',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={
                                                    servicesForm.errors[
                                                        `value.items.${index}.description`
                                                    ] as string | undefined
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label
                                                htmlFor={`service-icon-${index}`}
                                            >
                                                Icon
                                            </Label>
                                            <Input
                                                id={`service-icon-${index}`}
                                                value={item.icon}
                                                placeholder="Code2"
                                                onChange={(event) =>
                                                    updateServiceItem(
                                                        index,
                                                        'icon',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        <div className="flex items-end justify-end gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() =>
                                                    moveServiceItem(index, -1)
                                                }
                                                disabled={index === 0}
                                            >
                                                Up
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() =>
                                                    moveServiceItem(index, 1)
                                                }
                                                disabled={
                                                    index ===
                                                    servicesForm.data.value
                                                        .items.length -
                                                        1
                                                }
                                            >
                                                Down
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                onClick={() =>
                                                    removeServiceItem(index)
                                                }
                                            >
                                                Remove
                                            </Button>
                                        </div>
                                    </div>
                                ),
                            )}
                        </div>
                    )}

                    <InputError
                        message={servicesForm.errors.value}
                        className="mt-3"
                    />
                    {servicesForm.recentlySuccessful ? (
                        <p className="mt-2 text-sm text-emerald-600">
                            Services saved.
                        </p>
                    ) : null}
                </section>

                <section className="rounded-xl border bg-card p-4">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-semibold">Skills</h2>
                            <p className="text-sm text-muted-foreground">
                                Manage homepage skills and ordering.
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={addSkillItem}
                            >
                                Add row
                            </Button>
                            <Button
                                type="button"
                                onClick={() =>
                                    skillsForm.put('/admin/content', {
                                        preserveScroll: true,
                                    })
                                }
                                disabled={skillsForm.processing}
                            >
                                {skillsForm.processing
                                    ? 'Saving...'
                                    : 'Save skills'}
                            </Button>
                        </div>
                    </div>

                    {skillsForm.data.value.items.length === 0 ? (
                        <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                            No skills yet. Add a row to get started.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {skillsForm.data.value.items.map((item, index) => (
                                <div
                                    key={`skill-${index}`}
                                    className="grid gap-3 rounded-lg border p-3 md:grid-cols-2"
                                >
                                    <div className="space-y-2">
                                        <Label htmlFor={`skill-name-${index}`}>
                                            Name
                                        </Label>
                                        <Input
                                            id={`skill-name-${index}`}
                                            value={item.name}
                                            onChange={(event) =>
                                                updateSkillItem(
                                                    index,
                                                    'name',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={
                                                skillsForm.errors[
                                                    `value.items.${index}.name`
                                                ] as string | undefined
                                            }
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label
                                            htmlFor={`skill-category-${index}`}
                                        >
                                            Category
                                        </Label>
                                        <Input
                                            id={`skill-category-${index}`}
                                            value={item.category}
                                            onChange={(event) =>
                                                updateSkillItem(
                                                    index,
                                                    'category',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={
                                                skillsForm.errors[
                                                    `value.items.${index}.category`
                                                ] as string | undefined
                                            }
                                        />
                                    </div>
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor={`skill-logo-${index}`}>
                                            Logo path
                                        </Label>
                                        <Input
                                            id={`skill-logo-${index}`}
                                            value={item.logo_path}
                                            placeholder="/images/skills/react.svg"
                                            onChange={(event) =>
                                                updateSkillItem(
                                                    index,
                                                    'logo_path',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                    </div>
                                    <div className="flex items-end justify-end gap-2 md:col-span-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                moveSkillItem(index, -1)
                                            }
                                            disabled={index === 0}
                                        >
                                            Up
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                moveSkillItem(index, 1)
                                            }
                                            disabled={
                                                index ===
                                                skillsForm.data.value.items
                                                    .length -
                                                    1
                                            }
                                        >
                                            Down
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            onClick={() =>
                                                removeSkillItem(index)
                                            }
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    <InputError
                        message={skillsForm.errors.value}
                        className="mt-3"
                    />
                    {skillsForm.recentlySuccessful ? (
                        <p className="mt-2 text-sm text-emerald-600">
                            Skills saved.
                        </p>
                    ) : null}
                </section>

                <section className="rounded-xl border bg-card p-4">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-semibold">
                                Homepage SEO
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Metadata used by search engines and social
                                cards.
                            </p>
                        </div>
                        <Button
                            type="button"
                            onClick={() =>
                                seoForm.put('/admin/content', {
                                    preserveScroll: true,
                                })
                            }
                            disabled={seoForm.processing}
                        >
                            {seoForm.processing ? 'Saving...' : 'Save SEO'}
                        </Button>
                    </div>

                    <div className="grid gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="meta_title">Meta title</Label>
                            <Input
                                id="meta_title"
                                value={seoForm.data.value.meta_title}
                                onChange={(event) =>
                                    seoForm.setData('value', {
                                        ...seoForm.data.value,
                                        meta_title: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="meta_description">
                                Meta description
                            </Label>
                            <Textarea
                                id="meta_description"
                                value={seoForm.data.value.meta_description}
                                onChange={(event) =>
                                    seoForm.setData('value', {
                                        ...seoForm.data.value,
                                        meta_description: event.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                    <InputError
                        message={seoForm.errors.value}
                        className="mt-3"
                    />
                    {seoForm.recentlySuccessful ? (
                        <p className="mt-2 text-sm text-emerald-600">
                            SEO section saved.
                        </p>
                    ) : null}
                </section>
            </div>
        </AppLayout>
    );
}
