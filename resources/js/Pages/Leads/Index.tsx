import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AppLayout from '../../Components/AppLayout';
import LeadStatusControl from '../../Components/LeadStatusControl';
import type { Lead, LeadFilters, LeadStatusOption, Pagination, SharedProps } from '../../types';

type LeadsIndexProps = SharedProps & {
    leads: Pagination<Lead>;
    filters: LeadFilters;
    statuses: LeadStatusOption[];
};

export default function Index({ leads, filters, statuses }: LeadsIndexProps) {
    const filterForm = useForm<LeadFilters>({ ...filters, status: filters.status ?? '' });

    function applyFilters(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        filterForm.post('/leads/filters', { preserveScroll: true });
    }

    function deleteLead(lead: Lead) {
        if (window.confirm(`Delete ${lead.name}? This action cannot be undone.`)) {
            router.delete(`/leads/${lead.id}`, { preserveScroll: true });
        }
    }

    return (
        <AppLayout>
            <Head title="Leads" />
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight text-slate-900">Lead List</h1>
                    <p className="mt-1 text-sm text-slate-500">Manage and track your potential customers.</p>
                </div>
                <Link href="/leads/create" className="button-primary self-start sm:self-auto">
                    <span aria-hidden="true" className="mr-2 text-lg leading-none">+</span>
                    Add lead
                </Link>
            </div>

            <section aria-label="Lead list" className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <form onSubmit={applyFilters} className="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-end">
                    <label className="relative block min-w-0 flex-1 sm:max-w-sm">
                        <span className="sr-only">Search by name or email</span>
                        <span aria-hidden="true" className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">⌕</span>
                        <input
                            type="search"
                            value={filterForm.data.search}
                            maxLength={100}
                            onChange={(event) => filterForm.setData('search', event.target.value)}
                            placeholder="Search by name or email"
                            className="field-input pl-9"
                        />
                    </label>
                    <label>
                        <span className="sr-only">Filter by status</span>
                        <select
                            value={filterForm.data.status ?? ''}
                            onChange={(event) => filterForm.setData('status', event.target.value as LeadFilters['status'])}
                            className="field-select"
                        >
                            <option value="">All statuses</option>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>{status.label}</option>
                            ))}
                        </select>
                    </label>
                    <label>
                        <span className="sr-only">Sort by created date</span>
                        <select
                            value={filterForm.data.sort}
                            onChange={(event) => filterForm.setData('sort', event.target.value as 'asc' | 'desc')}
                            className="field-select"
                        >
                            <option value="desc">Newest first</option>
                            <option value="asc">Oldest first</option>
                        </select>
                    </label>
                    <button type="submit" disabled={filterForm.processing} className="button-secondary">
                        {filterForm.processing ? 'Applying…' : 'Apply'}
                    </button>
                </form>

                <div className="overflow-x-auto">
                    <table className="w-full min-w-[760px] text-left text-sm">
                        <thead className="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" className="px-5 py-3">Name</th>
                                <th scope="col" className="px-5 py-3">Email address</th>
                                <th scope="col" className="px-5 py-3">Phone number</th>
                                <th scope="col" className="px-5 py-3">Status</th>
                                <th scope="col" className="px-5 py-3">Created</th>
                                <th scope="col" className="px-5 py-3"><span className="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {leads.data.map((lead) => (
                                <tr key={lead.id} className="hover:bg-slate-50/70">
                                    <td className="px-5 py-4 font-medium text-slate-900">{lead.name}</td>
                                    <td className="px-5 py-4 text-slate-600">{lead.email}</td>
                                    <td className="px-5 py-4 text-slate-600">{lead.phone}</td>
                                    <td className="px-5 py-4">
                                        <LeadStatusControl
                                            leadId={lead.id}
                                            name={lead.name}
                                            status={lead.status}
                                            statuses={statuses}
                                        />
                                    </td>
                                    <td className="px-5 py-4 text-slate-500">
                                        <time dateTime={lead.created_at}>
                                            {new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(lead.created_at))}
                                        </time>
                                    </td>
                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-3">
                                            <Link
                                                href={`/leads/${lead.id}/edit`}
                                                className="font-medium text-slate-600 hover:text-slate-950"
                                            >
                                                Edit<span className="sr-only"> {lead.name}</span>
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => deleteLead(lead)}
                                                className="font-medium text-red-600 hover:text-red-800"
                                            >
                                                Delete<span className="sr-only"> {lead.name}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {leads.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-5 py-16 text-center">
                                        <p className="font-medium text-slate-800">No leads found</p>
                                        <p className="mt-1 text-sm text-slate-500">
                                            {filters.search || filters.status
                                                ? 'Try adjusting your search or filters.'
                                                : 'Add your first lead to get started.'}
                                        </p>
                                        {!filters.search && !filters.status && (
                                            <Link href="/leads/create" className="mt-4 inline-block text-sm font-medium text-slate-900 underline">
                                                Add a lead
                                            </Link>
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Pagination" className="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-sm text-slate-500">
                        {leads.from === null ? 'No results' : `Showing ${leads.from}–${leads.to} of ${leads.total} leads`}
                    </p>
                    <div className="flex flex-wrap items-center justify-center gap-1">
                        {leads.links.map((link, index) => (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url ?? '#'}
                                preserveScroll
                                aria-current={link.active ? 'page' : undefined}
                                aria-disabled={link.url === null}
                                className={`min-w-9 rounded-md px-3 py-2 text-center text-sm ${
                                    link.active
                                        ? 'bg-slate-900 font-medium text-white'
                                        : link.url
                                          ? 'text-slate-600 hover:bg-slate-100'
                                          : 'cursor-not-allowed text-slate-300'
                                }`}
                                onClick={(event) => {
                                    if (!link.url) event.preventDefault();
                                }}
                            >
                                {link.label.replace('&laquo; ', '').replace(' &raquo;', '')}
                            </Link>
                        ))}
                    </div>
                </nav>
            </section>
        </AppLayout>
    );
}
