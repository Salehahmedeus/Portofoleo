import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type DailyTotal = {
    date: string;
    count: number;
};

type ProjectView = {
    slug: string;
    title: string;
    views: number;
};

type CountryDistributionItem = {
    country: string;
    count: number;
};

type ContactSubmissionItem = {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    created_at: string | null;
    read: boolean;
};

type OutboundClickItem = {
    target: string;
    count: number;
};

type AdminAnalyticsIndexProps = {
    range: number;
    available_ranges: number[];
    total_events: number;
    unique_sessions: number;
    top_event_types: Record<string, number>;
    daily_totals: DailyTotal[];
    total_visitors: number;
    page_views: number;
    contact_submissions_count: number;
    top_project: ProjectView | null;
    top_project_views: ProjectView[];
    traffic_sources: Record<string, number>;
    device_types: Record<string, number>;
    country_distribution: CountryDistributionItem[];
    contact_submissions: ContactSubmissionItem[];
    outbound_clicks: OutboundClickItem[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Analytics',
        href: '/admin/analytics',
    },
];

function formatDate(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
    }).format(date);
}

function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Unknown date';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
}

export default function AdminAnalyticsIndex({
    range,
    available_ranges,
    total_events,
    unique_sessions,
    top_event_types,
    daily_totals,
    total_visitors,
    page_views,
    contact_submissions_count,
    top_project,
    top_project_views,
    traffic_sources,
    device_types,
    country_distribution,
    contact_submissions,
    outbound_clicks,
}: AdminAnalyticsIndexProps) {
    const topTypeEntries = Object.entries(top_event_types).sort(
        (a, b) => b[1] - a[1],
    );
    const maxTypeCount = topTypeEntries.length > 0 ? topTypeEntries[0][1] : 0;
    const maxDailyCount = daily_totals.reduce(
        (max, day) => Math.max(max, day.count),
        0,
    );
    const trafficEntries = Object.entries(traffic_sources).sort(
        (a, b) => b[1] - a[1],
    );
    const trafficTotal = trafficEntries.reduce((total, [, count]) => {
        return total + count;
    }, 0);
    const deviceEntries = Object.entries(device_types).sort(
        (a, b) => b[1] - a[1],
    );
    const deviceTotal = deviceEntries.reduce((total, [, count]) => {
        return total + count;
    }, 0);
    const maxCountryCount = country_distribution.reduce((max, item) => {
        return Math.max(max, item.count);
    }, 0);
    const maxOutboundClicks = outbound_clicks.reduce((max, item) => {
        return Math.max(max, item.count);
    }, 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Analytics" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-card p-4">
                    <div>
                        <h1 className="text-xl font-semibold">Analytics</h1>
                        <p className="text-sm text-muted-foreground">
                            Event activity overview for the selected date range.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {available_ranges.map((availableRange) => (
                            <button
                                key={availableRange}
                                type="button"
                                className={`rounded-md border px-3 py-1.5 text-sm transition-colors ${
                                    range === availableRange
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'hover:bg-accent'
                                }`}
                                onClick={() =>
                                    router.get(
                                        '/admin/analytics',
                                        { range: availableRange },
                                        {
                                            preserveScroll: true,
                                            preserveState: true,
                                            replace: true,
                                        },
                                    )
                                }
                            >
                                Last {availableRange} days
                            </button>
                        ))}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">Range</p>
                        <p className="mt-2 text-2xl font-semibold">
                            {range} days
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Total events
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {total_events}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Unique sessions
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {unique_sessions}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Avg events/day
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {(total_events / Math.max(range, 1)).toFixed(1)}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Total visitors
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {total_visitors}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Page views
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {page_views}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Contact submissions
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {contact_submissions_count}
                        </p>
                    </div>
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Top project
                        </p>
                        <p className="mt-2 text-lg font-semibold">
                            {top_project
                                ? top_project.title
                                : 'No project views'}
                        </p>
                        {top_project ? (
                            <p className="text-sm text-muted-foreground">
                                {top_project.views} views
                            </p>
                        ) : null}
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Top event types
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Ranked by frequency.
                        </p>

                        {topTypeEntries.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No event type data for this range.
                            </div>
                        ) : (
                            <ul className="space-y-3">
                                {topTypeEntries.map(([eventType, count]) => {
                                    const width =
                                        maxTypeCount > 0
                                            ? `${(count / maxTypeCount) * 100}%`
                                            : '0%';

                                    return (
                                        <li
                                            key={eventType}
                                            className="space-y-1"
                                        >
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium">
                                                    {eventType}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {count}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-primary"
                                                    style={{ width }}
                                                />
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">Daily totals</h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Event count per day.
                        </p>

                        {daily_totals.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No daily activity yet.
                            </div>
                        ) : (
                            <ul className="space-y-2">
                                {daily_totals.map((day) => {
                                    const width =
                                        maxDailyCount > 0
                                            ? `${(day.count / maxDailyCount) * 100}%`
                                            : '0%';

                                    return (
                                        <li
                                            key={day.date}
                                            className="grid grid-cols-[90px_1fr_auto] items-center gap-3"
                                        >
                                            <span className="text-xs text-muted-foreground">
                                                {formatDate(day.date)}
                                            </span>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-emerald-500"
                                                    style={{ width }}
                                                />
                                            </div>
                                            <span className="text-xs font-medium">
                                                {day.count}
                                            </span>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Top projects viewed
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Ranked by project detail page views.
                        </p>

                        {top_project_views.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No project view data for this range.
                            </div>
                        ) : (
                            <ol className="space-y-2">
                                {top_project_views.map((project, index) => (
                                    <li
                                        key={project.slug}
                                        className="flex items-center justify-between rounded-md border p-3"
                                    >
                                        <div>
                                            <p className="text-sm font-medium">
                                                #{index + 1} {project.title}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                /projects/{project.slug}
                                            </p>
                                        </div>
                                        <span className="text-sm font-semibold">
                                            {project.views}
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </section>

                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Traffic sources
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Direct, social, search, and referral split.
                        </p>

                        {trafficEntries.length === 0 || trafficTotal === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No traffic source data for this range.
                            </div>
                        ) : (
                            <ul className="space-y-3">
                                {trafficEntries.map(([source, count]) => {
                                    const width = `${(count / trafficTotal) * 100}%`;

                                    return (
                                        <li key={source} className="space-y-1">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium capitalize">
                                                    {source}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {count}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-primary"
                                                    style={{ width }}
                                                />
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Device type distribution
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Visitor sessions by device category.
                        </p>

                        {deviceEntries.length === 0 || deviceTotal === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No device type data for this range.
                            </div>
                        ) : (
                            <ul className="space-y-3">
                                {deviceEntries.map(([device, count]) => {
                                    const width = `${(count / deviceTotal) * 100}%`;

                                    return (
                                        <li key={device} className="space-y-1">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium capitalize">
                                                    {device}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {count}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-emerald-500"
                                                    style={{ width }}
                                                />
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Country distribution
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Events grouped by country code.
                        </p>

                        {country_distribution.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No country distribution data for this range.
                            </div>
                        ) : (
                            <ul className="space-y-2">
                                {country_distribution.map((item) => {
                                    const width =
                                        maxCountryCount > 0
                                            ? `${(item.count / maxCountryCount) * 100}%`
                                            : '0%';

                                    return (
                                        <li
                                            key={item.country}
                                            className="grid grid-cols-[90px_1fr_auto] items-center gap-3"
                                        >
                                            <span className="text-xs font-medium">
                                                {item.country}
                                            </span>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-amber-500"
                                                    style={{ width }}
                                                />
                                            </div>
                                            <span className="text-xs text-muted-foreground">
                                                {item.count}
                                            </span>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Recent contact submissions
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Latest submissions received in this range.
                        </p>

                        {contact_submissions.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No contact submissions for this range.
                            </div>
                        ) : (
                            <ul className="space-y-3">
                                {contact_submissions.map((submission) => (
                                    <li
                                        key={submission.id}
                                        className="rounded-md border p-3"
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-sm font-medium">
                                                    {submission.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {submission.email}
                                                </p>
                                            </div>
                                            <span className="text-xs text-muted-foreground">
                                                {formatDateTime(
                                                    submission.created_at,
                                                )}
                                            </span>
                                        </div>
                                        {submission.subject ? (
                                            <p className="mt-2 text-sm font-medium">
                                                {submission.subject}
                                            </p>
                                        ) : null}
                                        <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                            {submission.message}
                                        </p>
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {submission.read
                                                ? 'Read'
                                                : 'Unread'}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-xl border bg-card p-4">
                        <h2 className="text-lg font-semibold">
                            Outbound click channels
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Targets users clicked away to from your site.
                        </p>

                        {outbound_clicks.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No outbound clicks for this range.
                            </div>
                        ) : (
                            <ul className="space-y-2">
                                {outbound_clicks.map((item) => {
                                    const width =
                                        maxOutboundClicks > 0
                                            ? `${(item.count / maxOutboundClicks) * 100}%`
                                            : '0%';

                                    return (
                                        <li
                                            key={item.target}
                                            className="space-y-1"
                                        >
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium">
                                                    {item.target}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {item.count}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded bg-muted">
                                                <div
                                                    className="h-2 rounded bg-sky-500"
                                                    style={{ width }}
                                                />
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
