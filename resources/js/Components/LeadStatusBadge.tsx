import type { LeadStatus } from '../types';

const statusStyles: Record<LeadStatus, string> = {
    New: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    Contacted: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    Converted: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
};

type LeadStatusBadgeProps = {
    status: LeadStatus;
};

export default function LeadStatusBadge({ status }: LeadStatusBadgeProps) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status]}`}>
            {status}
        </span>
    );
}
