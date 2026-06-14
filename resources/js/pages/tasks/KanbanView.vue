<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Task } from '@/types/models/task';

const { tasks } = defineProps<{ tasks: Task[] }>();

const statuses = computed(() => {
    const map = new Map<number, { id: number; name: string; color: string | null; position: number; is_closed: boolean }>();
    tasks.forEach((t) => {
        if (t.status && !map.has(t.status.id)) {
            map.set(t.status.id, t.status);
        }
    });
    return Array.from(map.values()).sort((a, b) => a.position - b.position);
});

const columns = computed(() =>
    statuses.value.map((status) => ({
        status,
        tasks: tasks.filter((t) => t.task_status_id === status.id).sort((a, b) => a.position - b.position),
    })),
);

const dragging = ref<{ id: number; fromStatusId: number } | null>(null);
const dragOverStatus = ref<number | null>(null);

const canDrop = (toStatusId: number): boolean => {
    if (!dragging.value) return false;
    return dragging.value.fromStatusId !== toStatusId;
};

function onDragStart(e: DragEvent, task: Task) {
    dragging.value = { id: task.id, fromStatusId: task.task_status_id };
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
    }
}

function onDragEnd() {
    dragging.value = null;
    dragOverStatus.value = null;
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

    router.patch(
        `/tasks/${taskId}/status`,
        { task_status_id: targetStatusId },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="flex gap-3 overflow-x-auto pb-4">
        <div
            v-for="col in columns"
            :key="col.status.id"
            class="flex w-[260px] shrink-0 flex-col rounded-xl border bg-muted/30"
            :class="dragOverStatus === col.status.id && canDrop(col.status.id) ? 'ring-2 ring-border' : ''"
            @dragover="onDragOver($event, col.status.id)"
            @drop="onDrop($event, col.status.id)"
        >
            <div class="flex items-center gap-2 px-3 pt-3 pb-2">
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full"
                    :style="{ backgroundColor: col.status.color || '#94a3b8' }"
                />
                <span class="truncate text-sm font-semibold text-foreground">{{ col.status.name }}</span>
                <span class="ml-auto rounded-full bg-muted/50 px-1.5 py-0.5 text-xs font-semibold tabular-nums">
                    {{ col.tasks.length }}
                </span>
            </div>

            <div class="mx-3 mb-2 h-0.5 rounded-full" :style="{ backgroundColor: col.status.color || '#94a3b8' }" />

            <div class="flex flex-1 flex-col gap-2 overflow-y-auto px-2 pb-3" style="max-height: 72vh; min-height: 120px">
                <div
                    v-for="task in col.tasks"
                    :key="task.id"
                    class="cursor-grab rounded-lg border bg-background p-3 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing"
                    :class="dragging?.id === task.id ? 'scale-95 opacity-40' : ''"
                    draggable="true"
                    @dragstart="onDragStart($event, task)"
                    @dragend="onDragEnd"
                >
                    <p class="text-sm font-medium text-foreground">{{ task.title }}</p>
                    <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                        <span v-if="task.assignee">{{ task.assignee.name }}</span>
                        <span v-else>Unassigned</span>
                        <span v-if="task.due_date">{{ task.due_date }}</span>
                    </div>
                </div>

                <div v-if="col.tasks.length === 0" class="flex h-16 items-center justify-center text-xs text-muted-foreground/50">
                    No tasks
                </div>
            </div>
        </div>
    </div>
</template>
