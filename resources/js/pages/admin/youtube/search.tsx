import { Form, Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useState } from 'react';

type SearchResult = {
    id: string;
    title: string;
    thumbnail: string;
    channel: string;
    publishedAt: string;
    already_imported: boolean;
};

type Props = {
    query: string | null;
    results: SearchResult[] | null;
    nextPageToken: string | null;
    error: string | null;
    apiKeyConfigured: boolean;
};

export default function YouTubeSearch({ query, results, error, apiKeyConfigured }: Props) {
    const [urlInput, setUrlInput] = useState('');

    function handleImport(videoId: string) {
        router.post('/admin/youtube/import', { video_id: videoId }, {
            preserveScroll: true,
        });
    }

    function handleImportUrl(e: React.FormEvent) {
        e.preventDefault();
        router.post('/admin/content/import-url', { url: urlInput }, {
            preserveScroll: true,
            onSuccess: () => setUrlInput(''),
        });
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
                            />
                        </div>
                        <Button type="submit" variant="outline" disabled={!urlInput}>
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
                                            >
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
        </>
    );
}

YouTubeSearch.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'YouTube Discovery', href: '/admin/youtube/search' }]}>
        {page}
    </AppLayout>
);
