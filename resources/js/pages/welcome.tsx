import { Link } from '@inertiajs/react';
import {
    ArrowUpRightIcon,
    MailIcon,
    MapPinIcon,
    MessageCircleIcon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import { Container } from '@/components/layout/container';
import { Grid } from '@/components/layout/grid';
import { Section } from '@/components/layout/section';
import { ProjectCard } from '@/components/project-card';
import { Reveal } from '@/components/reveal';
import { SeoHead } from '@/components/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { postJson } from '@/lib/api';
import type {
    AnalyticsEventPayload,
    ContactSettings,
    ContactSubmissionPayload,
    HeroSettings,
    Project,
    Service,
    SiteSettings,
    Skill,
    SettingValue,
} from '@/types';

type WelcomeProps = {
    canRegister: boolean;
    services: Service[];
    skills: Skill[];
    projects: Project[];
    settings: SiteSettings;
};

type ContactFormValues = ContactSubmissionPayload;

type ContactFormErrors = Partial<Record<keyof ContactFormValues, string>>;

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function asRecord(
    value: SettingValue | undefined,
): Record<string, SettingValue> | null {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        return value as Record<string, SettingValue>;
    }

    return null;
}

function asString(value: SettingValue | undefined): string | undefined {
    return typeof value === 'string' && value.length > 0 ? value : undefined;
}

function getSessionId(): string | undefined {
    if (typeof window === 'undefined') {
        return undefined;
    }

    const key = 'portfolio_session_id';
    const existingSession = window.sessionStorage.getItem(key);

    if (existingSession) {
        return existingSession;
    }

    const generatedSession =
        typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
            ? crypto.randomUUID()
            : `${Date.now()}_${Math.random().toString(16).slice(2)}`;

    window.sessionStorage.setItem(key, generatedSession);

    return generatedSession;
}

function detectDeviceType(): AnalyticsEventPayload['device_type'] {
    if (typeof window === 'undefined') {
        return undefined;
    }

    const ua = window.navigator.userAgent.toLowerCase();

    if (/(ipad|tablet)/.test(ua)) {
        return 'tablet';
    }

    if (/(mobi|android|iphone)/.test(ua)) {
        return 'mobile';
    }

    return 'desktop';
}

function validateContactForm(values: ContactFormValues): ContactFormErrors {
    const errors: ContactFormErrors = {};

    if (values.name.trim().length < 2) {
        errors.name = 'Please enter your full name.';
    }

    if (!emailPattern.test(values.email.trim())) {
        errors.email = 'Please enter a valid email address.';
    }

    if (values.subject && values.subject.trim().length > 255) {
        errors.subject = 'Subject must be less than 255 characters.';
    }

    if (values.message.trim().length < 10) {
        errors.message = 'Please write at least 10 characters.';
    }

    return errors;
}

