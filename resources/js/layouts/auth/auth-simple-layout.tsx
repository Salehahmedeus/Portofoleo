import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative flex min-h-svh items-center justify-center overflow-hidden bg-slate-950 p-6 md:p-10">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(56,189,248,0.25),transparent_35%),radial-gradient(circle_at_80%_10%,rgba(59,130,246,0.18),transparent_30%),linear-gradient(160deg,#020617_0%,#0f172a_55%,#111827_100%)]"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -top-32 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-cyan-400/20 blur-3xl"
            />

            <div className="relative w-full max-w-md rounded-3xl border border-white/15 bg-white/5 p-8 shadow-[0_30px_120px_rgba(2,6,23,0.65)] backdrop-blur-xl sm:p-10">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4 text-center">
                        <Link
                            href={home()}
                            className="group flex flex-col items-center gap-2 rounded-xl px-2 py-1 font-medium transition-colors hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-300/80 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 focus-visible:outline-none"
                        >
                            <div className="mb-1 flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition group-hover:border-cyan-200/50 group-hover:bg-cyan-200/10">
                                <AppLogoIcon className="size-9 fill-current" />
                            </div>
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-2">
                            <h1 className="text-2xl font-semibold text-white">
                                {title}
                            </h1>
                            <p className="text-sm text-slate-300/90">
                                {description}
                            </p>
                        </div>
                    </div>

                    <div className="text-slate-100">{children}</div>
                </div>
            </div>
        </div>
    );
}
