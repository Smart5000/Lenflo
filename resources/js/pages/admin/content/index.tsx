import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type Category = {
    id: number;
    name: string;
};

type ContentItem = {
    id: number;
    title: string;
    provider: string;
    moderation_status: string;
    thumbnail_url: string | null;
    created_at: string;
    categories: Category[];
};

type PaginatedContent = {
    data: ContentItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type Props = {
    contentItems: PaginatedContent;
};

const STATUS_COLORS: Record<string, string> = {
    candidate: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    pending_review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    published: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
    disabled: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

export default function ContentIndex({ contentItems }: Props) {
    return (
        <>
            <Head title="Content Management" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Content Management</h1>
                <p className="text-sm text-muted-foreground">
                    {contentItems.total} total items
                </p>
                <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b border-sidebar-border/70 dark:border-sidebar-border">
                                <th className="p-3 text-left text-sm font-medium">Title</th>
                                <th className="p-3 text-left text-sm font-medium">Provider</th>
                                <th className="p-3 text-left text-sm font-medium">Status</th>
                                <th className="p-3 text-left text-sm font-medium">Categories</th>
                                <th className="p-3 text-left text-sm font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {contentItems.data.map((item) => (
                                <tr key={item.id} className="border-b border-sidebar-border/70 dark:border-sidebar-border">
                                    <td className="p-3">
                                        <a
                                            href={`/admin/content/${item.id}/review`}
                                            className="text-sm text-blue-600 hover:underline"
                                        >
                                            {item.title}
                                        </a>
                                    </td>
                                    <td className="p-3 text-sm">{item.provider}</td>
                                    <td className="p-3">
                                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_COLORS[item.moderation_status] ?? ''}`}>
                                            {item.moderation_status.replace('_', ' ')}
                                        </span>
                                    </td>
                                    <td className="p-3 text-sm">
                                        {item.categories.map((c) => c.name).join(', ') || '—'}
                                    </td>
                                    <td className="p-3">
                                        <a
                                            href={`/admin/content/${item.id}/review`}
                                            className="text-sm text-blue-600 hover:underline"
                                        >
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

ContentIndex.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: 'Content', href: '/admin/content' }]}>
        {page}
    </AppLayout>
);
