<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Clock, Play, Square, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { TimeEntry } from '@/types/models/time_entry';

const props = defineProps<{
    taskId: number;
    projectId: number;
    timeEntries: TimeEntry[];
}>();

const isRunning = ref(false);
const elapsedSeconds = ref(0);
const timerInterval = ref<ReturnType<typeof setInterval> | null>(null);
const description = ref('');
const hourlyRate = ref('');
const isBillable = ref(true);

const startTimer = () => {
    isRunning.value = true;
    timerInterval.value = setInterval(() => {
        elapsedSeconds.value++;
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
        timerInterval.value = null;
    }

    isRunning.value = false;

    const startedAt = new Date(Date.now() - elapsedSeconds.value * 1000);
    const endedAt = new Date();

    router.post(`/tasks/${props.taskId}/time-entries`, {
        project_id: props.projectId,
        description: description.value || null,
        started_at: startedAt.toISOString(),
        ended_at: endedAt.toISOString(),
        is_billable: isBillable.value,
        hourly_rate: hourlyRate.value ? parseFloat(hourlyRate.value) : null,
        date: endedAt.toISOString().split('T')[0],
    }, {
        preserveScroll: true,
        onFinish: () => {
            elapsedSeconds.value = 0;
            description.value = '';
        },
    });
};

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

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};

const totalHours = computed(() => {
    const totalSeconds = props.timeEntries.reduce((sum, entry) => sum + entry.duration_seconds, 0);

    return (totalSeconds / 3600).toFixed(2);
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Time Entries</h3>
                <span class="text-xs text-muted-foreground">({{ timeEntries.length }})</span>
            </div>
            <span class="text-sm text-muted-foreground">Total: {{ totalHours }}h</span>
        </div>

        <div class="rounded-lg border p-4">
            <div class="flex items-center gap-4">
                <div class="flex-1 space-y-2">
                    <Textarea
                        v-model="description"
                        placeholder="What are you working on?"
                        rows="2"
                    />
                    <div class="flex items-center gap-3">
                        <Label class="flex items-center gap-1.5 text-sm">
                            <Input
                                type="checkbox"
                                :checked="isBillable"
                                class="h-4 w-4"
                                @change="isBillable = ($event.target as HTMLInputElement).checked"
                            />
                            Billable
                        </Label>
                        <Input
                            v-model="hourlyRate"
                            type="number"
                            placeholder="Hourly rate"
                            class="w-32"
                        />
                    </div>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <span class="text-2xl font-mono font-semibold">{{ formatDuration(elapsedSeconds) }}</span>
                    <Button
                        v-if="!isRunning"
                        size="sm"
                        class="gap-1"
                        @click="startTimer"
                    >
                        <Play class="h-4 w-4" />
                        Start
                    </Button>
                    <Button
                        v-else
                        size="sm"
                        variant="destructive"
                        class="gap-1"
                        @click="stopTimer"
                    >
                        <Square class="h-4 w-4" />
                        Stop
                    </Button>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <div
                v-for="entry in timeEntries"
                :key="entry.id"
                class="flex items-center justify-between rounded-lg border p-3"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium">{{ entry.description || 'No description' }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDate(entry.date) }} &middot; {{ formatDuration(entry.duration_seconds) }}
                        <span v-if="entry.is_billable">&middot; Billable</span>
                        <span v-if="entry.user">&middot; {{ entry.user.name }}</span>
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

            <p v-if="!timeEntries.length" class="text-sm text-muted-foreground">No time entries yet.</p>
        </div>
    </div>
</template>
