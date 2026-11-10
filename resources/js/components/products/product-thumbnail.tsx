import { ImageOff } from 'lucide-react';
import { cn } from '@/lib/utils';

type Props = {
    src: string | null;
    alt: string;
    className?: string;
};

/**
 * A square product image, with a placeholder when there is no thumbnail.
 */
export default function ProductThumbnail({ src, alt, className }: Props) {
    return (
        <div
            className={cn(
                'flex aspect-square shrink-0 items-center justify-center overflow-hidden rounded-md border bg-muted',
                className,
            )}
        >
            {src ? (
                <img
                    src={src}
                    alt={alt}
                    className="size-full object-cover"
                    loading="lazy"
                />
            ) : (
                <ImageOff
                    className="size-1/3 text-muted-foreground"
                    aria-hidden
                />
            )}
        </div>
    );
}
