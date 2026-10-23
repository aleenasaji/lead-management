import { useForm } from '@inertiajs/react';
import FieldError from './FieldError';
import LeadStatusBadge from './LeadStatusBadge';
import type { LeadStatus, LeadStatusOption } from '../types';

type LeadStatusControlProps = {
    leadId: number;
    name: string;
    status: LeadStatus;
    statuses: LeadStatusOption[];
};

export default function LeadStatusControl({ leadId, name, status, statuses }: LeadStatusControlProps) {
    const form = useForm({ status });

    function saveStatus() {
        form.patch(`/leads/${leadId}/status`, {
            preserveScroll: true,
            onSuccess: () => form.setDefaults(),
        });
    }

    return (
        <div className="flex min-w-36 flex-col items-start gap-2">
            <LeadStatusBadge status={status} />
            <div className="flex items-center gap-2">
                <label className="sr-only" htmlFor={`lead-${leadId}-status`}>Status for {name}</label>
                <select
                    id={`lead-${leadId}-status`}
                    value={form.data.status}
                    disabled={form.processing}
                    onChange={(event) => form.setData('status', event.target.value as LeadStatus)}
                    className="field-select min-w-32"
                    aria-invalid={Boolean(form.errors.status)}
                >
                    {statuses.map((option) => (
                        <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                </select>
                {form.isDirty && (
                    <button
                        type="button"
                        onClick={saveStatus}
                        disabled={form.processing}
                        className="text-sm font-medium text-slate-700 underline decoration-slate-300 underline-offset-2 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {form.processing ? 'Saving…' : 'Save'}
                    </button>
                )}
            </div>
            <FieldError message={form.errors.status} />
        </div>
    );
}
