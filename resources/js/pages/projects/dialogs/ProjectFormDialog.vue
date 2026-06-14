<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useProjectStatuses } from '@/composables/useEnums';
import { useBuilderDataStore } from '@/stores/builderData';
import type { Project } from '@/types/models/project';

interface Props {
    open: boolean;
    project?: Project | null;
}

const props = withDefaults(defineProps<Props>(), {
    project: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const builderStore = useBuilderDataStore();
const projectStatuses = useProjectStatuses();

const PRESET_COLORS = [
    '#6366f1', '#ec4899', '#10b981', '#f59e0b',
    '#3b82f6', '#ef4444', '#8b5cf6', '#14b8a6',
    '#f97316', '#06b6d4',
];

function randomColor(): string {
    return PRESET_COLORS[Math.floor(Math.random() * PRESET_COLORS.length)];
}

const form = ref({
    account_id: props.project?.account_id?.toString() || '',
    name: props.project?.name || '',
    description: props.project?.description || '',
    color: props.project?.color || randomColor(),
    status: props.project?.status || 'active',
    start_date: props.project?.start_date || '',
    due_date: props.project?.due_date || '',
    portal_visible: props.project?.portal_visible ?? true,
});

watch(
    () => props.project,
    (p) => {
        if (p) {
            form.value = {
                account_id: p.account_id?.toString() || '',
                name: p.name,
                description: p.description || '',
                color: p.color || randomColor(),
                status: p.status,
                start_date: p.start_date || '',
                due_date: p.due_date || '',
                portal_visible: p.portal_visible ?? true,
            };
        } else {
            form.value = {
                account_id: '',
                name: '',
                description: '',
                color: randomColor(),
                status: 'active',
                start_date: '',
                due_date: '',
                portal_visible: true,
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            builderStore.fetchAccounts();
            if (!props.project) {
                form.value = {
                    account_id: '',
                    name: '',
                    description: '',
                    color: randomColor(),
                    status: 'active',
                    start_date: '',
                    due_date: '',
                    portal_visible: true,
                };
            }
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{ project ? 'Edit Project' : 'New Project' }}</DialogTitle>
                <DialogDescription>
                    {{ project ? 'Update the project details below.' : 'Create a new project for your workspace.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (project
                        ? ProjectController.update.form({ project: project.id })
                        : ProjectController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="emit('success'); emit('update:open', false);"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="project-name" required>Name</Label>
                            <Input
                                id="project-name"
                                name="name"
                                v-model="form.name"
                                required
                                placeholder="Website Redesign"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="project-account" required>Account</Label>
                            <Select name="account_id" v-model="form.account_id" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select an account" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="account in builderStore.accounts"
                                        :key="account.id"
                                        :value="account.id.toString()"
                                    >
                                        {{ account.company_name || account.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.account_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="project-status">Status</Label>
                            <Select name="status" v-model="form.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="s in projectStatuses.values"
                                        :key="s.value"
                                        :value="s.value"
                                    >
                                        {{ s.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.status" />
                        </div>

                        <input type="hidden" name="color" :value="form.color" />

                        <div class="grid gap-2">
                            <Label for="project-start">Start Date</Label>
                            <Input
                                id="project-start"
                                name="start_date"
                                v-model="form.start_date"
                                type="date"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="project-due">Due Date</Label>
                            <Input
                                id="project-due"
                                name="due_date"
                                v-model="form.due_date"
                                type="date"
                            />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="project-description">Description</Label>
                            <Textarea
                                id="project-description"
                                name="description"
                                v-model="form.description"
                                placeholder="Brief description of the project..."
                                rows="3"
                            />
                        </div>

                        <div class="flex items-center gap-2 sm:col-span-2">
                            <Checkbox
                                id="project-portal"
                                name="portal_visible"
                                v-model:checked="form.portal_visible"
                            />
                            <Label for="project-portal" class="font-normal">
                                Visible in client portal
                            </Label>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ project ? 'Update' : 'Create' }} Project
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
