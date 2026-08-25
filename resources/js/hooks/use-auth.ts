import { router, usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import type { Auth } from '@/types/auth';

export function useAuth() {
    const { auth } = usePage().props;
    const user = auth?.user;

    const requireAuth = useCallback(
        (callback: () => void, redirectTo?: string) => {
            if (user) {
                callback();
            } else {
                const currentUrl = redirectTo || window.location.pathname;
                router.visit(
                    `/login?redirect=${encodeURIComponent(currentUrl)}`,
                );
            }
        },
        [user],
    );

    return {
        user,
        isAuthenticated: !!user,
        requireAuth,
    };
}
