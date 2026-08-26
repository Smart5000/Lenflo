import { useCallback, useEffect, useRef, useState } from 'react';

let apiLoaded = false;
let apiLoading = false;
let apiReadyCallbacks: Array<() => void> = [];

function loadApi(): Promise<void> {
    if (apiLoaded) {
        return Promise.resolve();
    }

    if (apiLoading) {
        return new Promise((resolve) => {
            apiReadyCallbacks.push(resolve);
        });
    }

    apiLoading = true;

    return new Promise((resolve) => {
        window.onYouTubeIframeAPIReady = () => {
            apiLoaded = true;
            apiReadyCallbacks.forEach((cb) => cb());
            apiReadyCallbacks = [];
            resolve();
        };

        const script = document.createElement('script');
        script.src = 'https://www.youtube.com/iframe_api';
        script.async = true;
        document.head.appendChild(script);
    });
}

type PlayerState = 'idle' | 'loading' | 'ready' | 'playing' | 'paused' | 'error';

export function useYouTubePlayer(videoId: string | null) {
    const playerRef = useRef<YTPlayer | null>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    const [state, setState] = useState<PlayerState>('idle');
    const [muted, setMuted] = useState(true);
    const mountedRef = useRef(true);

    const destroyPlayer = useCallback(() => {
        if (playerRef.current) {
            try {
                playerRef.current.destroy();
            } catch {
                // ignore cleanup errors
            }

            playerRef.current = null;
        }
    }, []);

    const createPlayer = useCallback(
        async (id: string) => {
            if (!containerRef.current || playerRef.current) {
                return;
            }

            setState('loading');
            await loadApi();

            if (!mountedRef.current || !containerRef.current || !window.YT?.Player) {
                return;
            }

            try {
                const player = new window.YT.Player(containerRef.current, {
                    videoId: id,
                    playerVars: {
                        autoplay: 1,
                        mute: 1,
                        controls: 0,
                        rel: 0,
                        modestbranding: 1,
                        playsinline: 1,
                        enablejsapi: 1,
                        iv_load_policy: 3,
                        fs: 0,
                        cc_load_policy: 0,
                        disablekb: 1,
                    },
                    events: {
                        onReady: () => {
                            if (!mountedRef.current) {
                                return;
                            }

                            playerRef.current = player;
                            setState('ready');
                            setMuted(true);
                        },
                        onStateChange: (event) => {
                            if (!mountedRef.current) {
                                return;
                            }

                            const ytState = event.data;

                            if (ytState === window.YT!.PlayerState.PLAYING) {
                                setState('playing');
                                setMuted(player.isMuted());
                            } else if (ytState === window.YT!.PlayerState.PAUSED) {
                                setState('paused');
                            } else if (ytState === window.YT!.PlayerState.ENDED) {
                                setState('paused');
                            }
                        },
                        onError: () => {
                            if (mountedRef.current) {
                                setState('error');
                            }
                        },
                    },
                });
            } catch {
                if (mountedRef.current) {
                    setState('error');
                }
            }
        },
        [],
    );

    const play = useCallback(() => {
        const player = playerRef.current;

        if (!player) {
            return;
        }

        try {
            player.playVideo();
        } catch {
            // ignore if player not ready
        }
    }, []);

    const pause = useCallback(() => {
        const player = playerRef.current;

        if (!player) {
            return;
        }

        try {
            player.pauseVideo();
        } catch {
            // ignore
        }
    }, []);

    const toggleMute = useCallback(() => {
        const player = playerRef.current;

        if (!player) {
            return;
        }

        try {
            if (player.isMuted()) {
                player.unMute();
                setMuted(false);
            } else {
                player.mute();
                setMuted(true);
            }
        } catch {
            // ignore
        }
    }, []);

    const unmute = useCallback(() => {
        const player = playerRef.current;

        if (!player) {
            return;
        }

        try {
            player.unMute();
            setMuted(false);
        } catch {
            // ignore
        }
    }, []);

    // Create/destroy player based on videoId
    useEffect(() => {
        mountedRef.current = true;

        if (videoId) {
            createPlayer(videoId);
        }

        return () => {
            mountedRef.current = false;
            destroyPlayer();
        };
    }, [videoId, createPlayer, destroyPlayer]);

    // Handle tab visibility
    useEffect(() => {
        function handleVisibilityChange() {
            if (document.hidden && playerRef.current) {
                try {
                    playerRef.current.pauseVideo();
                } catch {
                    // ignore
                }
            }
        }

        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, []);

    return {
        containerRef,
        state,
        muted,
        play,
        pause,
        toggleMute,
        unmute,
    };
}
