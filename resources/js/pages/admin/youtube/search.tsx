import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import ClassificationOverlay from '@/components/admin/classification-overlay';
import type { ContentItem } from '@/components/admin/content-classification-form';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';

type SearchResult = {
    id: string;
    title: string;
    thumbnail: string;
    channel: string;
    publishedAt: string;
    already_imported: boolean;
};

type Category = { id: number; name: string };
type AgeBand = { value: string; label: string };

type Props = {
    query: string | null;
    results: SearchResult[] | null;
    nextPageToken: string | null;
    error: string | null;
    apiKeyConfigured: boolean;
};

type OverlayData = {
    contentItem: ContentItem;
    ageBandValues: string[];
    availableCategories: Category[];
    allAgeBands: AgeBand[];
};

export default function YouTubeSearch({ query, results, error, apiKeyConfigured }: Props) {
    const [urlInput, setUrlInput] = useState('');
    const [importing, setImporting] = useState(false);
    const [overlayData, setOverlayData] = useState<OverlayData | null>(null);
    const [overlayOpen, setOverlayOpen] = useState(false);

    function getCsrfToken(): string {
        return decodeURIComponent(
            document.cookie
                .split('; ')
                .find((c) => c.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '',
        );
    }

    function handleImport(videoId: string) {
        setImporting(true);

        fetch('/admin/youtube/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ video_id: videoId }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    const data = await response.json().catch(() => null);

                    throw new Error(data?.error ?? 'Import failed.');
                }

                return response.json();
            })
            .then((data: OverlayData) => {
                toast.success(`Imported "${data.contentItem.title}".`);
                setOverlayData(data);
                setOverlayOpen(true);
            })
            .catch((err: Error) => {
                toast.error(err.message || 'Import failed. Please try again.');
            })
            .finally(() => {
                setImporting(false);
            });
    }

    function handleImportUrl(e: React.FormEvent) {
        e.preventDefault();
        setImporting(true);

        fetch('/admin/content/import-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ url: urlInput }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    const data = await response.json().catch(() => null);

                    throw new Error(data?.error ?? 'Import failed.');
                }

                return response.json();
            })
            .then((data: OverlayData) => {
                toast.success(`Imported "${data.contentItem.title}".`);
                setOverlayData(data);
                setOverlayOpen(true);
                setUrlInput('');
            })
            .catch((err: Error) => {
                toast.error(err.message || 'Import failed. Please try again.');
            })
            .finally(() => {
                setImporting(false);
            });
    }

    function handleOverlaySaved() {
        setOverlayOpen(false);
        setOverlayData(null);
    }

    return (
        <>
            <Head title="YouTube Discovery" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">YouTube Discovery</h1>

                {!apiKeyConfigured && (
                    <div className="rounded-xl border border-yellow-300 bg-yellow-50 p-4 text-sm dark:border-yellow-700 dark:bg-yellow-950">
                        YouTube API key is not configured. Set <code>YOUTUBE_API_KEY</code> in your <code>.env</code> file and enable the YouTube Data API v3 in Google Cloud Console.
                    </div>
                )}

                {/* Manual URL Import */}
                <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <h2 className="mb-3 text-lg font-semibold">Import by URL</h2>
                    <form onSubmit={handleImportUrl} className="flex gap-3">
                        <div className="flex-1">
                            <Label htmlFor="url" className="sr-only">YouTube URL</Label>
                            <Input
                                id="url"
                                type="url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                value={urlInput}
                                onChange={(e) => setUrlInput(e.target.value)}
                                required
                                disabled={importing}
                            />
                        </div>
                        <Button type="submit" variant="outline" disabled={!urlInput || importing}>
                            {importing && <Spinner />}
                            Import
                        </Button>
                    </form>
                </div>

                {/* Search */}
                {apiKeyConfigured && (
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-3 text-lg font-semibold">Search YouTube</h2>
                        <Form method="get" action="/admin/youtube/search" preserveState className="flex gap-3">
                            {({ processing }) => (
                                <>
                                    <div className="flex-1">
                                        <Label htmlFor="q" className="sr-only">Search query</Label>
                                        <Input
                                            id="q"
                                            name="q"
                                            type="search"
                                            placeholder="Search for educational videos..."
                                            defaultValue={query ?? ''}
                                        />
                                    </div>
                                    <Button type="submit" disabled={processing}>
                                        {processing && <Spinner />}
                                        Search
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>
                )}

                {error && (
                    <div className="rounded-xl border border-red-300 bg-red-50 p-4 text-sm dark:border-red-700 dark:bg-red-950">
                        {error}
                    </div>
                )}

                {/* Results */}
                {results && results.length === 0 && (
                    <p className="text-sm text-muted-foreground">No results found for "{query}".</p>
                )}

                {results && results.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {results.map((item) => (
                            <div key={item.id} className="rounded-xl border border-sidebar-border/70 overflow-hidden dark:border-sidebar-border">
                                <img
                                    src={item.thumbnail}
                                    alt={item.title}
                                    className="aspect-video w-full object-cover"
                                />
                                <div className="p-3">
                                    <h3 className="line-clamp-2 text-sm font-medium">{item.title}</h3>
                                    <p className="mt-1 text-xs text-muted-foreground">{item.channel}</p>
                                    <div className="mt-3">
                                        {item.already_imported ? (
                                            <span className="text-xs text-muted-foreground">Already imported</span>
                                        ) : (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => handleImport(item.id)}
                                                disabled={importing}
                                            >
                                                {importing && <Spinner />}
                                                Import
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Classification overlay */}
            {overlayData && (
                <ClassificationOverlay
                    open={overlayOpen}
                    onOpenChange={setOverlayOpen}
                    contentItem={overlayData.contentItem}
                    ageBandValues={overlayData.ageBandValues}
                    availableCategories={overlayData.availableCategories}
                    allAgeBands={overlayData.allAgeBands}
                    onSaved={handleOverlaySaved}
                />
            )}
        </>
    );
}

YouTubeSearch.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'YouTube Discovery', href: '/admin/youtube/search' }]}>
        {page}
    </AppLayout>
);
