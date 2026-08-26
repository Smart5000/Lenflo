import { Head } from '@inertiajs/react';
import ContentClassificationForm, {
    STATUS_COLORS,
} from '@/components/admin/content-classification-form';
import AppLayout from '@/layouts/app-layout';

type Category = { id: number; name: string };
type AgeBand = { value: string; label: string };
type Reviewer = { id: number; name: string };

type ContentItem = {
    id: number;
    title: string;
    description: string | null;
    provider: string;
    external_id: string;
    thumbnail_url: string | null;
    embed_url: string;
    duration_seconds: number | null;
    original_language_code: string | null;
    country_code: string | null;
    moderation_status: string;
    reviewed_by: number | null;
    reviewed_at: string | null;
    rejection_reason: string | null;
    internal_notes: string | null;
    published_at: string | null;
    categories: Category[];
    reviewer: Reviewer | null;
};

type Props = {
    contentItem: ContentItem;
    ageBandValues: string[];
    availableCategories: Category[];
    allAgeBands: AgeBand[];
};

export default function ContentReview({ contentItem, ageBandValues, availableCategories, allAgeBands }: Props) {
    const statusColor = STATUS_COLORS[contentItem.moderation_status] ?? STATUS_COLORS.candidate;

    return (
        <>
            <Head title={`Review: ${contentItem.title}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <h1 className="text-2xl font-bold">{contentItem.title}</h1>
                        <span className={`rounded-full px-3 py-1 text-xs font-medium ${statusColor}`}>
                            {contentItem.moderation_status.replace('_', ' ')}
                        </span>
                    </div>
                    <a href="/admin/content" className="text-sm text-blue-600 hover:underline">
                        &larr; Back to content list
                    </a>
                </div>

                <div className="grid gap-4 lg:grid-cols-5">
                    {/* Left column: preview + details */}
                    <div className="flex flex-col gap-4 lg:col-span-3">
                        {/* Embed preview */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Preview</h2>
                            <div className="aspect-video w-full overflow-hidden rounded-lg bg-black">
                                <iframe
                                    src={`${contentItem.embed_url}?rel=0`}
                                    title={contentItem.title}
                                    className="h-full w-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowFullScreen
                                />
                            </div>
                        </div>

                        {/* Details */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Details</h2>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt className="font-medium text-muted-foreground">Provider</dt>
                                    <dd>{contentItem.provider}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-muted-foreground">External ID</dt>
                                    <dd className="font-mono text-xs">{contentItem.external_id}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-muted-foreground">Duration</dt>
                                    <dd>{contentItem.duration_seconds ? `${Math.floor(contentItem.duration_seconds / 60)}m ${contentItem.duration_seconds % 60}s` : 'N/A'}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-muted-foreground">Reviewed By</dt>
                                    <dd>{contentItem.reviewer?.name ?? 'Unreviewed'}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-muted-foreground">Reviewed At</dt>
                                    <dd>{contentItem.reviewed_at ? new Date(contentItem.reviewed_at).toLocaleString() : 'N/A'}</dd>
                                </div>
                                <div>
                                    <dt className="font-medium text-muted-foreground">Published At</dt>
                                    <dd>{contentItem.published_at ? new Date(contentItem.published_at).toLocaleString() : 'N/A'}</dd>
                                </div>
                            </dl>
                        </div>

                        {/* Description */}
                        {contentItem.description && (
                            <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <h2 className="mb-2 text-lg font-semibold">Description</h2>
                                <p className="text-sm whitespace-pre-wrap">{contentItem.description}</p>
                            </div>
                        )}

                        {/* Rejection reason */}
                        {contentItem.rejection_reason && (
                            <div className="rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-700 dark:bg-red-950">
                                <h2 className="mb-2 text-sm font-semibold text-red-800 dark:text-red-300">Rejection Reason</h2>
                                <p className="text-sm text-red-700 dark:text-red-400">{contentItem.rejection_reason}</p>
                            </div>
                        )}
                    </div>

                    {/* Right column: classification + actions */}
                    <div className="flex flex-col gap-4 lg:col-span-2">
                        <ContentClassificationForm
                            contentItem={contentItem}
                            ageBandValues={ageBandValues}
                            availableCategories={availableCategories}
                            allAgeBands={allAgeBands}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}

ContentReview.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'Review Content', href: '/admin/content' }]}>
        {page}
    </AppLayout>
);
