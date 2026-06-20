<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref, watch, computed } from 'vue';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
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
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTaskPriorities } from '@/composables/useEnums';
import { useBuilderDataStore } from '@/stores/builderData';
import type { Tag } from '@/types/models/tag';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';

interface Props {
    open: boolean;
    task?: Task | null;
    task_statuses?: TaskStatus[];
    tags?: Tag[];
}

const props = withDefaults(defineProps<Props>(), {
    task: null,
    task_statuses: () => [],
    tags: () => [],
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const page = usePage();
const builderData = useBuilderDataStore();
const { projects, projectsLoaded } = storeToRefs(builderData);
const taskStatuses = computed(() => props.task_statuses || []);
const taskPriorities = useTaskPriorities();

function toggleTag(tagId: number) {
    const idStr = tagId.toString();
    const idx = form.value.tag_ids.indexOf(idStr);

    if (idx > -1) {
        form.value.tag_ids.splice(idx, 1);
    } else {
        form.value.tag_ids.push(idStr);
    }
}

function isTagSelected(tagId: number): boolean {
    return form.value.tag_ids.includes(tagId.toString());
}

watch(() => props.open, (isOpen) => {
    if (isOpen && !projectsLoaded.value) {
        builderData.fetchProjects();
    }
});

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
                                        v-for="status in taskStatuses"
                                        :key="status.id"
                                        :value="status.id.toString()"
                                    >
                                        {{ status.title }}
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

                        <div class="grid gap-2 sm:col-span-2">
                            <Label>Tags</Label>
                            <input
                                v-for="id in form.tag_ids"
                                :key="id"
                                type="hidden"
                                name="tag_ids[]"
                                :value="id"
                            />
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-auto min-h-[36px] flex-wrap justify-start gap-1 px-2 py-1"
                                    >
                                        <template v-if="form.tag_ids.length === 0">
                                            <span class="text-muted-foreground text-sm">Select tags...</span>
                                        </template>
                                        <Badge
                                            v-for="tag in tags.filter((t) => form.tag_ids.includes(t.id.toString()))"
                                            :key="tag.id"
                                            class="text-xs"
                                            :style="{
                                                backgroundColor: tag.color ? tag.color + '33' : '#f1f5f9',
                                                color: tag.color || '#475569',
                                                borderColor: tag.color ? tag.color + '66' : '#e2e8f0',
                                            }"
                                        >
                                            {{ tag.name }}
                                        </Badge>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-[260px] p-2" align="start">
                                    <div class="flex flex-col gap-1 max-h-[200px] overflow-y-auto">
                                        <div
                                            v-for="tag in tags"
                                            :key="tag.id"
                                            class="flex items-center gap-2 rounded px-2 py-1.5 cursor-pointer hover:bg-muted transition-colors"
                                            @click="toggleTag(tag.id)"
                                        >
                                            <Checkbox
                                                :checked="isTagSelected(tag.id)"
                                                class="pointer-events-none"
                                            />
                                            <span
                                                class="h-2 w-2 rounded-full shrink-0"
                                                :style="{ backgroundColor: tag.color || '#94a3b8' }"
                                            />
                                            <span class="text-sm">{{ tag.name }}</span>
                                        </div>
                                        <div v-if="tags.length === 0" class="text-sm text-muted-foreground px-2 py-1">
                                            No tags available.
                                        </div>
                                    </div>
                                </PopoverContent>
                            </Popover>
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
