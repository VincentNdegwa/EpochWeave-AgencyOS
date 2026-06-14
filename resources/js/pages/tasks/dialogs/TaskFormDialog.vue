<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
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
import { useTaskPriorities } from '@/composables/useEnums';
import { usePage } from '@inertiajs/vue3';
import type { Task } from '@/types/models/task';

interface Props {
    open: boolean;
    task?: Task | null;
}

const props = withDefaults(defineProps<Props>(), {
    task: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const page = usePage();
const projects = computed(() => (page.props.projects as any[]) || []);
const taskPriorities = useTaskPriorities();

const form = ref({
    project_id: props.task?.project_id?.toString() || '',
    task_status_id: props.task?.task_status_id?.toString() || '',
    title: props.task?.title || '',
    description: props.task?.description || '',
    assignee_id: props.task?.assignee_id?.toString() || '',
    priority: props.task?.priority || 'medium',
    due_date: props.task?.due_date || '',
    estimated_hours: props.task?.estimated_hours?.toString() || '',
    is_billable: props.task?.is_billable ?? true,
    tag_ids: [] as string[],
});

watch(
    () => props.task,
    (t) => {
        if (t) {
            form.value = {
                project_id: t.project_id?.toString() || '',
                task_status_id: t.task_status_id?.toString() || '',
                title: t.title,
                description: t.description || '',
                assignee_id: t.assignee_id?.toString() || '',
                priority: t.priority,
                due_date: t.due_date || '',
                estimated_hours: t.estimated_hours?.toString() || '',
                is_billable: t.is_billable ?? true,
                tag_ids: (t.tags || []).map((tag) => tag.id.toString()),
            };
        } else {
            form.value = {
                project_id: '',
                task_status_id: '',
                title: '',
                description: '',
                assignee_id: '',
                priority: 'medium',
                due_date: '',
                estimated_hours: '',
                is_billable: true,
                tag_ids: [],
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.task) {
            form.value = {
                project_id: '',
                task_status_id: '',
                title: '',
                description: '',
                assignee_id: '',
                priority: 'medium',
                due_date: '',
                estimated_hours: '',
                is_billable: true,
                tag_ids: [],
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{ task ? 'Edit Task' : 'New Task' }}</DialogTitle>
                <DialogDescription>
                    {{ task ? 'Update the task details below.' : 'Create a new task.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (task
                        ? TaskController.update.form({ task: task.id })
                        : TaskController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="emit('success'); emit('update:open', false);"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="task-title" required>Title</Label>
                            <Input
                                id="task-title"
                                name="title"
                                v-model="form.title"
                                required
                                placeholder="Implement authentication"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-project">Project</Label>
                            <Select name="project_id" v-model="form.project_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a project" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="project in projects"
                                        :key="project.id"
                                        :value="project.id.toString()"
                                    >
                                        {{ project.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.project_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-status">Status</Label>
                            <Select name="task_status_id" v-model="form.task_status_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="status in (page.props.task_statuses || [])"
                                        :key="status.id"
                                        :value="status.id.toString()"
                                    >
                                        {{ status.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.task_status_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-priority">Priority</Label>
                            <Select name="priority" v-model="form.priority">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="p in taskPriorities.values"
                                        :key="p.value"
                                        :value="p.value"
                                    >
                                        {{ p.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.priority" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-due">Due Date</Label>
                            <Input
                                id="task-due"
                                name="due_date"
                                v-model="form.due_date"
                                type="date"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="task-estimated">Estimated Hours</Label>
                            <Input
                                id="task-estimated"
                                name="estimated_hours"
                                v-model="form.estimated_hours"
                                type="number"
                                min="0"
                                step="0.5"
                                placeholder="8"
                            />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="task-description">Description</Label>
                            <Textarea
                                id="task-description"
                                name="description"
                                v-model="form.description"
                                placeholder="Describe the task..."
                                rows="3"
                            />
                        </div>

                        <div class="flex items-center gap-2 sm:col-span-2">
                            <Checkbox
                                id="task-billable"
                                name="is_billable"
                                v-model:checked="form.is_billable"
                            />
                            <Label for="task-billable" class="font-normal">
                                Billable
                            </Label>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ task ? 'Update' : 'Create' }} Task
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
