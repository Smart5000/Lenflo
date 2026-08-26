import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

type Category = { id: number; name: string };
type AgeBand = { value: string; label: string };
type Reviewer = { id: number; name: string };

export type ContentItem = {
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

export const STATUS_COLORS: Record<string, string> = {
    candidate: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    pending_review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    published: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
    disabled: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

type Props = {
    contentItem: ContentItem;
    ageBandValues: string[];
    availableCategories: Category[];
    allAgeBands: AgeBand[];
    onSaved?: () => void;
    embedded?: boolean;
};

export default function ContentClassificationForm({
    contentItem,
    ageBandValues,
    availableCategories,
    allAgeBands,
    onSaved,
    embedded = false,
}: Props) {
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
            onSuccess: () => {
                toast.success('Classification saved.');
                onSaved?.();
            },
        });
    }

    function handleApprove() {
        router.post(`/admin/content/${contentItem.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => onSaved?.(),
        });
    }

    function handleReject() {
        router.post(`/admin/content/${contentItem.id}/reject`, { rejection_reason: rejectReason }, {
            preserveScroll: true,
            onSuccess: () => {
                setRejectReason('');
                setShowRejectDialog(false);
                onSaved?.();
            },
        });
    }

    function handlePublish() {
        router.post(`/admin/content/${contentItem.id}/publish`, {}, {
            preserveScroll: true,
            onSuccess: () => onSaved?.(),
        });
    }

    function handleDisable() {
        router.post(`/admin/content/${contentItem.id}/disable`, {}, {
            preserveScroll: true,
            onSuccess: () => onSaved?.(),
        });
    }

    function handleReReview() {
        router.post(`/admin/content/${contentItem.id}/re-review`, {}, {
            preserveScroll: true,
            onSuccess: () => onSaved?.(),
        });
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

    const content = (
        <div className={`flex flex-col gap-4 ${embedded ? '' : ''}`}>
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
                        <Label htmlFor={`language-${contentItem.id}`} className="text-xs text-muted-foreground">Language</Label>
                        <select
                            id={`language-${contentItem.id}`}
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
                        <Label htmlFor={`country-${contentItem.id}`} className="text-xs text-muted-foreground">Country</Label>
                        <select
                            id={`country-${contentItem.id}`}
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
    );

    return content;
}
