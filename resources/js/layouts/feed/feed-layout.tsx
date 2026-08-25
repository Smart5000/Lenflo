import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';

type Props = {
    children: ReactNode;
};

export default function FeedLayout({ children }: Props) {
    return (
        <div className="flex min-h-screen flex-col bg-background">
            <header className="sticky top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-md">
                <div className="mx-auto flex h-14 max-w-2xl items-center justify-between px-4">
                    <Link href="/" className="flex items-center gap-2" prefetch>
                        <AppLogoIcon className="size-7" />
                        <span className="text-lg font-semibold tracking-tight">
                            Lenflo
                        </span>
                    </Link>
                    <nav className="flex items-center gap-3 text-sm">
                        <Link
                            href="/"
                            className="text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Feed
                        </Link>
                        <Link
                            href="/login"
                            className="rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground transition-opacity hover:opacity-90"
                        >
                            Sign in
                        </Link>
                    </nav>
                </div>
            </header>

            <main className="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center px-4 py-8">
                {children}
            </main>

            <footer className="border-t border-border/50 py-6 text-center text-xs text-muted-foreground">
                Lenflo — Learning Flow
            </footer>
        </div>
    );
}
