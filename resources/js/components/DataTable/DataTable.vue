<script setup lang="ts" generic="T extends Record<string, unknown>">
import { computed } from 'vue';
import { ArrowUpDown, ArrowUp, ArrowDown, Search, Loader2 } from 'lucide-vue-next';
import type { DataTableColumn, DataTablePagination, DataTableFilters } from '@/types/datatable';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    columns: DataTableColumn<T>[];
    data: T[];
    pagination: DataTablePagination;
    filters?: DataTableFilters;
    loading?: boolean;
    searchable?: boolean;
    perPageOptions?: number[];
    emptyMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    searchable: true,
    perPageOptions: () => [10, 25, 50, 100],
    emptyMessage: 'No data available',
});

const emit = defineEmits<{
    (e: 'search', value: string): void;
    (e: 'sort', column: string): void;
    (e: 'per-page', value: number): void;
    (e: 'page', page: number): void;
}>();

const searchValue = computed({
    get: () => props.filters?.search || '',
    set: (value: string) => emit('search', value),
});

const getSortDirection = (column: string): 'asc' | 'desc' | null => {
    if (props.filters?.sort?.column === column) {
        return props.filters.sort.direction;
    }
    return null;
};

const getCellValue = (row: T, column: DataTableColumn<T>): unknown => {
    const keys = String(column.key).split('.');
    let value: unknown = row;

    for (const key of keys) {
        if (value && typeof value === 'object' && key in value) {
            value = (value as Record<string, unknown>)[key];
        } else {
            value = undefined;
            break;
        }
    }

    if (column.render) {
        return column.render(value, row);
    }

    return value;
};

const pageNumbers = computed(() => {
    const total = props.pagination.last_page;
    const current = props.pagination.current_page;
    const pages: (number | string)[] = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);

        if (current > 3) {
            pages.push('...');
        }

        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);

        for (let i = start; i <= end; i++) {
            if (!pages.includes(i)) {
                pages.push(i);
            }
        }

        if (current < total - 2) {
            pages.push('...');
        }

        if (!pages.includes(total)) {
            pages.push(total);
        }
    }

    return pages;
});
</script>

<template>
    <div class="w-full space-y-4">
        <!-- Header: Search and Per Page -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <!-- Search -->
            <div v-if="searchable" class="relative w-full sm:max-w-xs">
                <Search class="text-muted-foreground absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2" />
                <Input
                    v-model="searchValue"
                    type="text"
                    placeholder="Search..."
                    class="pl-9"
                />
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground text-sm">Show</span>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="min-w-[70px]">
                            {{ pagination.per_page }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem
                            v-for="option in perPageOptions"
                            :key="option"
                            @click="$emit('per-page', option)"
                        >
                            {{ option }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <span class="text-muted-foreground text-sm">entries</span>
            </div>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto rounded-lg border">
            <!-- Loading Overlay -->
            <div
                v-if="loading"
                class="bg-background/80 absolute inset-0 z-10 flex items-center justify-center"
            >
                <Loader2 class="text-primary h-8 w-8 animate-spin" />
            </div>

            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 border-b text-xs uppercase">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="String(column.key)"
                            :class="[
                                'px-4 py-3 font-medium',
                                column.headerClass,
                                column.sortable ? 'cursor-pointer select-none hover:bg-muted' : '',
                            ]"
                            @click="column.sortable && $emit('sort', String(column.key))"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <template v-if="column.sortable">
                                    <ArrowUp
                                        v-if="getSortDirection(String(column.key)) === 'asc'"
                                        class="h-4 w-4"
                                    />
                                    <ArrowDown
                                        v-else-if="getSortDirection(String(column.key)) === 'desc'"
                                        class="h-4 w-4"
                                    />
                                    <ArrowUpDown
                                        v-else
                                        class="text-muted-foreground/50 h-4 w-4"
                                    />
                                </template>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="data.length === 0">
                        <td
                            :colspan="columns.length"
                            class="text-muted-foreground px-4 py-8 text-center"
                        >
                            {{ emptyMessage }}
                        </td>
                    </tr>
                    <tr
                        v-for="(row, index) in data"
                        :key="index"
                        class="hover:bg-muted/50 border-b transition-colors last:border-0"
                    >
                        <td
                            v-for="column in columns"
                            :key="String(column.key)"
                            :class="['px-4 py-3', column.class]"
                        >
                            <slot :name="`cell-${String(column.key)}`" :row="row" :value="getCellValue(row, column)" :index="index">
                                {{ getCellValue(row, column) }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer: Info and Pagination -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <!-- Info -->
            <div class="text-muted-foreground text-sm">
                <template v-if="pagination.total > 0">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
                </template>
                <template v-else>
                    No entries found
                </template>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.current_page === 1"
                    @click="$emit('page', pagination.current_page - 1)"
                >
                    Previous
                </Button>

                <template v-for="page in pageNumbers" :key="page">
                    <span v-if="page === '...'" class="text-muted-foreground px-2">...</span>
                    <Button
                        v-else
                        :variant="page === pagination.current_page ? 'default' : 'outline'"
                        size="sm"
                        class="min-w-[36px]"
                        @click="$emit('page', Number(page))"
                    >
                        {{ page }}
                    </Button>
                </template>

                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="$emit('page', pagination.current_page + 1)"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
