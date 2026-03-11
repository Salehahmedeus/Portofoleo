import { Link } from '@inertiajs/react';
import { Container } from '@/components/layout/container';
import { Section } from '@/components/layout/section';
import { SeoHead } from '@/components/seo-head';
import { Button } from '@/components/ui/button';

export default function NotFound() {
    return (
        <Section className="min-h-screen">
            <SeoHead
                title="Page not found"
                description="The page you are looking for does not exist."
            />

            <Container className="flex min-h-[70vh] flex-col items-center justify-center text-center">
                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    Error 404
                </p>
                <h1 className="mt-2 font-display text-4xl font-semibold sm:text-5xl">
                    This page could not be found
                </h1>
                <p className="mt-4 max-w-xl text-sm text-muted-foreground sm:text-base">
                    The link may be outdated or the page might have moved. You
                    can head back to the homepage or browse the project archive.
                </p>
                <div className="mt-6 flex flex-wrap justify-center gap-3">
                    <Button asChild>
                        <Link href="/">Go home</Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href="/projects">View projects</Link>
                    </Button>
                </div>
            </Container>
        </Section>
    );
}
