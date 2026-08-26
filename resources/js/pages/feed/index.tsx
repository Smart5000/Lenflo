import { Head } from '@inertiajs/react';
import { LogIn } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import VideoSlide from '@/components/feed/video-slide';
import { useAuth } from '@/hooks/use-auth';
import FeedLayout from '@/layouts/feed/feed-layout';

type Category = {
    id: number;
    name: string;
    slug: string;
};

type ContentItem = {
    id: number;
    title: string;
    description: string | null;
    provider: string;
    external_id: string;
    embed_url: string;
    thumbnail_url: string | null;
    duration_seconds: number | null;
    categories: Category[];
    likes_count: number;
    saves_count: number;
};

type FeedPageProps = {
    contentItems: ContentItem[];
    userLikes: Record<number, boolean>;
    userSaves: Record<number, boolean>;
    meta: {
        title: string;
        description: string;
    };
};

export default function FeedIndex({ contentItems, userLikes, userSaves, meta }: FeedPageProps) {
    const { isAuthenticated, requireAuth } = useAuth();
    const [activeIndex, setActiveIndex] = useState(0);
    const [likes, setLikes] = useState<Record<number, boolean>>(userLikes);
    const [saves, setSaves] = useState<Record<number, boolean>>(userSaves);
    const containerRef = useRef<HTMLDivElement>(null);
    const slideRefs = useRef<(HTMLElement | null)[]>([]);

    // IntersectionObserver to track active slide
    useEffect(() => {
        const container = containerRef.current;

        if (!container) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const idx = slideRefs.current.indexOf(entry.target as HTMLElement);

                        if (idx !== -1) {
                            setActiveIndex(idx);
                        }
                    }
                });
            },
            {
                root: container,
                threshold: 0.6,
            },
        );

        slideRefs.current.forEach((el) => {
            if (el) {
                observer.observe(el);
            }
        });

        return () => observer.disconnect();
    }, [contentItems.length]);

    // Keyboard navigation
    useEffect(() => {
        function handleKeyDown(e: KeyboardEvent) {
            if (e.key === 'ArrowDown' || e.key === 'j') {
                e.preventDefault();
                const next = Math.min(activeIndex + 1, contentItems.length - 1);
                slideRefs.current[next]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else if (e.key === 'ArrowUp' || e.key === 'k') {
                e.preventDefault();
                const prev = Math.max(activeIndex - 1, 0);
                slideRefs.current[prev]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [activeIndex, contentItems.length]);

    const handleLike = useCallback(
        (id: number) => {
            requireAuth(() => {
                const previousLiked = !!likes[id];
                setLikes((prev) => ({ ...prev, [id]: !prev[id] }));

                fetch(`/feed/${id}/like`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': decodeURIComponent(
                            document.cookie
                                .split('; ')
                                .find((c) => c.startsWith('XSRF-TOKEN='))
                                ?.split('=')[1] ?? '',
                        ),
                    },
                }).then((response) => {
                    if (!response.ok) {
                        setLikes((prev) => ({ ...prev, [id]: previousLiked }));
                        toast.error('Could not update like. Please try again.');
                    }
                }).catch(() => {
                    setLikes((prev) => ({ ...prev, [id]: previousLiked }));
                    toast.error('Could not update like. Please try again.');
                });
            });
        },
        [requireAuth, likes],
    );

    const handleSave = useCallback(
        (id: number) => {
            requireAuth(() => {
                const previousSaved = !!saves[id];
                setSaves((prev) => ({ ...prev, [id]: !prev[id] }));

                fetch(`/feed/${id}/save`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': decodeURIComponent(
                            document.cookie
                                .split('; ')
                                .find((c) => c.startsWith('XSRF-TOKEN='))
                                ?.split('=')[1] ?? '',
                        ),
                    },
                }).then((response) => {
                    if (!response.ok) {
                        setSaves((prev) => ({ ...prev, [id]: previousSaved }));
                        toast.error('Could not update save. Please try again.');
                    }
                }).catch(() => {
                    setSaves((prev) => ({ ...prev, [id]: previousSaved }));
                    toast.error('Could not update save. Please try again.');
                });
            });
        },
        [requireAuth, saves],
    );

    const handleShare = useCallback(
        (item: ContentItem) => {
            const shareData = { title: item.title, url: window.location.href };

            const trackShare = () => {
                fetch(`/feed/${item.id}/share`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': decodeURIComponent(
                            document.cookie
                                .split('; ')
                                .find((c) => c.startsWith('XSRF-TOKEN='))
                                ?.split('=')[1] ?? '',
                        ),
                    },
                }).catch(() => {});
            };

            if (navigator.share) {
                navigator.share(shareData).then(trackShare).catch(() => {});
            } else {
                navigator.clipboard.writeText(shareData.url).then(() => {
                    trackShare();
                    toast.success('Link copied to clipboard');
                }).catch(() => {
                    toast.error('Could not copy link');
                });
            }
        },
        [],
    );

    const setSlideRef = useCallback((index: number) => (el: HTMLElement | null) => {
        slideRefs.current[index] = el;
    }, []);

    if (contentItems.length === 0) {
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

    return (
        <>
            <Head title={meta.title} />

            {/* Full-viewport scroll-snap container */}
            <div
                ref={containerRef}
                className="h-[100dvh] overflow-y-scroll snap-y snap-mandatory scroll-smooth"
                role="feed"
                aria-label="Learning feed"
                aria-busy={false}
            >
                {contentItems.map((item, index) => (
                    <div
                        key={item.id}
                        ref={setSlideRef(index)}
                        className="snap-start snap-always"
                    >
                        <VideoSlide
                            item={item}
                            isActive={index === activeIndex}
                            isLiked={!!likes[item.id]}
                            isSaved={!!saves[item.id]}
                            onLike={handleLike}
                            onSave={handleSave}
                            onShare={handleShare}
                        />
                    </div>
                ))}
            </div>

            {/* Floating sign-in for guests */}
            {!isAuthenticated && (
                <a
                    href="/login"
                    className="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-full bg-white/90 px-5 py-2.5 text-sm font-semibold text-black shadow-lg backdrop-blur-sm transition-all hover:bg-white hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                >
                    <LogIn className="mr-2 inline-block size-4" />
                    Sign in
                </a>
            )}

            {/* Keyboard hint (desktop only) */}
            <div className="pointer-events-none fixed bottom-6 right-6 z-40 hidden items-center gap-1 text-[11px] text-white/40 sm:flex">
                <kbd className="rounded border border-white/20 bg-white/10 px-1.5 py-0.5 font-mono">↑</kbd>
                <kbd className="rounded border border-white/20 bg-white/10 px-1.5 py-0.5 font-mono">↓</kbd>
                <span className="ml-1">navigate</span>
            </div>
        </>
    );
}

FeedIndex.layout = (page: React.ReactNode) => page;
