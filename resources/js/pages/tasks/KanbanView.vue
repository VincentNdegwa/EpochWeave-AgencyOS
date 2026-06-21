<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Calendar, Clock, DollarSign, Hash } from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { getInitials } from '@/composables/useInitials';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TaskActions from './components/TaskActions.vue';

const props = withDefaults(
    defineProps<{
        tasks: Task[];
        task_statuses?: TaskStatus[];
    }>(),
    {
        task_statuses: () => [],
    },
);

const { formatDate } = useDateFormat();

const localTasks = ref<Task[]>([...props.tasks]);

watch(
    () => props.tasks,
    (newTasks) => {
        if (!dragging.value) {
            localTasks.value = [...newTasks];
        }
    },
    { deep: true },
);

const columns = computed(() =>
    props.task_statuses.map((status) => ({
        status,
        tasks: localTasks.value
            .filter((t) => t.task_status_id === status.id)
            .sort((a, b) => a.position - b.position),
    })),
);

const dragging = ref<{ id: number; fromStatusId: number } | null>(null);
const dragOverStatus = ref<number | null>(null);
const isDragging = ref(false);
const hoveredTaskId = ref<number | null>(null);

const mouseDownTime = ref<number>(0);
const mouseDownTarget = ref<number | null>(null);

const canDrop = (toStatusId: number): boolean => {
    if (!dragging.value) {
        return false;
    }

    return dragging.value.fromStatusId !== toStatusId;
};

function onDragStart(e: DragEvent, task: Task) {
    hoveredTaskId.value = null;
    isDragging.value = true;
    dragging.value = { id: task.id, fromStatusId: task.task_status_id };

    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
    }
}

function onDragEnd() {
    dragging.value = null;
    dragOverStatus.value = null;
    isDragging.value = false;
}

function onTaskMouseDown(task: Task) {
    mouseDownTime.value = Date.now();
    mouseDownTarget.value = task.id;
}

function onTaskMouseUp(task: Task) {
    const elapsed = Date.now() - mouseDownTime.value;

    if (
        mouseDownTarget.value === task.id &&
        !isDragging.value &&
        elapsed < 200 &&
        elapsed > 50
    ) {
        router.visit(`/tasks/${task.id}`);
    }

    mouseDownTarget.value = null;
}

function onDragOver(e: DragEvent, statusId: number) {
    if (canDrop(statusId)) {
        e.preventDefault();
        dragOverStatus.value = statusId;
    }
}

