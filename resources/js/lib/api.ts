function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

async function getErrorMessage(response: Response): Promise<string> {
    const fallbackMessage = `Request failed with status ${response.status}`;

    try {
        const payload = (await response.json()) as {
            message?: string;
            error?: string;
        };

        return payload.message ?? payload.error ?? fallbackMessage;
    } catch {
        return fallbackMessage;
    }
}

export async function postJson<T>(url: string, payload: unknown): Promise<T> {
    const csrfToken = getCsrfToken();

    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error(await getErrorMessage(response));
    }

    return (await response.json()) as T;
}
