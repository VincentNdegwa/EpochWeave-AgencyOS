<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Activity } from '@lucide/vue';
import type { Activity as ActivityModel } from '@/types/models/activity';

defineProps<{
    activities: {
        data: ActivityModel[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();
</script>

<template>
    <Head title="Activity Log" />

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center gap-2">
            <Activity class="h-5 w-5 text-muted-foreground" />
            <h1 class="text-lg font-semibold">Activity Log</h1>
        </div>

        <div class="space-y-3">
            <div
                v-for="item in activities.data"
                :key="item.id"
                class="rounded-lg border p-4"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">{{ item.description }}</p>
                    <span class="text-xs text-muted-foreground">{{
                        new Date(item.created_at).toLocaleString()
                    }}</span>
                </div>
                <p v-if="item.user" class="mt-1 text-xs text-muted-foreground">
                    By {{ item.user.name }}
                </p>
            </div>

            <p
                v-if="!activities.data.length"
                class="text-sm text-muted-foreground"
            >
                No activities yet.
            </p>
        </div>
    </div>
</template>
