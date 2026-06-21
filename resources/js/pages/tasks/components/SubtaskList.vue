<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, Circle, ListChecks, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Task } from '@/types/models/task';

const props = defineProps<{
    parentTask: Task;
}>();

const newTitle = ref('');
const adding = ref(false);

const subtasks = computed(() => props.parentTask.children || []);

const completedCount = computed(
    () => subtasks.value.filter((s) => s.completed_at).length,
);

const progress = computed(() => {
    if (subtasks.value.length === 0) {
        return 0;
    }

    return Math.round((completedCount.value / subtasks.value.length) * 100);
});

const addSubtask = () => {
    if (!newTitle.value.trim()) {
        return;
    }

    router.post(
        `/tasks`,
        {
            project_id: props.parentTask.project_id,
            title: newTitle.value,
            parent_id: props.parentTask.id,
            task_status_id: props.parentTask.task_status_id,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                newTitle.value = '';
                adding.value = false;
            },
        },
    );
};

const toggleSubtask = (subtask: Task) => {
    const closedStatusId = subtask.status?.is_closed
        ? null
        : props.parentTask.task_status_id;
    router.patch(
        `/tasks/${subtask.id}/status`,
        {
            task_status_id: closedStatusId,
        },
        {
            preserveScroll: true,
        },
    );
};

const deleteSubtask = (subtaskId: number) => {
    router.delete(`/tasks/${subtaskId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <ListChecks class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Subtasks</h3>
                <span class="text-xs text-muted-foreground"
                    >({{ completedCount }}/{{ subtasks.length }})</span
                >
            </div>
            <Button
                size="sm"
                variant="outline"
                class="gap-1"
                @click="adding = true"
            >
                <Plus class="h-3.5 w-3.5" />
                Add
            </Button>
        </div>

        <div v-if="subtasks.length" class="space-y-2">
            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full rounded-full bg-primary transition-all"
                    :style="{ width: `${progress}%` }"
                />
            </div>

            <div class="space-y-1">
                <div
                    v-for="subtask in subtasks"
                    :key="subtask.id"
                    class="flex items-center gap-2 rounded-lg border p-2"
                >
                    <button
                        class="flex h-5 w-5 shrink-0 items-center justify-center"
                        @click="toggleSubtask(subtask)"
                    >
                        <CheckCircle2
                            v-if="subtask.completed_at"
                            class="h-5 w-5 text-emerald-500"
                        />
                        <Circle v-else class="h-5 w-5 text-muted-foreground" />
                    </button>
                    <span
                        class="flex-1 text-sm"
                        :class="
                            subtask.completed_at
                                ? 'text-muted-foreground line-through'
                                : 'text-foreground'
                        "
                    >
                        {{ subtask.title }}
                    </span>
                    <Button
                        size="icon"
                        variant="ghost"
                        class="h-6 w-6 text-muted-foreground hover:text-destructive"
                        @click="deleteSubtask(subtask.id)"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>

        <div v-if="adding" class="flex items-center gap-2">
            <Input
                v-model="newTitle"
                placeholder="New subtask title..."
                class="flex-1"
                @keyup.enter="addSubtask"
            />
            <Button size="sm" @click="addSubtask">Add</Button>
            <Button size="sm" variant="ghost" @click="adding = false"
                >Cancel</Button
            >
        </div>

        <p
            v-if="!subtasks.length && !adding"
            class="text-sm text-muted-foreground"
        >
            No subtasks yet.
        </p>
    </div>
</template>
