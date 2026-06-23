<script setup lang="ts">
import {
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Hash,
    SignalHigh,
    SignalLow,
    SignalMedium,
} from '@lucide/vue';
import { computed } from 'vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { getInitials } from '@/composables/useInitials';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TaskActions from './TaskActions.vue';

const props = defineProps<{
    task: Task;
    taskStatuses?: TaskStatus[];
    isDragging?: boolean;
}>();

const { formatDate } = useDateFormat();

const priorityMeta = computed(() => {
    switch (props.task.priority) {
        case 'high':
            return { icon: SignalHigh, color: 'text-red-500' };
        case 'medium':
            return { icon: SignalMedium, color: 'text-amber-500' };
        case 'low':
            return { icon: SignalLow, color: 'text-slate-500' };
        default:
            return null;
    }
});

const isCompleted = computed(() => !!props.task.completed_at);

const dueState = computed(() => {
    if (!props.task.due_date) {
        return null;
    }

    const diff = Math.ceil(
        (new Date(props.task.due_date).getTime() - Date.now()) / 86_400_000,
    );

    if (isCompleted.value) {
        return { cls: 'text-muted-foreground', label: formatDate(props.task.due_date) };
    }

    if (diff < 0) {
        return { cls: 'text-destructive font-medium', label: `${Math.abs(diff)}d overdue` };
    }

    if (diff === 0) {
        return { cls: 'text-amber-500 font-medium', label: 'Today' };
    }

    if (diff === 1) {
        return { cls: 'text-amber-500', label: 'Tomorrow' };
    }

    return { cls: 'text-muted-foreground', label: formatDate(props.task.due_date) };
});
</script>

<template>
    <div
        class="group relative flex flex-col gap-2 rounded-lg border border-border bg-background p-3 shadow-sm transition-all select-none cursor-grab hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing"
        :class="isDragging ? 'scale-95 opacity-40' : ''"
    >
        <!-- Row 1: Project pill + priority + actions -->
        <div class="flex items-center justify-between gap-1">
            <div class="flex min-w-0 items-center gap-1.5">
                <span
                    v-if="task.project"
                    class="flex max-w-32 items-center gap-1 truncate rounded-md bg-muted/60 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                >
                    <span
                        class="h-1.5 w-1.5 shrink-0 rounded-full"
                        :style="{ backgroundColor: task.project.color ?? '#94a3b8' }"
                    />
                    {{ task.project.name }}
                </span>
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <span
                    v-if="priorityMeta"
                    class="flex items-center gap-0.5 rounded px-1 py-0.5 text-[10px] font-semibold"
                    :class="priorityMeta.color"
                    :title="task.priority + ' priority'"
                >
                    <component :is="priorityMeta.icon" class="h-4 w-4" />
                </span>

                <TaskActions
                    :task="task"
                    :task-statuses="taskStatuses"
                    variant="dropdown"
                    size="icon"
                    class="opacity-0 transition-opacity group-hover:opacity-100"
                />
            </div>
        </div>

        <!-- Row 2: Title -->
        <div class="flex items-start gap-1.5">
            <CheckCircle2
                v-if="isCompleted"
                class="mt-0.5 h-3.5 w-3.5 shrink-0 text-green-500"
            />
            <p class="text-[13px] font-medium leading-snug text-foreground">
                {{ task.title }}
            </p>
        </div>

        <!-- Row 3: Tags -->
        <div v-if="task.tags?.length" class="flex flex-wrap gap-1">
            <span
                v-for="tag in task.tags"
                :key="tag.id"
                class="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[10px] font-medium"
                :style="{
                    backgroundColor: tag.color ? tag.color + '20' : '#f1f5f9',
                    color: tag.color ?? '#475569',
                }"
            >
                <Hash class="h-2.5 w-2.5" />
                {{ tag.name }}
            </span>
        </div>

        <!-- Row 4: Footer meta -->
        <div class="flex items-center justify-between gap-2 pt-0.5">
            <!-- Left: assignee avatar -->
            <div>
                <span
                    v-if="task.assignee"
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-[9px] font-bold text-primary ring-1 ring-border"
                    :title="task.assignee.name"
                >
                    {{ getInitials(task.assignee.name) }}
                </span>
                <span
                    v-else
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-muted text-[9px] text-muted-foreground ring-1 ring-border"
                    title="Unassigned"
                >
                    ?
                </span>
            </div>

            <!-- Right: metadata chips -->
            <div class="flex items-center gap-2 text-[10px] text-muted-foreground">
                <span
                    v-if="task.estimated_hours"
                    class="flex items-center gap-0.5"
                    :title="`Estimated: ${task.estimated_hours}h`"
                >
                    <Clock class="h-3 w-3" />
                    {{ task.estimated_hours }}h
                </span>

                <span
                    v-if="task.is_billable"
                    class="flex items-center text-emerald-600"
                    title="Billable"
                >
                    <DollarSign class="h-3 w-3" />
                </span>

                <span
                    v-if="dueState"
                    class="flex items-center gap-0.5"
                    :class="dueState.cls"
                    :title="`Due: ${task.due_date}`"
                >
                    <Calendar class="h-3 w-3" />
                    {{ dueState.label }}
                </span>
            </div>
        </div>
    </div>
</template>
