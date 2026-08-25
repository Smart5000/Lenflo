import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type Category = {
    id: number;
    name: string;
};

type User = {
    id: number;
    name: string;
};

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
    editorial_score: string | null;
    published_at: string | null;
    created_at: string;
    categories: Category[];
    reviewer: User | null;
};

type Props = {
    contentItem: ContentItem;
};

export default function ContentShow({ contentItem }: Props) {
    return (
        <>
            <Head title={contentItem.title} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">{contentItem.title}</h1>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-2 text-lg font-semibold">Details</h2>
                        <dl className="space-y-2 text-sm">
                            <div>
                                <dt className="font-medium text-muted-foreground">Provider</dt>
                                <dd>{contentItem.provider}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">External ID</dt>
                                <dd>{contentItem.external_id}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Status</dt>
                                <dd>{contentItem.moderation_status}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Duration</dt>
                                <dd>{contentItem.duration_seconds ? `${contentItem.duration_seconds}s` : 'N/A'}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Language</dt>
                                <dd>{contentItem.original_language_code ?? 'N/A'}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Country</dt>
                                <dd>{contentItem.country_code ?? 'N/A'}</dd>
                            </div>
                        </dl>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-2 text-lg font-semibold">Review</h2>
                        <dl className="space-y-2 text-sm">
                            <div>
                                <dt className="font-medium text-muted-foreground">Reviewer</dt>
                                <dd>{contentItem.reviewer?.name ?? 'Unassigned'}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Reviewed At</dt>
                                <dd>{contentItem.reviewed_at ?? 'N/A'}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Editorial Score</dt>
                                <dd>{contentItem.editorial_score ?? 'N/A'}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-muted-foreground">Published At</dt>
                                <dd>{contentItem.published_at ?? 'N/A'}</dd>
                            </div>
                            {contentItem.rejection_reason && (
                                <div>
                                    <dt className="font-medium text-muted-foreground">Rejection Reason</dt>
                                    <dd>{contentItem.rejection_reason}</dd>
                                </div>
                            )}
                        </dl>
                    </div>
                </div>
                {contentItem.description && (
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-2 text-lg font-semibold">Description</h2>
                        <p className="text-sm">{contentItem.description}</p>
                    </div>
                )}
                <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <h2 className="mb-2 text-lg font-semibold">Categories</h2>
                    <div className="flex flex-wrap gap-2">
                        {contentItem.categories.map((category) => (
                            <span
                                key={category.id}
                                className="rounded-full bg-sidebar-border/50 px-3 py-1 text-xs"
                            >
                                {category.name}
                            </span>
                        ))}
                    </div>
                </div>
                <a href="/admin/content" className="text-sm text-blue-600 hover:underline">
                    &larr; Back to content list
                </a>
            </div>
        </>
    );
}

ContentShow.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'Content Detail', href: '/admin/content' }]}>
        {page}
    </AppLayout>
);
