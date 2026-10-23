import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

export default function AuthLayout({ children }: PropsWithChildren) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12">
            <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <Link href="/login" className="text-xl font-semibold tracking-tight text-slate-900">
                    Lead Management
                </Link>
                {children}
            </div>
        </main>
    );
}
