<script setup lang="ts">
import {
    PlusIcon,
    PencilIcon,
    Trash2Icon,
    EyeIcon,
    SendIcon,
    CheckCircle2Icon,
    XCircleIcon,
    BanknoteIcon,
    ActivityIcon,
} from '@lucide/vue';
import { computed } from 'vue';

interface ActivityItem {
    id: number;
    type: string;
    description: string;
    created_at: string;
    user?: { id: number; name: string } | null;
}

const props = defineProps<{ activities: ActivityItem[] }>();

type IconDef = { icon: unknown; cls: string };

const iconDefs: Record<string, IconDef> = {
    created: { icon: PlusIcon, cls: 'text-muted-foreground bg-muted' },
    updated: { icon: PencilIcon, cls: 'text-muted-foreground bg-muted' },
    deleted: { icon: Trash2Icon, cls: 'text-destructive bg-destructive/10' },
    viewed: { icon: EyeIcon, cls: 'text-muted-foreground bg-muted' },
    sent: {
        icon: SendIcon,
        cls: 'text-blue-600 bg-blue-50 dark:bg-blue-950/40',
    },
    accepted: {
        icon: CheckCircle2Icon,
        cls: 'text-green-600 bg-green-50 dark:bg-green-950/40',
    },
    declined: { icon: XCircleIcon, cls: 'text-destructive bg-destructive/10' },
    paid: {
        icon: BanknoteIcon,
        cls: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40',
    },
    payment: {
        icon: BanknoteIcon,
        cls: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40',
    },
};

function resolveIconDef(type: string): IconDef {
    const key = Object.keys(iconDefs).find((k) => type.includes(k));

    return key
        ? iconDefs[key]
        : { icon: ActivityIcon, cls: 'text-muted-foreground bg-muted' };
}

function relativeTime(iso: string): string {
    const diffMs = Date.now() - new Date(iso).getTime();
    const sec = Math.floor(diffMs / 1000);
    const min = Math.floor(sec / 60);
    const hr = Math.floor(min / 60);
    const day = Math.floor(hr / 24);

    if (sec < 60) {
        return 'now';
    }

    if (min < 60) {
        return `${min}m`;
    }

    if (hr < 24) {
        return `${hr}h`;
    }

    if (day < 7) {
        return `${day}d`;
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
    }).format(new Date(iso));
}

function fullTimestamp(iso: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
}

const hasActivity = computed(() => props.activities.length > 0);
</script>

<template>
    <div class="flow-root">
        <div
            v-if="!hasActivity"
            class="flex flex-col items-center gap-2 py-10 text-center"
        >
            <ActivityIcon class="h-5 w-5 text-muted-foreground/30" />
            <p class="text-xs text-muted-foreground">No activity yet</p>
        </div>

        <ul v-else role="list" class="space-y-0">
            <li
                v-for="(activity, index) in activities"
                :key="activity.id"
                class="group relative flex gap-3 py-2"
            >
                <span
                    v-if="index < activities.length - 1"
                    class="absolute top-7 bottom-0 left-[13px] w-px bg-border"
                    aria-hidden="true"
                />

                <span
                    class="relative z-10 mt-0.5 flex h-[26px] w-[26px] flex-shrink-0 items-center justify-center rounded-full"
                    :class="resolveIconDef(activity.type).cls"
                >
                    <component
                        :is="resolveIconDef(activity.type).icon"
                        class="h-3 w-3"
                    />
                </span>

                <div
                    class="flex min-w-0 flex-1 items-start justify-between gap-3 pt-0.5"
                >
                    <div class="min-w-0">
                        <p class="text-[13px] leading-snug text-foreground">
                            <span v-if="activity.user" class="font-medium">{{
                                activity.user.name
                            }}</span>
                            <span
                                v-if="activity.user"
                                class="text-muted-foreground"
                            >
                                ·
                            </span>
                            <span
                                :class="
                                    activity.user ? 'text-muted-foreground' : ''
                                "
                                >{{ activity.description }}</span
                            >
                        </p>
                    </div>

                    <time
                        :datetime="activity.created_at"
                        :title="fullTimestamp(activity.created_at)"
                        class="flex-shrink-0 cursor-default text-[11px] whitespace-nowrap text-muted-foreground/70 tabular-nums"
                    >
                        {{ relativeTime(activity.created_at) }}
                    </time>
                </div>
            </li>
        </ul>
    </div>
</template>
