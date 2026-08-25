import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type AdminStats = {
    total_users: number;
    total_content: number;
    pending_review: number;
    published: number;
};

type Props = {
    stats: AdminStats;
};

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Admin Dashboard</h1>
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">Total Users</p>
                        <p className="text-2xl font-bold">{stats.total_users}</p>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">Total Content</p>
                        <p className="text-2xl font-bold">{stats.total_content}</p>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">Pending Review</p>
                        <p className="text-2xl font-bold">{stats.pending_review}</p>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">Published</p>
                        <p className="text-2xl font-bold">{stats.published}</p>
                    </div>
                </div>
                <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <a href="/admin/content" className="text-sm text-blue-600 hover:underline">
                        View all content &rarr;
                    </a>
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'Admin Dashboard', href: '/admin' }]}>
        {page}
    </AppLayout>
);
