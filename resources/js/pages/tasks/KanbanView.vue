<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TaskCard from './components/TaskCard.vue';

const props = withDefaults(
    defineProps<{
        tasks: Task[];
        task_statuses?: TaskStatus[];
    }>(),
    {
        task_statuses: () => [],
    },
);

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
                <TaskCard
                    v-for="task in col.tasks"
                    :key="task.id"
                    :task="task"
                    :task-statuses="props.task_statuses"
                    :is-dragging="dragging?.id === task.id"
                    draggable="true"
                    @mouseenter="hoveredTaskId = task.id"
                    @mouseleave="hoveredTaskId = null"
                    @dragstart="onDragStart($event, task)"
                    @dragend="onDragEnd"
                    @mousedown="onTaskMouseDown(task)"
                    @mouseup="onTaskMouseUp(task)"
                />

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
