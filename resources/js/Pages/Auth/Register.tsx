import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';
import FieldError from '../../Components/FieldError';

export default function Register() {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post('/register');
    }

    return (
        <AuthLayout>
            <Head title="Create account" />
            <p className="mt-2 text-sm text-slate-600">Create an account to get started.</p>
            <form onSubmit={submit} className="mt-7 space-y-5">
                <div>
                    <label htmlFor="name" className="field-label">Name</label>
                    <input
                        id="name"
                        autoComplete="name"
                        required
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        className="field-input"
                    />
                    <FieldError message={form.errors.name} />
                </div>
                <div>
                    <label htmlFor="email" className="field-label">Email address</label>
                    <input
                        id="email"
                        type="email"
                        autoComplete="email"
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
                        autoComplete="new-password"
                        required
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        className="field-input"
                    />
                    <FieldError message={form.errors.password} />
                </div>
                <div>
                    <label htmlFor="password_confirmation" className="field-label">Confirm password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        required
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        className="field-input"
                    />
                </div>
                <button type="submit" disabled={form.processing} className="button-primary w-full">
                    {form.processing ? 'Creating account…' : 'Create account'}
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-600">
                Already registered? <Link href="/login" className="font-medium text-slate-900 underline">Sign in</Link>
            </p>
        </AuthLayout>
    );
}
