import { Link, router, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import type { SharedProps } from '../types';

export default function AppLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<SharedProps>().props;
    const initials = auth.user?.name
        .split(/\s+/)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    function signOut() {
        if (window.confirm('Are you sure you want to sign out?')) {
            router.post('/logout');
        }
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href="/leads" className="text-lg font-semibold tracking-tight text-slate-900">
                        Lead List
                    </Link>
                    <div className="flex items-center gap-4">
                        <span className="hidden text-sm text-slate-600 sm:inline">{auth.user?.name}</span>
                        <span
                            aria-label={`${auth.user?.name ?? 'User'} account`}
                            className="flex size-9 items-center justify-center rounded-full bg-emerald-500 text-sm font-semibold text-white"
                        >
                            {initials}
                        </span>
                        <button
                            type="button"
                            onClick={signOut}
                            className="text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            Sign out
                        </button>
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {flash.success && (
                    <div
                        role="status"
                        className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        {flash.success}
                    </div>
                )}
                {children}
            </main>
        </div>
    );
}
