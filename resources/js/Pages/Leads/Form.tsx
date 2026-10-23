import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AppLayout from '../../Components/AppLayout';
import FieldError from '../../Components/FieldError';
import type { Lead, LeadStatus, LeadStatusOption, SharedProps } from '../../types';

type LeadFormData = {
    name: string;
    email: string;
    phone: string;
    status: LeadStatus;
};

type LeadFormProps = SharedProps & {
    lead: Pick<Lead, 'id' | 'name' | 'email' | 'phone' | 'status'> | null;
    statuses: LeadStatusOption[];
};

export default function LeadForm({ lead, statuses }: LeadFormProps) {
    const form = useForm<LeadFormData>({
        name: lead?.name ?? '',
        email: lead?.email ?? '',
        phone: lead?.phone ?? '',
        status: lead?.status ?? 'New',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (lead) {
            form.put(`/leads/${lead.id}`);
        } else {
            form.post('/leads');
        }
    }

    return (
        <AppLayout>
            <Head title={lead ? 'Edit lead' : 'Add lead'} />
            <div className="mx-auto max-w-3xl">
                <div className="mb-6">
                    <Link href="/leads" className="text-sm font-medium text-slate-500 hover:text-slate-900">
                        ← Back to leads
                    </Link>
                    <h1 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        {lead ? 'Edit lead' : 'Add a lead'}
                    </h1>
                </div>
                <form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label htmlFor="name" className="field-label">Name</label>
                            <input
                                id="name"
                                autoComplete="name"
                                required
                                maxLength={150}
                                value={form.data.name}
                                onChange={(event) => form.setData('name', event.target.value)}
                                className="field-input"
                                aria-invalid={Boolean(form.errors.name)}
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
                                maxLength={255}
                                value={form.data.email}
                                onChange={(event) => form.setData('email', event.target.value)}
                                className="field-input"
                                aria-invalid={Boolean(form.errors.email)}
                            />
                            <FieldError message={form.errors.email} />
                        </div>
                        <div>
                            <label htmlFor="phone" className="field-label">Phone number</label>
                            <input
                                id="phone"
                                type="tel"
                                autoComplete="tel"
                                required
                                maxLength={30}
                                pattern="\\+?[0-9][0-9\\s().-]*[0-9]"
                                title="Enter 7 to 15 digits. You may use a leading +, spaces, parentheses, dots, or hyphens."
                                value={form.data.phone}
                                onChange={(event) => form.setData('phone', event.target.value)}
                                className="field-input"
                                aria-invalid={Boolean(form.errors.phone)}
                            />
                            <FieldError message={form.errors.phone} />
                        </div>
                        <div>
                            <label htmlFor="status" className="field-label">Status</label>
                            <select
                                id="status"
                                value={form.data.status}
                                onChange={(event) => form.setData('status', event.target.value as LeadStatus)}
                                className="field-input"
                                aria-invalid={Boolean(form.errors.status)}
                            >
                                {statuses.map((status) => (
                                    <option key={status.value} value={status.value}>{status.label}</option>
                                ))}
                            </select>
                            <FieldError message={form.errors.status} />
                        </div>
                    </div>
                    <div className="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <Link href="/leads" className="button-secondary text-center">Cancel</Link>
                        <button type="submit" disabled={form.processing} className="button-primary">
                            {form.processing ? 'Saving…' : lead ? 'Save changes' : 'Create lead'}
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
