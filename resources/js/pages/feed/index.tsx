import { Head } from '@inertiajs/react';
import FeedLayout from '@/layouts/feed/feed-layout';

type FeedPageProps = {
    meta: {
        title: string;
        description: string;
    };
};

export default function FeedIndex({ meta }: FeedPageProps) {
    return (
        <FeedLayout>
            <Head title={meta.title} />

            <div className="flex flex-1 flex-col items-center justify-center gap-6 py-20 text-center">
                <div className="bg-lenflo-indigo/10 text-lenflo-indigo flex size-16 items-center justify-center rounded-2xl">
                    <svg
                        className="size-8"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    >
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </div>

                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Your learning feed
                    </h1>
                    <p className="max-w-sm text-muted-foreground">
                        Curated educational videos coming soon. Sign in to
                        personalise your experience.
                    </p>
                </div>

                <div className="flex gap-3">
                    <a
                        href="/login"
                        className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90"
                    >
                        Sign in
                    </a>
                    <a
                        href="/register"
                        className="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    >
                        Create account
                    </a>
                </div>
            </div>
        </FeedLayout>
    );
}

FeedIndex.layout = (page: React.ReactNode) => page;
