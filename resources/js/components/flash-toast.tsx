import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

type FlashData = {
    success?: string | null;
    error?: string | null;
};

type ToastState = {
    type: 'success' | 'error';
    message: string;
};

type SuccessEvent = {
    detail: {
        page?: {
            props?: {
                flash?: FlashData;
            };
        };
    };
};

function toToastState(flash?: FlashData): ToastState | null {
    const successMessage = flash?.success?.trim();
    if (successMessage) {
        return { type: 'success', message: successMessage };
    }

    const errorMessage = flash?.error?.trim();
    if (errorMessage) {
        return { type: 'error', message: errorMessage };
    }

    return null;
}

export function FlashToast({ initialFlash }: { initialFlash?: FlashData }) {
    const [toast, setToast] = useState<ToastState | null>(() =>
        toToastState(initialFlash),
    );
    const toastKey = toast ? `${toast.type}:${toast.message}` : null;
    const [dismissedToastKey, setDismissedToastKey] = useState<string | null>(
        null,
    );

    useEffect(() => {
        setToast(toToastState(initialFlash));
    }, [initialFlash]);

    useEffect(() => {
        return router.on('success', (event) => {
            const successEvent = event as SuccessEvent;
            setToast(toToastState(successEvent.detail.page?.props?.flash));
        });
    }, []);

    useEffect(() => {
        if (!toastKey) {
            return;
        }

        const timeout = window.setTimeout(() => {
            setDismissedToastKey(toastKey);
        }, 4500);

        return () => {
            window.clearTimeout(timeout);
        };
    }, [toastKey]);

    if (!toast || dismissedToastKey === toastKey) {
        return null;
    }

    return (
        <div className="pointer-events-none fixed top-4 right-4 z-[120] w-full max-w-sm px-4 sm:top-6 sm:right-6 sm:px-0">
            <div
                role={toast.type === 'error' ? 'alert' : 'status'}
                aria-live={toast.type === 'error' ? 'assertive' : 'polite'}
                className={`pointer-events-auto rounded-xl border px-4 py-3 pr-10 text-sm shadow-lg backdrop-blur transition-all ${
                    toast.type === 'error'
                        ? 'border-red-500/40 bg-red-500/10 text-red-100'
                        : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-100'
                }`}
            >
                <p>{toast.message}</p>
                <button
                    type="button"
                    onClick={() => {
                        setDismissedToastKey(toastKey);
                    }}
                    className="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-md text-current/75 transition hover:text-current focus-visible:ring-2 focus-visible:ring-current/50 focus-visible:outline-none"
                    aria-label="Dismiss notification"
                >
                    x
                </button>
            </div>
        </div>
    );
}
