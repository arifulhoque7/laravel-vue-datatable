import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { DataTableFilters, DataTableSort } from '@/types/datatable';

export function useDataTable(routeName: string, initialFilters: DataTableFilters = {}) {
    const loading = ref(false);
    const filters = ref<DataTableFilters>({
        search: initialFilters.search || '',
        sort: initialFilters.sort || undefined,
        per_page: initialFilters.per_page || 10,
        page: initialFilters.page || 1,
    });

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    const fetchData = (preserveScroll = true) => {
        loading.value = true;

        const params: Record<string, unknown> = {};

        if (filters.value.search) {
            params.search = filters.value.search;
        }

        if (filters.value.sort) {
            params.sort_by = filters.value.sort.column;
            params.sort_direction = filters.value.sort.direction;
        }

        if (filters.value.per_page) {
            params.per_page = filters.value.per_page;
        }

        if (filters.value.page && filters.value.page > 1) {
            params.page = filters.value.page;
        }

        router.get(
            routeName,
            params,
            {
                preserveState: true,
                preserveScroll,
                onFinish: () => {
                    loading.value = false;
                },
            }
        );
    };

    const debouncedFetch = (delay = 300) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(() => {
            fetchData();
        }, delay);
    };

    const setSearch = (value: string) => {
        filters.value.search = value;
        filters.value.page = 1;
        debouncedFetch();
    };

    const setSort = (column: string) => {
        const currentSort = filters.value.sort;

        if (currentSort?.column === column) {
            filters.value.sort = {
                column,
                direction: currentSort.direction === 'asc' ? 'desc' : 'asc',
            };
        } else {
            filters.value.sort = {
                column,
                direction: 'asc',
            };
        }

        filters.value.page = 1;
        fetchData();
    };

    const setPerPage = (value: number) => {
        filters.value.per_page = value;
        filters.value.page = 1;
        fetchData();
    };

    const setPage = (page: number) => {
        filters.value.page = page;
        fetchData();
    };

    const getSortDirection = (column: string): 'asc' | 'desc' | null => {
        if (filters.value.sort?.column === column) {
            return filters.value.sort.direction;
        }
        return null;
    };

    const resetFilters = () => {
        filters.value = {
            search: '',
            sort: undefined,
            per_page: 10,
            page: 1,
        };
        fetchData();
    };

    return {
        loading,
        filters,
        setSearch,
        setSort,
        setPerPage,
        setPage,
        getSortDirection,
        resetFilters,
        fetchData,
    };
}