function onDrop(e: DragEvent, targetStatusId: number) {
    e.preventDefault();

    if (!dragging.value || !canDrop(targetStatusId)) {
        onDragEnd();

        return;
    }

    const taskId = dragging.value.id;
    onDragEnd();

    const taskIndex = localTasks.value.findIndex((t) => t.id === taskId);

    if (taskIndex !== -1) {
        localTasks.value[taskIndex] = {
            ...localTasks.value[taskIndex],
            task_status_id: targetStatusId,
        };
    }

    router.patch(
        `/tasks/${taskId}/status`,
        { task_status_id: targetStatusId },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="custom-scrollbar flex gap-3 overflow-x-auto pb-4">
        <div
            v-for="col in columns"
            :key="col.status.id"
            class="flex w-[260px] shrink-0 flex-col rounded-xl border bg-muted/30"
            :class="
                dragOverStatus === col.status.id && canDrop(col.status.id)
                    ? 'ring-2 ring-border'
                    : ''
            "
            @dragover="onDragOver($event, col.status.id)"
            @drop="onDrop($event, col.status.id)"
        >
            <div class="flex items-center gap-2 px-3 pt-3 pb-2">
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full"
                    :style="{ backgroundColor: col.status.color || '#94a3b8' }"
                />
                <span class="truncate text-sm font-semibold text-foreground">{{
                    col.status.title
                }}</span>
                <span
                    class="ml-auto rounded-full bg-muted/50 px-1.5 py-0.5 text-xs font-semibold tabular-nums"
                >
                    {{ col.tasks.length }}
                </span>
            </div>

            <div
                class="mx-3 mb-2 h-0.5 rounded-full"
                :style="{ backgroundColor: col.status.color || '#94a3b8' }"
            />

            <div
                class="flex flex-1 flex-col gap-2 overflow-y-auto px-2 pb-3"
                style="max-height: 72vh; min-height: 120px"
            >
                <div
                    v-for="task in col.tasks"
                    :key="task.id"
                    class="group relative rounded-lg border bg-background p-3 shadow-sm transition-all select-none"
                    :class="[
                        'cursor-grab hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing',
                        dragging?.id === task.id ? 'scale-95 opacity-40' : '',
                    ]"
                    draggable="true"
                    @mouseenter="hoveredTaskId = task.id"
                    @mouseleave="hoveredTaskId = null"
                    @dragstart="onDragStart($event, task)"
                    @dragend="onDragEnd"
                    @mousedown="onTaskMouseDown(task)"
                    @mouseup="onTaskMouseUp(task)"
                >
                    <div class="mb-1.5 flex items-start justify-between gap-1">
                        <span
                            v-if="task.project"
                            class="flex items-center gap-1 truncate text-[11px] font-medium text-muted-foreground"
                        >
                            <span
                                class="inline-block h-1.5 w-1.5 shrink-0 rounded-full"
                                :style="{
                                    backgroundColor:
                                        task.project.color || '#94a3b8',
                                }"
                            />
                            {{ task.project.name }}
                        </span>
                        <TaskActions
                            :task="task"
                            :task-statuses="props.task_statuses"
                            variant="dropdown"
                            size="icon"
                        />
                    </div>

                    <!-- Title -->
                    <p class="text-sm leading-snug font-medium text-foreground">
                        {{ task.title }}
                    </p>

                    <!-- Tags -->
                    <div
                        v-if="task.tags?.length"
                        class="mt-2 flex flex-wrap gap-1"
                    >
                        <span
                            v-for="tag in task.tags"
                            :key="tag.id"
                            class="inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[10px] font-medium"
                            :style="{
                                backgroundColor: tag.color
                                    ? tag.color + '26'
                                    : '#f1f5f9',
                                color: tag.color || '#475569',
                            }"
                        >
                            <Hash class="h-2.5 w-2.5" />
                            {{ tag.name }}
                        </span>
                    </div>

                    <!-- Footer: Assignee | Due Date | Est Hours -->
                    <div class="mt-2.5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <!-- Assignee Avatar -->
                            <span
                                v-if="task.assignee"
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-muted text-[9px] font-semibold text-muted-foreground ring-1 ring-border"
                                :title="task.assignee.name"
                            >
                                {{ getInitials(task.assignee.name) }}
                            </span>
                            <span
                                v-else
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-muted/50 text-[9px] text-muted-foreground ring-1 ring-border"
                                title="Unassigned"
                            >
                                —
                            </span>
                        </div>

                        <div
                            class="flex items-center gap-2.5 text-[10px] text-muted-foreground"
                        >
                            <!-- Due Date -->
                            <span
                                v-if="task.due_date"
                                class="flex items-center gap-0.5"
                                :class="{
                                    'font-medium text-orange-500':
                                        new Date(task.due_date) < new Date(),
                                }"
                            >
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(task.due_date) }}
                            </span>

                            <!-- Estimated Hours -->
                            <span
                                v-if="task.estimated_hours"
                                class="flex items-center gap-0.5"
                            >
                                <Clock class="h-3 w-3" />
                                {{ task.estimated_hours }}h
                            </span>

                            <!-- Billable -->
                            <span
                                v-if="task.is_billable"
                                class="flex items-center gap-0.5 text-emerald-600"
                            >
                                <DollarSign class="h-3 w-3" />
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="col.tasks.length === 0"
                    class="flex h-16 items-center justify-center text-xs text-muted-foreground/50"
                >
                    No tasks
                </div>
            </div>
        </div>
    </div>
</template>
