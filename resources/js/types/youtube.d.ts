 

type YTPlayerState = -1 | 0 | 1 | 2 | 3 | 5;

interface YTPlayerEvent {
    target: YT.Player;
    data: number;
}

interface YTPlayer {
    playVideo(): void;
    pauseVideo(): void;
    stopVideo(): void;
    mute(): void;
    unMute(): void;
    isMuted(): boolean;
    getVolume(): number;
    setVolume(volume: number): void;
    destroy(): void;
    getDuration(): number;
    getCurrentTime(): number;
    seekTo(seconds: number, allowSeekAhead: boolean): void;
    getPlayerState(): YTPlayerState;
    getVideoData(): { video_id: string; title: string };
}

interface YTPlayerOptions {
    height?: string | number;
    width?: string | number;
    videoId?: string;
    playerVars?: Record<string, any>;
    events?: {
        onReady?: (event: YTPlayerEvent) => void;
        onStateChange?: (event: YTPlayerEvent) => void;
        onError?: (event: YTPlayerEvent) => void;
        onPlaybackQualityChange?: (event: YTPlayerEvent) => void;
        onPlaybackRateChange?: (event: YTPlayerEvent) => void;
    };
}

interface YTStatic {
    Player: new (elementId: string | HTMLElement, options: YTPlayerOptions) => YTPlayer;
    PlayerState: {
        UNSTARTED: -1;
        ENDED: 0;
        PLAYING: 1;
        PAUSED: 2;
        BUFFERING: 3;
        CUED: 5;
    };
}

interface Window {
    YT?: YTStatic;
    onYouTubeIframeAPIReady?: () => void;
}
