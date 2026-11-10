import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import ProductThumbnail from '@/components/products/product-thumbnail';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    currentUrl?: string | null;
    /** Shown in the preview when there is no current image, e.g. the catalog thumbnail. */
    fallbackUrl?: string | null;
    error?: string;
    label?: string;
    hint?: string;
};

/**
 * A file input for a thumbnail, with a live preview and an option to remove the current image.
 */
export default function ThumbnailInput({
    currentUrl = null,
    fallbackUrl = null,
    error,
    label = 'Thumbnail',
    hint = 'JPG, PNG or WebP, up to 2 MB.',
}: Props) {
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [remove, setRemove] = useState(false);

    useEffect(() => {
        return () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        };
    }, [previewUrl]);

    const shownUrl = previewUrl ?? (remove ? null : currentUrl) ?? fallbackUrl;

    return (
        <div className="grid gap-2">
            <Label htmlFor="thumbnail">{label}</Label>
            <div className="flex items-start gap-4">
                <ProductThumbnail
                    src={shownUrl}
                    alt="Thumbnail preview"
                    className="size-24"
                />
                <div className="grid flex-1 gap-2">
                    <Input
                        id="thumbnail"
                        name="thumbnail"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        onChange={(event) => {
                            const file = event.target.files?.[0];
                            setPreviewUrl(
                                file ? URL.createObjectURL(file) : null,
                            );
                        }}
                    />
                    <p className="text-xs text-muted-foreground">{hint}</p>
                    {currentUrl && !previewUrl && (
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remove_thumbnail"
                                name="remove_thumbnail"
                                value="1"
                                checked={remove}
                                onCheckedChange={(checked) =>
                                    setRemove(checked === true)
                                }
                            />
                            <Label
                                htmlFor="remove_thumbnail"
                                className="font-normal"
                            >
                                Remove current image
                            </Label>
                        </div>
                    )}
                </div>
            </div>
            <InputError message={error} />
        </div>
    );
}
