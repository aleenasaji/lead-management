import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';
import FieldError from '../../Components/FieldError';

export default function Login() {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post('/login');
    }

    return (
        <AuthLayout>
            <Head title="Sign in" />
            <p className="mt-2 text-sm text-slate-600">Sign in to manage your leads.</p>
            <form onSubmit={submit} className="mt-7 space-y-5">
                <div>
                    <label htmlFor="email" className="field-label">Email address</label>
                    <input
                        id="email"
                        type="email"
                        autoComplete="email"
                        autoFocus
                        required
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        className="field-input"
                    />
                    <FieldError message={form.errors.email} />
                </div>
                <div>
                    <label htmlFor="password" className="field-label">Password</label>
                    <input
                        id="password"
                        type="password"
                        autoComplete="current-password"
                        required
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        className="field-input"
                    />
                    <FieldError message={form.errors.password} />
                </div>
                <label className="flex items-center gap-2 text-sm text-slate-600">
                    <input
                        type="checkbox"
                        checked={form.data.remember}
                        onChange={(event) => form.setData('remember', event.target.checked)}
                        className="size-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                    />
                    Remember me
                </label>
                <button type="submit" disabled={form.processing} className="button-primary w-full">
                    {form.processing ? 'Signing in…' : 'Sign in'}
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-600">
                New here? <Link href="/register" className="font-medium text-slate-900 underline">Create an account</Link>
            </p>
        </AuthLayout>
    );
}
