export type LeadStatus = 'New' | 'Contacted' | 'Converted';

export type LeadStatusOption = {
    value: LeadStatus;
    label: string;
};

export type Lead = {
    id: number;
    name: string;
    email: string;
    phone: string;
    status: LeadStatus;
    created_at: string;
};

export type LeadFilters = {
    search: string;
    status: LeadStatus | '' | null;
    sort: 'asc' | 'desc';
};

export type Pagination<T> = {
    data: T[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

export type SharedProps = {
    auth: {
        user: {
            name: string;
            email: string;
        } | null;
    };
    flash: {
        success: string | null;
    };
};
