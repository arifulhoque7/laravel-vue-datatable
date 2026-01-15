<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { DataTable } from '@/components/DataTable';
import { useDataTable } from '@/composables/useDataTable';
import type { DataTableColumn, DataTablePagination, DataTableFilters } from '@/types/datatable';
import type { BreadcrumbItem, User } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserFormModal from '@/components/Users/UserFormModal.vue';
import DeleteUserDialog from '@/components/Users/DeleteUserDialog.vue';

interface Props {
    users: User[];
    pagination: DataTablePagination;
    filters: DataTableFilters;
}

const props = defineProps<Props>();

const currentUser = usePage().props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const {
    loading,
    setSearch,
    setSort,
    setPerPage,
    setPage,
} = useDataTable('/users', props.filters);

const getSerialNumber = (index: number): number => {
    const from = props.pagination.from ?? 1;
    return from + index;
};

const columns: DataTableColumn<User>[] = [
    {
        key: 'serial',
        label: '#',
        sortable: false,
        class: 'w-16',
    },
    {
        key: 'name',
        label: 'Name',
        sortable: true,
    },
    {
        key: 'email',
        label: 'Email',
        sortable: true,
    },
    {
        key: 'email_verified_at',
        label: 'Verified',
        sortable: false,
    },
    {
        key: 'created_at',
        label: 'Created At',
        sortable: true,
    },
    {
        key: 'actions',
        label: 'Actions',
        sortable: false,
        class: 'w-24',
    },
];

const formatDate = (date: string | null): string => {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Modal state
const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedUser = ref<User | null>(null);

const openCreateModal = () => {
    selectedUser.value = null;
    showFormModal.value = true;
};

const openEditModal = (user: User) => {
    selectedUser.value = user;
    showFormModal.value = true;
};

const openDeleteDialog = (user: User) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Users Management</h1>
                <Button @click="openCreateModal">
                    <Plus class="mr-2 h-4 w-4" />
                    Add User
                </Button>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                <DataTable
                    :columns="columns"
                    :data="users"
                    :pagination="pagination"
                    :filters="filters"
                    :loading="loading"
                    @search="setSearch"
                    @sort="setSort"
                    @per-page="setPerPage"
                    @page="setPage"
                >
                    <template #cell-serial="{ index }">
                        {{ getSerialNumber(index) }}
                    </template>

                    <template #cell-email_verified_at="{ value }">
                        <Badge :variant="value ? 'default' : 'secondary'">
                            {{ value ? 'Verified' : 'Pending' }}
                        </Badge>
                    </template>

                    <template #cell-created_at="{ value }">
                        {{ formatDate(value as string) }}
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="openEditModal(row)"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button
                                v-if="row.id !== currentUser.id"
                                variant="ghost"
                                size="sm"
                                @click="openDeleteDialog(row)"
                            >
                                <Trash2 class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AppLayout>

    <!-- Modals -->
    <UserFormModal
        v-model:open="showFormModal"
        :user="selectedUser"
    />

    <DeleteUserDialog
        v-model:open="showDeleteDialog"
        :user="selectedUser"
    />
</template>
