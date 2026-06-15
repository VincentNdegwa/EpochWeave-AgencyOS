<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TaskStatusController from '@/actions/App/Http/Controllers/TaskStatusController';
import InputError from '@/components/InputError.vue';
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
import type { TaskStatus } from '@/types/models/task_status';

interface Props {
    open: boolean;
    status?: TaskStatus | null;
}

const props = withDefaults(defineProps<Props>(), {
    status: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    title: props.status?.title || '',
    color: props.status?.color || '#94a3b8',
    position: props.status?.position?.toString() || '0',
});

watch(
    () => props.status,
    (s) => {
        if (s) {
            form.value = {
                title: s.title,
                color: s.color || '#94a3b8',
                position: s.position?.toString() || '0',
            };
        } else {
            form.value = {
                title: '',
                color: '#94a3b8',
                position: '0',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.status) {
            form.value = {
                title: '',
                color: '#94a3b8',
                position: '0',
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ status ? 'Edit Status' : 'New Status' }}</DialogTitle>
                <DialogDescription>
                    {{ status ? 'Update task status details.' : 'Create a new task status for your projects.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (status
                        ? TaskStatusController.update.form({ task_status: status.id })
                        : TaskStatusController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="emit('success'); emit('update:open', false);"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="ts-title" required>Title</Label>
                            <Input
                                id="ts-title"
                                name="title"
                                v-model="form.title"
                                required
                                placeholder="e.g. In Progress"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="ts-color">Color</Label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    name="color"
                                    v-model="form.color"
                                    class="h-9 w-9 cursor-pointer rounded border"
                                />
                                <Input name="color" v-model="form.color" class="flex-1" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="ts-position">Position</Label>
                            <Input
                                id="ts-position"
                                name="position"
                                v-model="form.position"
                                type="number"
                                min="0"
                            />
                            <InputError :message="errors.position" />
                        </div>

                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ status ? 'Update' : 'Create' }} Status
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
