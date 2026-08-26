import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { useState } from 'react';

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

const STATUS_COLORS: Record<string, string> = {
    candidate: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    pending_review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    published: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
    disabled: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

export default function ContentReview({ contentItem, ageBandValues, availableCategories, allAgeBands }: Props) {
    const [rejectReason, setRejectReason] = useState('');
    const [showRejectDialog, setShowRejectDialog] = useState(false);

    const classificationForm = useForm({
        category_ids: contentItem.categories.map((c) => c.id),
        age_bands: ageBandValues,
        original_language_code: contentItem.original_language_code ?? '',
        country_code: contentItem.country_code ?? '',
        internal_notes: contentItem.internal_notes ?? '',
    });

    function handleClassificationSave() {
        classificationForm.put(`/admin/content/${contentItem.id}/classification`, {
            preserveScroll: true,
        });
    }

    function handleApprove() {
        router.post(`/admin/content/${contentItem.id}/approve`, {}, { preserveScroll: true });
    }

    function handleReject() {
        router.post(`/admin/content/${contentItem.id}/reject`, { rejection_reason: rejectReason }, {
            preserveScroll: true,
            onSuccess: () => {
                setRejectReason('');
                setShowRejectDialog(false);
            },
        });
    }

    function handlePublish() {
        router.post(`/admin/content/${contentItem.id}/publish`, {}, { preserveScroll: true });
    }

    function handleDisable() {
        router.post(`/admin/content/${contentItem.id}/disable`, {}, { preserveScroll: true });
    }

    function handleReReview() {
        router.post(`/admin/content/${contentItem.id}/re-review`, {}, { preserveScroll: true });
    }

    function toggleCategory(categoryId: number) {
        const current = classificationForm.data.category_ids;
        const next = current.includes(categoryId)
            ? current.filter((id) => id !== categoryId)
            : [...current, categoryId];
        classificationForm.setData('category_ids', next);
    }

    function toggleAgeBand(band: string) {
        const current = classificationForm.data.age_bands;
        const next = current.includes(band)
            ? current.filter((b) => b !== band)
            : [...current, band];
        classificationForm.setData('age_bands', next);
    }

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
                        {/* Action buttons */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Actions</h2>
                            <div className="flex flex-wrap gap-2">
                                {(contentItem.moderation_status === 'candidate' || contentItem.moderation_status === 'pending_review') && (
                                    <>
                                        <Button size="sm" onClick={handleApprove}>Approve</Button>
                                        <Button size="sm" variant="destructive" onClick={() => setShowRejectDialog(true)}>Reject</Button>
                                    </>
                                )}
                                {contentItem.moderation_status === 'approved' && (
                                    <>
                                        <Button size="sm" onClick={handlePublish}>Publish</Button>
                                        <Button size="sm" variant="destructive" onClick={handleDisable}>Disable</Button>
                                    </>
                                )}
                                {contentItem.moderation_status === 'published' && (
                                    <Button size="sm" variant="destructive" onClick={handleDisable}>Disable</Button>
                                )}
                                {(contentItem.moderation_status === 'rejected' || contentItem.moderation_status === 'disabled') && (
                                    <Button size="sm" variant="outline" onClick={handleReReview}>Re-review</Button>
                                )}
                            </div>
                        </div>

                        {/* Reject dialog */}
                        {showRejectDialog && (
                            <div className="rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-700 dark:bg-red-950">
                                <h3 className="mb-2 text-sm font-semibold text-red-800 dark:text-red-300">Reject with reason</h3>
                                <textarea
                                    className="w-full rounded-lg border border-red-300 bg-white p-2 text-sm dark:border-red-700 dark:bg-gray-900"
                                    rows={3}
                                    placeholder="Why is this content being rejected?"
                                    value={rejectReason}
                                    onChange={(e) => setRejectReason(e.target.value)}
                                />
                                <div className="mt-2 flex gap-2">
                                    <Button size="sm" variant="destructive" onClick={handleReject} disabled={!rejectReason.trim()}>
                                        Confirm Reject
                                    </Button>
                                    <Button size="sm" variant="outline" onClick={() => setShowRejectDialog(false)}>
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                        )}

                        {/* Categories */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Categories</h2>
                            <div className="flex flex-wrap gap-2">
                                {availableCategories.map((category) => (
                                    <label
                                        key={category.id}
                                        className={`flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs transition-colors ${
                                            classificationForm.data.category_ids.includes(category.id)
                                                ? 'border-blue-500 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                                                : 'border-sidebar-border/70 bg-transparent text-muted-foreground hover:bg-sidebar-border/30 dark:border-sidebar-border'
                                        }`}
                                    >
                                        <Checkbox
                                            checked={classificationForm.data.category_ids.includes(category.id)}
                                            onCheckedChange={() => toggleCategory(category.id)}
                                            className="sr-only"
                                        />
                                        {category.name}
                                    </label>
                                ))}
                                {availableCategories.length === 0 && (
                                    <p className="text-xs text-muted-foreground">No categories available.</p>
                                )}
                            </div>
                        </div>

                        {/* Age bands */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Age Eligibility</h2>
                            <div className="flex flex-wrap gap-2">
                                {allAgeBands.map((band) => (
                                    <label
                                        key={band.value}
                                        className={`flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs transition-colors ${
                                            classificationForm.data.age_bands.includes(band.value)
                                                ? 'border-green-500 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'border-sidebar-border/70 bg-transparent text-muted-foreground hover:bg-sidebar-border/30 dark:border-sidebar-border'
                                        }`}
                                    >
                                        <Checkbox
                                            checked={classificationForm.data.age_bands.includes(band.value)}
                                            onCheckedChange={() => toggleAgeBand(band.value)}
                                            className="sr-only"
                                        />
                                        {band.label}
                                    </label>
                                ))}
                            </div>
                        </div>

                        {/* Language & Country */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Language & Region</h2>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <Label htmlFor="language" className="text-xs text-muted-foreground">Language</Label>
                                    <select
                                        id="language"
                                        className="mt-1 w-full rounded-lg border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm dark:border-sidebar-border"
                                        value={classificationForm.data.original_language_code}
                                        onChange={(e) => classificationForm.setData('original_language_code', e.target.value)}
                                    >
                                        <option value="">Any</option>
                                        <option value="en">English</option>
                                        <option value="fr">French</option>
                                        <option value="sw">Swahili</option>
                                        <option value="pt">Portuguese</option>
                                        <option value="ar">Arabic</option>
                                        <option value="ha">Hausa</option>
                                        <option value="yo">Yoruba</option>
                                        <option value="am">Amharic</option>
                                    </select>
                                </div>
                                <div>
                                    <Label htmlFor="country" className="text-xs text-muted-foreground">Country</Label>
                                    <select
                                        id="country"
                                        className="mt-1 w-full rounded-lg border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm dark:border-sidebar-border"
                                        value={classificationForm.data.country_code}
                                        onChange={(e) => classificationForm.setData('country_code', e.target.value)}
                                    >
                                        <option value="">All regions</option>
                                        <option value="NG">Nigeria</option>
                                        <option value="KE">Kenya</option>
                                        <option value="ZA">South Africa</option>
                                        <option value="GH">Ghana</option>
                                        <option value="TZ">Tanzania</option>
                                        <option value="UG">Uganda</option>
                                        <option value="ET">Ethiopia</option>
                                        <option value="RW">Rwanda</option>
                                        <option value="SN">Senegal</option>
                                        <option value="CM">Cameroon</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {/* Internal notes */}
                        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <h2 className="mb-3 text-lg font-semibold">Internal Notes</h2>
                            <textarea
                                className="w-full rounded-lg border border-sidebar-border/70 bg-transparent p-3 text-sm dark:border-sidebar-border"
                                rows={3}
                                placeholder="Private moderation notes..."
                                value={classificationForm.data.internal_notes}
                                onChange={(e) => classificationForm.setData('internal_notes', e.target.value)}
                            />
                        </div>

                        {/* Save classification */}
                        <Button onClick={handleClassificationSave} disabled={classificationForm.processing}>
                            {classificationForm.processing ? 'Saving...' : 'Save Classification'}
                        </Button>
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
