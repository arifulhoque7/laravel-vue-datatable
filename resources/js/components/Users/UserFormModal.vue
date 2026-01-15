<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import type { User } from '@/types';

interface Props {
    open: boolean;
    user?: User | null;
}

const props = withDefaults(defineProps<Props>(), {
    user: null,
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEditing = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
});

watch(
    () => props.open,
    (open) => {
        if (open) {
            isEditing.value = !!props.user;
            form.name = props.user?.name ?? '';
            form.email = props.user?.email ?? '';
            form.password = '';
            form.clearErrors();
        }
    }
);

const submit = () => {
    if (isEditing.value && props.user) {
        form.put(`/users/${props.user.id}`, {
            preserveScroll: true,
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post('/users', {
            preserveScroll: true,
            onSuccess: () => emit('update:open', false),
        });
    }
};

const closeModal = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit User' : 'Add User' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update user information.' : 'Create a new user account.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="John Doe"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="john@example.com"
                        required
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password">
                        Password
                        <span v-if="isEditing" class="text-muted-foreground text-xs">(leave blank to keep current)</span>
                    </Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        :required="!isEditing"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