export default function Welcome({
    canRegister,
    services,
    skills,
    projects,
    settings,
}: WelcomeProps) {
    const [contactValues, setContactValues] = useState<ContactFormValues>({
        name: '',
        email: '',
        subject: '',
        message: '',
        company: '',
    });
    const [contactErrors, setContactErrors] = useState<ContactFormErrors>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submissionStatus, setSubmissionStatus] = useState<{
        type: 'idle' | 'success' | 'error';
        message: string;
    }>({
        type: 'idle',
        message: '',
    });

    const featuredProjects = projects.slice(0, 3);

    const skillGroups = useMemo(() => {
        return skills.reduce<Record<string, Skill[]>>((groups, skill) => {
            const category = skill.category || 'other';

            if (!groups[category]) {
                groups[category] = [];
            }

            groups[category].push(skill);

            return groups;
        }, {});
    }, [skills]);

    const heroSettingsRecord = asRecord(settings.hero_content);
    const contactSettingsRecord = asRecord(settings.contact_information);

    const heroSettings: HeroSettings = {
        headline: asString(heroSettingsRecord?.headline),
        subheadline: asString(heroSettingsRecord?.subheadline),
        cta_label: asString(heroSettingsRecord?.cta_label),
    };

    const contactSettings: ContactSettings = {
        email: asString(contactSettingsRecord?.email),
        location: asString(contactSettingsRecord?.location),
        whatsapp: asString(contactSettingsRecord?.whatsapp),
        linkedin: asString(contactSettingsRecord?.linkedin),
        github: asString(contactSettingsRecord?.github),
    };

    const trackEvent = (
        eventType: string,
        eventData?: Record<string, unknown>,
    ) => {
        const payload: AnalyticsEventPayload = {
            event_type: eventType,
            event_data: eventData,
            page_url:
                typeof window !== 'undefined'
                    ? window.location.href
                    : '/portfolio',
            referrer:
                typeof document !== 'undefined' ? document.referrer : undefined,
            device_type: detectDeviceType(),
            session_id: getSessionId(),
        };

        void postJson<{ id: number; message: string }>(
            '/analytics-events',
            payload,
        ).catch(() => {
            return null;
        });
    };

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const nextErrors = validateContactForm(contactValues);
        setContactErrors(nextErrors);

        if (Object.keys(nextErrors).length > 0) {
            return;
        }

        if (contactValues.company?.trim()) {
            setSubmissionStatus({
                type: 'success',
                message: 'Message sent successfully.',
            });

            return;
        }

        setIsSubmitting(true);

        try {
            await postJson<{ id: number; message: string }>(
                '/contact-submissions',
                contactValues,
            );

            trackEvent('contact_submit', {
                source: 'homepage_contact_form',
            });

            setSubmissionStatus({
                type: 'success',
                message: 'Thanks for your message. I will reply soon.',
            });
            setContactValues({
                name: '',
                email: '',
                subject: '',
                message: '',
                company: '',
            });
        } catch (error) {
            const message =
                error instanceof Error
                    ? error.message
                    : 'Unable to send your message right now.';

            setSubmissionStatus({
                type: 'error',
                message,
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen bg-portfolio-gradient">
            <SeoHead
                title="Portfolio"
                description="Portfolio website showcasing featured projects, services, and contact details."
            />

            <header className="sticky top-0 z-40 border-b border-border/60 bg-background/80 backdrop-blur-lg">
                <Container className="flex h-16 items-center justify-between">
                    <a
                        href="#top"
                        className="font-display text-lg font-semibold"
                    >
                        Portfolio
                    </a>

                    <nav className="hidden items-center gap-5 text-sm md:flex">
                        <a href="#projects">Projects</a>
                        <a href="#services">Services</a>
                        <a href="#skills">Skills</a>
                        <a href="#contact">Contact</a>
                    </nav>

                    <div className="flex items-center gap-2">
                        <Link
                            href="/projects"
                            className="text-sm font-medium"
                            onClick={() => {
                                trackEvent('cta_click', {
                                    target: '/projects',
                                    location: 'top_nav',
                                });
                            }}
                        >
                            Browse projects
                        </Link>
                        {canRegister ? (
                            <Button asChild size="sm" variant="outline">
                                <Link href="/register">Register</Link>
                            </Button>
                        ) : null}
                    </div>
                </Container>
            </header>

            <main id="top">
                <Section className="relative scroll-mt-20 overflow-hidden pt-16">
                    <div
                        className="pointer-events-none absolute -top-20 -left-24 h-72 w-72 float-soft rounded-full bg-portfolio-orb opacity-70 blur-2xl"
                        aria-hidden="true"
                    />
                    <div
                        className="pointer-events-none absolute top-10 right-[-6rem] h-64 w-64 float-soft rounded-full bg-portfolio-orb opacity-60 blur-2xl [animation-delay:1.4s]"
                        aria-hidden="true"
                    />
                    <Container>
                        <Reveal className="space-y-8">
                            <div className="inline-flex items-center rounded-full border bg-background/70 px-3 py-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                Laravel Developer and Product Designer
                            </div>
                            <div className="max-w-3xl space-y-4">
                                <h1 className="font-display text-4xl leading-tight font-semibold text-balance sm:text-5xl">
                                    {heroSettings.headline ??
                                        'Building thoughtful digital products'}
                                </h1>
                                <p className="text-base text-pretty text-muted-foreground sm:text-lg">
                                    {heroSettings.subheadline ??
                                        'I design and develop web experiences with clear UX, strong performance, and maintainable architecture.'}
                                </p>
                            </div>
                            <div className="flex flex-wrap items-center gap-3">
                                <Button asChild size="lg">
                                    <a
                                        href="#projects"
                                        onClick={() => {
                                            trackEvent('cta_click', {
                                                target: '#projects',
                                                location: 'hero',
                                            });
                                        }}
                                    >
                                        {heroSettings.cta_label ??
                                            'View Projects'}
                                    </a>
                                </Button>
                                <Button asChild size="lg" variant="outline">
                                    <a
                                        href="#contact"
                                        onClick={() => {
                                            trackEvent('cta_click', {
                                                target: '#contact',
                                                location: 'hero',
                                            });
                                        }}
                                    >
                                        Start a project
                                    </a>
                                </Button>
                            </div>
                        </Reveal>
                    </Container>
                </Section>

                <Section id="projects" className="scroll-mt-20">
                    <Container>
                        <Reveal className="mb-8 flex items-end justify-between gap-4">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Featured work
                                </p>
                                <h2 className="font-display text-3xl font-semibold">
                                    Recent Projects
                                </h2>
                            </div>
                            <Link
                                href="/projects"
                                className="text-sm font-medium text-portfolio-accent-strong underline-offset-4 hover:underline"
                                onClick={() => {
                                    trackEvent('cta_click', {
                                        target: '/projects',
                                        location: 'projects_section',
                                    });
                                }}
                            >
                                View all
                            </Link>
                        </Reveal>

                        <Grid columns={3}>
                            {featuredProjects.map((project, index) => (
                                <Reveal key={project.id} delay={index * 80}>
                                    <ProjectCard
                                        project={project}
                                        onOutboundClick={(
                                            url,
                                            selectedProject,
                                        ) => {
                                            trackEvent('outbound_click', {
                                                url,
                                                source: 'project_card',
                                                project_slug:
                                                    selectedProject.slug,
                                            });
                                        }}
                                    />
                                </Reveal>
                            ))}
                        </Grid>
                    </Container>
                </Section>

                <Section id="services" className="scroll-mt-20">
                    <Container>
                        <Reveal className="mb-8 space-y-2">
                            <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Services
                            </p>
                            <h2 className="font-display text-3xl font-semibold">
                                How I can help
                            </h2>
                        </Reveal>

                        <Grid columns={3}>
                            {services.map((service, index) => (
                                <Reveal
                                    key={service.id}
                                    delay={index * 70}
                                    className="rounded-2xl border bg-card p-6"
                                >
                                    <div className="space-y-3">
                                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                            {service.icon ?? 'service'}
                                        </p>
                                        <h3 className="font-display text-xl font-semibold">
                                            {service.title}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {service.description}
                                        </p>
                                    </div>
                                </Reveal>
                            ))}
                        </Grid>
                    </Container>
                </Section>

                <Section id="skills" className="scroll-mt-20">
                    <Container>
                        <Reveal className="mb-8 space-y-2">
                            <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Stack
                            </p>
                            <h2 className="font-display text-3xl font-semibold">
                                Skills by category
                            </h2>
                        </Reveal>

                        <div className="grid gap-6 md:grid-cols-2">
                            {Object.entries(skillGroups).map(
                                ([category, categorySkills], index) => (
                                    <Reveal
                                        key={category}
                                        delay={index * 60}
                                        className="rounded-2xl border bg-card p-6"
                                    >
                                        <h3 className="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                            {category}
                                        </h3>
                                        <div className="flex flex-wrap gap-2">
                                            {categorySkills.map((skill) => (
                                                <span
                                                    key={skill.id}
                                                    className="rounded-full border bg-muted px-3 py-1 text-sm"
                                                >
                                                    {skill.name}
                                                </span>
                                            ))}
                                        </div>
                                    </Reveal>
                                ),
                            )}
                        </div>
                    </Container>
                </Section>

                <Section id="contact" className="scroll-mt-20 pb-24">
                    <Container>
                        <div className="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                            <Reveal className="rounded-2xl border bg-card p-6 sm:p-8">
                                <div className="mb-6 space-y-2">
                                    <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Contact
                                    </p>
                                    <h2 className="font-display text-3xl font-semibold">
                                        Let&apos;s build something useful
                                    </h2>
                                </div>

                                <form
                                    className="space-y-4"
                                    onSubmit={handleSubmit}
                                >
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                value={contactValues.name}
                                                onChange={(event) => {
                                                    setContactValues(
                                                        (previous) => ({
                                                            ...previous,
                                                            name: event.target
                                                                .value,
                                                        }),
                                                    );
                                                }}
                                                aria-invalid={Boolean(
                                                    contactErrors.name,
                                                )}
                                            />
                                            {contactErrors.name ? (
                                                <p className="text-xs text-destructive">
                                                    {contactErrors.name}
                                                </p>
                                            ) : null}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={contactValues.email}
                                                onChange={(event) => {
                                                    setContactValues(
                                                        (previous) => ({
                                                            ...previous,
                                                            email: event.target
                                                                .value,
                                                        }),
                                                    );
                                                }}
                                                aria-invalid={Boolean(
                                                    contactErrors.email,
                                                )}
                                            />
                                            {contactErrors.email ? (
                                                <p className="text-xs text-destructive">
                                                    {contactErrors.email}
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subject">Subject</Label>
                                        <Input
                                            id="subject"
                                            value={contactValues.subject}
                                            onChange={(event) => {
                                                setContactValues(
                                                    (previous) => ({
                                                        ...previous,
                                                        subject:
                                                            event.target.value,
                                                    }),
                                                );
                                            }}
                                            aria-invalid={Boolean(
                                                contactErrors.subject,
                                            )}
                                        />
                                        {contactErrors.subject ? (
                                            <p className="text-xs text-destructive">
                                                {contactErrors.subject}
                                            </p>
                                        ) : null}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="message">Message</Label>
                                        <Textarea
                                            id="message"
                                            value={contactValues.message}
                                            onChange={(event) => {
                                                setContactValues(
                                                    (previous) => ({
                                                        ...previous,
                                                        message:
                                                            event.target.value,
                                                    }),
                                                );
                                            }}
                                            aria-invalid={Boolean(
                                                contactErrors.message,
                                            )}
                                        />
                                        {contactErrors.message ? (
                                            <p className="text-xs text-destructive">
                                                {contactErrors.message}
                                            </p>
                                        ) : null}
                                    </div>

                                    <div className="hidden" aria-hidden="true">
                                        <Label htmlFor="company">Company</Label>
                                        <Input
                                            id="company"
                                            tabIndex={-1}
                                            autoComplete="off"
                                            value={contactValues.company}
                                            onChange={(event) => {
                                                setContactValues(
                                                    (previous) => ({
                                                        ...previous,
                                                        company:
                                                            event.target.value,
                                                    }),
                                                );
                                            }}
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={isSubmitting}
                                    >
                                        {isSubmitting
                                            ? 'Sending...'
                                            : 'Send message'}
                                    </Button>

                                    {submissionStatus.type !== 'idle' ? (
                                        <p
                                            className={
                                                submissionStatus.type ===
                                                'success'
                                                    ? 'text-sm text-emerald-600'
                                                    : 'text-sm text-destructive'
                                            }
                                        >
                                            {submissionStatus.message}
                                        </p>
                                    ) : null}
                                </form>
                            </Reveal>

                            <Reveal className="space-y-4 rounded-2xl border bg-card p-6 sm:p-8">
                                <h3 className="font-display text-2xl font-semibold">
                                    Contact details
                                </h3>

                                <div className="space-y-3 text-sm text-muted-foreground">
                                    {contactSettings.email ? (
                                        <a
                                            href={`mailto:${contactSettings.email}`}
                                            className="flex items-center gap-2 hover:text-foreground"
                                            onClick={() => {
                                                trackEvent('outbound_click', {
                                                    target: 'email',
                                                    url: `mailto:${contactSettings.email}`,
                                                });
                                            }}
                                        >
                                            <MailIcon className="size-4" />
                                            {contactSettings.email}
                                        </a>
                                    ) : null}

                                    {contactSettings.location ? (
                                        <p className="flex items-center gap-2">
                                            <MapPinIcon className="size-4" />
                                            {contactSettings.location}
                                        </p>
                                    ) : null}

                                    {contactSettings.linkedin ? (
                                        <a
                                            href={contactSettings.linkedin}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="flex items-center gap-2 hover:text-foreground"
                                            onClick={() => {
                                                trackEvent('outbound_click', {
                                                    target: 'linkedin',
                                                    url: contactSettings.linkedin,
                                                });
                                            }}
                                        >
                                            LinkedIn
                                            <ArrowUpRightIcon className="size-4" />
                                        </a>
                                    ) : null}

                                    {contactSettings.github ? (
                                        <a
                                            href={contactSettings.github}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="flex items-center gap-2 hover:text-foreground"
                                            onClick={() => {
                                                trackEvent('outbound_click', {
                                                    target: 'github',
                                                    url: contactSettings.github,
                                                });
                                            }}
                                        >
                                            GitHub
                                            <ArrowUpRightIcon className="size-4" />
                                        </a>
                                    ) : null}

                                    {contactSettings.whatsapp ? (
                                        <a
                                            href={contactSettings.whatsapp}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="flex items-center gap-2 hover:text-foreground"
                                            onClick={() => {
                                                trackEvent('outbound_click', {
                                                    target: 'whatsapp',
                                                    url: contactSettings.whatsapp,
                                                });
                                            }}
                                        >
                                            <MessageCircleIcon className="size-4" />
                                            WhatsApp
                                        </a>
                                    ) : null}
                                </div>
                            </Reveal>
                        </div>
                    </Container>
                </Section>
            </main>

            <footer className="border-t border-border/70 bg-background/90 py-6">
                <Container className="flex flex-col items-center justify-between gap-3 text-sm text-muted-foreground sm:flex-row">
                    <p>
                        © {new Date().getFullYear()} Portfolio. All rights
                        reserved.
                    </p>
                    <div className="flex items-center gap-4">
                        <a href="#top">Back to top</a>
                        <Link href="/projects">Projects</Link>
                    </div>
                </Container>
            </footer>
        </div>
    );
}
