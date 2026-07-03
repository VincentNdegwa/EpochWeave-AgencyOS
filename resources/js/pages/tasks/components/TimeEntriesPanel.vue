<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Clock, Pause, Trash2 } from '@lucide/vue';
import { computed, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import type { TimeEntry } from '@/types/models/time_entry';

const props = defineProps<{
    taskId: number;
    projectId: number;
    timeEntries: TimeEntry[];
}>();

const now = ref(new Date());
const timerInterval = ref<ReturnType<typeof setInterval> | null>(null);

const runningEntry = computed(() => {
    return props.timeEntries.find((e) => e.ended_at === null) ?? null;
});

const runningSeconds = computed(() => {
    if (!runningEntry.value) {
        return 0;
    }

    return Math.floor(
        (now.value.getTime() -
            new Date(runningEntry.value.started_at).getTime()) /
            1000,
    );
});

const closedEntries = computed(() => {
    return props.timeEntries
        .filter((e) => e.ended_at !== null)
        .sort(
            (a, b) =>
                new Date(b.started_at).getTime() -
                new Date(a.started_at).getTime(),
        );
});

const totalSeconds = computed(() => {
    const closed = closedEntries.value.reduce(
        (sum, e) => sum + e.duration_seconds,
        0,
    );

    return closed + runningSeconds.value;
});

const startLiveTimer = () => {
    if (timerInterval.value) {
        return;
    }

    timerInterval.value = setInterval(() => {
        now.value = new Date();
    }, 1000);
};

const stopLiveTimer = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
        timerInterval.value = null;
    }
};

watch(
    runningEntry,
    (entry) => {
        if (entry) {
            startLiveTimer();
        } else {
            stopLiveTimer();
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    stopLiveTimer();
});

const deleteTimeEntry = (entryId: number) => {
    router.delete(`/tasks/${props.taskId}/time-entries/${entryId}`, {
        preserveScroll: true,
    });
};

const formatDuration = (seconds: number): string => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;

    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

const formatDateTime = (dateString: string): string => {
    const d = new Date(dateString);

    return d.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Time Entries</h3>
                <span class="text-xs text-muted-foreground">
                    ({{ timeEntries.length }})
                </span>
            </div>
            <span class="text-sm font-medium text-muted-foreground">
                Total {{ formatDuration(totalSeconds) }}
            </span>
        </div>

        <!-- Running timer banner -->
        <div
            v-if="runningEntry"
            class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-900/50 dark:bg-blue-950/20"
        >
            <div class="flex items-center gap-3">
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40"
                >
                    <Pause
                        class="h-4 w-4 animate-pulse text-blue-600 dark:text-blue-400"
                    />
                </span>
                <div>
                    <p class="text-sm font-semibold text-foreground">
                        Tracking active
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Started {{ formatDateTime(runningEntry.started_at) }}
                        <span v-if="runningEntry.user">
                            &middot; {{ runningEntry.user.name }}
                        </span>
                    </p>
                </div>
            </div>
            <span
                class="font-mono text-xl font-bold text-blue-600 tabular-nums dark:text-blue-400"
            >
                {{ formatDuration(runningSeconds) }}
            </span>
        </div>

        <!-- Closed entries list -->
        <div class="space-y-2">
            <div
                v-for="entry in closedEntries"
                :key="entry.id"
                class="flex items-center justify-between rounded-lg border p-3"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium">
                        {{ entry.description || 'Auto-logged work session' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDateTime(entry.started_at) }}
                        &mdash;
                        {{ formatDateTime(entry.ended_at!) }}
                        &middot;
                        <span class="font-medium text-foreground tabular-nums">
                            {{ formatDuration(entry.duration_seconds) }}
                        </span>
                        <span v-if="entry.is_billable" class="text-emerald-600">
                            &middot; Billable
                        </span>
                        <span v-if="entry.hourly_rate">
                            &middot; ${{ entry.hourly_rate }}/h
                        </span>
                        <span v-if="entry.user">
                            &middot; {{ entry.user.name }}
                        </span>
                    </p>
                </div>
                <Button
                    size="icon"
                    variant="ghost"
                    class="h-7 w-7 shrink-0 text-muted-foreground hover:text-destructive"
                    @click="deleteTimeEntry(entry.id)"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </Button>
            </div>

            <p
                v-if="!timeEntries.length"
                class="py-8 text-center text-sm text-muted-foreground"
            >
                No time entries yet. Time is tracked automatically when you move
                a task to "In Progress".
            </p>
        </div>
    </div>
</template>
