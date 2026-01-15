<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { User } from '@/types';

interface Props {
    open: boolean;
    user: User | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const form = useForm({});

const confirmDelete = () => {
    if (!props.user) return;

    form.delete(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    });
};

const closeModal = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Delete User</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete <strong>{{ user?.name }}</strong>? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeModal">
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="form.processing"
                    @click="confirmDelete"
                >
                    {{ form.processing ? 'Deleting...' : 'Delete' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
