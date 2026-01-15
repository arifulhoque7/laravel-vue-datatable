export interface DataTableColumn<T = Record<string, unknown>> {
    key: keyof T | string;
    label: string;
    sortable?: boolean;
    searchable?: boolean;
    class?: string;
    headerClass?: string;
    render?: (value: unknown, row: T) => string;
}

export interface DataTablePagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export interface DataTableSort {
    column: string;
    direction: 'asc' | 'desc';
}

export interface DataTableFilters {
    search?: string;
    sort?: DataTableSort;
    per_page?: number;
    page?: number;
    [key: string]: unknown;
}

export interface DataTableResponse<T> {
    data: T[];
    pagination: DataTablePagination;
    filters: DataTableFilters;
}

export interface DataTableProps<T> {
    columns: DataTableColumn<T>[];
    data: T[];
    pagination: DataTablePagination;
    filters?: DataTableFilters;
    loading?: boolean;
    searchable?: boolean;
    perPageOptions?: number[];
    emptyMessage?: string;
}
