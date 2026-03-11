import type { ErrorInfo, ReactNode } from 'react';
import { Component } from 'react';

type ErrorBoundaryProps = {
    children: ReactNode;
};

type ErrorBoundaryState = {
    hasError: boolean;
};

export class ErrorBoundary extends Component<
    ErrorBoundaryProps,
    ErrorBoundaryState
> {
    public constructor(props: ErrorBoundaryProps) {
        super(props);

        this.state = {
            hasError: false,
        };
    }

    public static getDerivedStateFromError(): ErrorBoundaryState {
        return {
            hasError: true,
        };
    }

    public componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
        console.error('Unhandled render error', error, errorInfo);
    }

    public render(): ReactNode {
        if (this.state.hasError) {
            return (
                <main className="flex min-h-screen items-center justify-center bg-background p-6 text-foreground">
                    <section className="w-full max-w-lg rounded-2xl border border-border portfolio-glass p-8 text-center shadow-sm">
                        <p className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                            Something went wrong
                        </p>
                        <h1 className="mt-3 font-display text-2xl font-semibold md:text-3xl">
                            We hit an unexpected issue.
                        </h1>
                        <p className="mt-3 text-sm text-muted-foreground md:text-base">
                            Try refreshing this page. If the issue persists,
                            check the browser console for more details.
                        </p>
                        <button
                            className="mt-6 inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                            onClick={() => window.location.reload()}
                            type="button"
                        >
                            Refresh page
                        </button>
                    </section>
                </main>
            );
        }

        return this.props.children;
    }
}
