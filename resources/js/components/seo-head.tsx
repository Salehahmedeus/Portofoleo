import { Head } from '@inertiajs/react';

type SeoHeadProps = {
    title: string;
    description: string;
    image?: string;
    url?: string;
};

export function SeoHead({ title, description, image, url }: SeoHeadProps) {
    return (
        <Head title={title}>
            <meta
                head-key="description"
                name="description"
                content={description}
            />
            <meta head-key="og:title" property="og:title" content={title} />
            <meta
                head-key="og:description"
                property="og:description"
                content={description}
            />
            <meta head-key="og:type" property="og:type" content="website" />
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content={image ? 'summary_large_image' : 'summary'}
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={title}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={description}
            />
            {url ? (
                <meta head-key="og:url" property="og:url" content={url} />
            ) : null}
            {image ? (
                <>
                    <meta
                        head-key="og:image"
                        property="og:image"
                        content={image}
                    />
                    <meta
                        head-key="twitter:image"
                        name="twitter:image"
                        content={image}
                    />
                </>
            ) : null}
        </Head>
    );
}
