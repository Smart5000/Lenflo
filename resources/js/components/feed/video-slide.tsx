import { Heart, Bookmark, Share2, Play, VolumeX } from 'lucide-react';
import { useCallback, useEffect, useRef } from 'react';
import { useYouTubePlayer } from '@/hooks/use-youtube-player';
import { cn } from '@/lib/utils';

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

type Props = {
    item: ContentItem;
    isActive: boolean;
    isLiked: boolean;
    isSaved: boolean;
    onLike: (id: number) => void;
    onSave: (id: number) => void;
    onShare: (item: ContentItem) => void;
};

function formatDuration(seconds: number | null): string {
    if (!seconds) {
        return '';
    }

    const m = Math.floor(seconds / 60);
    const s = seconds % 60;

    return `${m}:${s.toString().padStart(2, '0')}`;
}

function getYouTubeId(url: string): string | null {
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/embed\/)([^?&]+)/);

    return match ? match[1] : null;
}

const DOUBLE_TAP_DELAY = 300;

export default function VideoSlide({ item, isActive, isLiked, isSaved, onLike, onSave, onShare }: Props) {
    const videoId = getYouTubeId(item.embed_url);
    const { containerRef, state, muted, play, unmute } = useYouTubePlayer(isActive ? videoId : null);
    const lastTapRef = useRef<number>(0);

    // Autoplay when player becomes ready
    useEffect(() => {
        if (state === 'ready' || state === 'playing') {
            play();
        }
    }, [state, play]);

    const handleLike = useCallback(() => {
        onLike(item.id);
    }, [onLike, item.id]);

    const handleSave = useCallback(() => {
        onSave(item.id);
    }, [onSave, item.id]);

    const handleShare = useCallback(() => {
        onShare(item);
    }, [onShare, item]);

    const handleDoubleTap = useCallback(() => {
        const now = Date.now();

        if (now - lastTapRef.current < DOUBLE_TAP_DELAY) {
            if (!isLiked) {
                onLike(item.id);
            }

            lastTapRef.current = 0;
        } else {
            lastTapRef.current = now;
        }
    }, [isLiked, onLike, item.id]);

    const handleUnmute = useCallback(() => {
        unmute();
    }, [unmute]);

    return (
        <section
            className="relative flex h-[100dvh] w-full snap-start snap-always items-center justify-center bg-black"
            aria-label={`Video: ${item.title}`}
        >
            {/* Video / Embed */}
            <div
                className="absolute inset-0 flex items-center justify-center"
                onDoubleClick={handleDoubleTap}
            >
                {videoId && isActive ? (
                    <div className="h-full w-full">
                        <div ref={containerRef} className="h-full w-full" />
                        {/* Autoplay error fallback: show play button */}
                        {state === 'error' && (
                            <button
                                onClick={play}
                                className="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors hover:bg-black/40"
                                aria-label={`Play ${item.title}`}
                            >
                                <div className="flex size-16 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur-sm transition-transform hover:scale-105">
                                    <Play className="size-7 ml-1 text-black" fill="black" />
                                </div>
                            </button>
                        )}
                        {/* Unmute overlay when muted */}
                        {state === 'playing' && muted && (
                            <button
                                onClick={handleUnmute}
                                className="absolute inset-0 flex items-center justify-center bg-transparent"
                                aria-label="Unmute video"
                            >
                                <div className="flex items-center gap-2 rounded-full bg-black/60 px-4 py-2 text-sm text-white backdrop-blur-sm transition-colors hover:bg-black/80">
                                    <VolumeX className="size-4" />
                                    <span>Tap to unmute</span>
                                </div>
                            </button>
                        )}
                    </div>
                ) : item.thumbnail_url ? (
                    <div className="relative h-full w-full">
                        <img
                            src={item.thumbnail_url}
                            alt={item.title}
                            className="h-full w-full object-cover"
                            loading={isActive ? 'eager' : 'lazy'}
                        />
                    </div>
                ) : (
                    <div className="flex flex-col items-center gap-4 text-white/60">
                        <Play className="size-12" />
                        <p className="text-sm">Video unavailable</p>
                    </div>
                )}
            </div>

            {/* Gradient overlays for readability */}
            <div className="pointer-events-none absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-black/60 to-transparent" />
            <div className="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/80 via-black/40 to-transparent" />

            {/* Top bar: title */}
            <div className="pointer-events-none absolute inset-x-0 top-0 z-10 p-4 pt-5">
                <h2 className="line-clamp-2 text-base font-semibold text-white drop-shadow-lg">
                    {item.title}
                </h2>
                {item.duration_seconds && (
                    <span className="mt-1 inline-block rounded bg-black/50 px-1.5 py-0.5 text-xs text-white/80">
                        {formatDuration(item.duration_seconds)}
                    </span>
                )}
            </div>

            {/* Right side: engagement actions */}
            <div className="absolute bottom-24 right-3 z-10 flex flex-col items-center gap-5 sm:right-5">
                <EngagementButton
                    icon={Heart}
                    activeIcon={Heart}
                    count={item.likes_count + (isLiked ? 1 : 0)}
                    active={isLiked}
                    activeColor="text-red-500"
                    label="Like"
                    onClick={handleLike}
                />
                <EngagementButton
                    icon={Bookmark}
                    activeIcon={Bookmark}
                    count={item.saves_count + (isSaved ? 1 : 0)}
                    active={isSaved}
                    activeColor="text-amber-400"
                    label="Save"
                    onClick={handleSave}
                />
                <EngagementButton
                    icon={Share2}
                    activeIcon={Share2}
                    count={null}
                    active={false}
                    activeColor=""
                    label="Share"
                    onClick={handleShare}
                />
            </div>

            {/* Bottom: categories */}
            {item.categories.length > 0 && (
                <div className="absolute bottom-4 left-4 z-10 flex flex-wrap gap-1.5 sm:left-5">
                    {item.categories.slice(0, 3).map((cat) => (
                        <span
                            key={cat.id}
                            className="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium text-white backdrop-blur-sm"
                        >
                            {cat.name}
                        </span>
                    ))}
                </div>
            )}
        </section>
    );
}

function EngagementButton({
    icon: Icon,
    activeIcon: ActiveIcon,
    count,
    active,
    activeColor,
    label,
    onClick,
}: {
    icon: React.ComponentType<{ className?: string; fill?: string }>;
    activeIcon: React.ComponentType<{ className?: string; fill?: string }>;
    count: number | null;
    active: boolean;
    activeColor: string;
    label: string;
    onClick: () => void;
}) {
    const Comp = active ? ActiveIcon : Icon;

    return (
        <button
            onClick={onClick}
            className={cn(
                'flex flex-col items-center gap-1 text-white/80 transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent rounded-lg p-1',
                active && activeColor,
            )}
            aria-label={label}
            aria-pressed={active}
        >
            <Comp
                className={cn('size-7 transition-transform', active && 'scale-110')}
                fill={active ? 'currentColor' : 'none'}
            />
            {count !== null && (
                <span className="text-[11px] font-medium tabular-nums">
                    {count > 0 ? count.toLocaleString() : ''}
                </span>
            )}
        </button>
    );
}
