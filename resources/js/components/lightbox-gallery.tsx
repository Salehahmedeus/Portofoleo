import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import type { ProjectImage } from '@/types';

type LightboxGalleryProps = {
    images: ProjectImage[];
    title?: string;
};

export function LightboxGallery({
    images,
    title = 'Project gallery',
}: LightboxGalleryProps) {
    const [selectedIndex, setSelectedIndex] = useState<number | null>(null);

    const orderedImages = useMemo(
        () =>
            [...images].sort(
                (a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0),
            ),
        [images],
    );

    const activeIndex = selectedIndex ?? 0;
    const activeImage = orderedImages[activeIndex];

    if (orderedImages.length === 0) {
        return null;
    }

    return (
        <>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {orderedImages.map((image, index) => (
                    <button
                        key={image.id}
                        type="button"
                        onClick={() => {
                            setSelectedIndex(index);
                        }}
                        className="group relative overflow-hidden rounded-xl border bg-muted text-left"
                    >
                        <img
                            src={image.image_url ?? image.image_path}
                            alt={
                                image.alt_text ?? `${title} image ${index + 1}`
                            }
                            loading="lazy"
                            className="aspect-[4/3] size-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                    </button>
                ))}
            </div>

            <Dialog
                open={selectedIndex !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedIndex(null);
                    }
                }}
            >
                <DialogContent className="max-w-5xl overflow-hidden border-none bg-transparent p-0 shadow-none">
                    <div className="relative rounded-xl bg-black/95 p-4 md:p-6">
                        <DialogTitle className="sr-only">{title}</DialogTitle>
                        <DialogDescription className="sr-only">
                            Browse project gallery images.
                        </DialogDescription>

                        {activeImage ? (
                            <>
                                <img
                                    src={
                                        activeImage.image_url ??
                                        activeImage.image_path
                                    }
                                    alt={activeImage.alt_text ?? title}
                                    className="mx-auto max-h-[75vh] w-auto max-w-full rounded-lg object-contain"
                                />

                                <div className="mt-4 flex items-center justify-between gap-4 text-sm text-white">
                                    <p className="line-clamp-2">
                                        {activeImage.alt_text ??
                                            'Project image'}
                                    </p>
                                    <p className="shrink-0 text-white/80">
                                        {activeIndex + 1} /{' '}
                                        {orderedImages.length}
                                    </p>
                                </div>
                            </>
                        ) : null}

                        {orderedImages.length > 1 ? (
                            <>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedIndex((current) => {
                                            if (current === null) {
                                                return 0;
                                            }

                                            return current === 0
                                                ? orderedImages.length - 1
                                                : current - 1;
                                        });
                                    }}
                                    className="absolute top-1/2 left-3 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/25"
                                    aria-label="Previous image"
                                >
                                    <ChevronLeftIcon className="size-5" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedIndex((current) => {
                                            if (current === null) {
                                                return 0;
                                            }

                                            return current ===
                                                orderedImages.length - 1
                                                ? 0
                                                : current + 1;
                                        });
                                    }}
                                    className="absolute top-1/2 right-3 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/25"
                                    aria-label="Next image"
                                >
                                    <ChevronRightIcon className="size-5" />
                                </button>
                            </>
                        ) : null}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
