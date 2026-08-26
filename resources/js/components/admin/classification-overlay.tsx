import ContentClassificationForm, {
    
    STATUS_COLORS
} from '@/components/admin/content-classification-form';
import type {ContentItem} from '@/components/admin/content-classification-form';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/components/ui/sheet';

type Category = { id: number; name: string };
type AgeBand = { value: string; label: string };

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    contentItem: ContentItem | null;
    ageBandValues: string[];
    availableCategories: Category[];
    allAgeBands: AgeBand[];
    onSaved?: () => void;
};

function formatDuration(seconds: number | null): string {
    if (!seconds) {
return 'N/A';
}

    const m = Math.floor(seconds / 60);
    const s = seconds % 60;

    return `${m}m ${s}s`;
}

export default function ClassificationOverlay({
    open,
    onOpenChange,
    contentItem,
    ageBandValues,
    availableCategories,
    allAgeBands,
    onSaved,
}: Props) {
    if (!contentItem) {
return null;
}

    const statusColor = STATUS_COLORS[contentItem.moderation_status] ?? STATUS_COLORS.candidate;

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="w-full overflow-y-auto sm:max-w-lg">
                <SheetHeader className="pr-8">
                    <SheetTitle className="line-clamp-1">{contentItem.title}</SheetTitle>
                    <SheetDescription>
                        <span className={`mr-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor}`}>
                            {contentItem.moderation_status.replace('_', ' ')}
                        </span>
                        {contentItem.provider} &middot; {formatDuration(contentItem.duration_seconds)}
                    </SheetDescription>
                </SheetHeader>

                {/* Preview */}
                {contentItem.thumbnail_url && (
                    <div className="mx-4 aspect-video overflow-hidden rounded-lg bg-black">
                        <img
                            src={contentItem.thumbnail_url}
                            alt={contentItem.title}
                            className="h-full w-full object-cover"
                        />
                    </div>
                )}

                {/* Description */}
                {contentItem.description && (
                    <div className="mx-4 rounded-xl border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                        <p className="line-clamp-3 text-xs text-muted-foreground">{contentItem.description}</p>
                    </div>
                )}

                {/* Classification form */}
                <div className="px-4 pb-4">
                    <ContentClassificationForm
                        contentItem={contentItem}
                        ageBandValues={ageBandValues}
                        availableCategories={availableCategories}
                        allAgeBands={allAgeBands}
                        onSaved={onSaved}
                        embedded
                    />
                </div>
            </SheetContent>
        </Sheet>
    );
}
