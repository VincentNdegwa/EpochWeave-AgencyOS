<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TaskStatusController from '@/actions/App/Http/Controllers/TaskStatusController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
    name: props.status?.name || '',
    color: props.status?.color || '#94a3b8',
    position: props.status?.position?.toString() || '0',
    is_default: props.status?.is_default || false,
    is_closed: props.status?.is_closed || false,
});

watch(
    () => props.status,
    (s) => {
        if (s) {
            form.value = {
                name: s.name,
                color: s.color || '#94a3b8',
                position: s.position?.toString() || '0',
                is_default: s.is_default,
                is_closed: s.is_closed,
            };
        } else {
            form.value = {
                name: '',
                color: '#94a3b8',
                position: '0',
                is_default: false,
                is_closed: false,
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.status) {
            form.value = {
                name: '',
                color: '#94a3b8',
                position: '0',
                is_default: false,
                is_closed: false,
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
                            <Label for="ts-name" required>Name</Label>
                            <Input
                                id="ts-name"
                                name="name"
                                v-model="form.name"
                                required
                                placeholder="e.g. In Progress"
                            />
                            <InputError :message="errors.name" />
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

                        <div class="flex items-center gap-6 sm:col-span-2">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="ts-default"
                                    name="is_default"
                                    v-model:checked="form.is_default"
                                />
                                <Label for="ts-default" class="font-normal">Default status</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="ts-closed"
                                    name="is_closed"
                                    v-model:checked="form.is_closed"
                                />
                                <Label for="ts-closed" class="font-normal">Closed status</Label>
                            </div>
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
