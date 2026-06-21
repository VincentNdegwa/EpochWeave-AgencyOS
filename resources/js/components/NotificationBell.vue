<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { BellIcon, BellOffIcon, CheckCheck } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandGroup,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import type { Notification } from '@/types/models/notification';

const page = usePage();
const notifications = computed(
    () => page.props.notifications as Notification[],
);
const open = ref(false);

const unreadCount = computed(() => notifications.value.length);

const markAsRead = (id: string) => {
    router.patch(
        `/notifications/${id}/read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
            },
        },
    );
};

const markAllAsRead = () => {
    router.patch(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
            },
        },
    );
};

const getNotificationData = (notification: Notification) => {
    if (typeof notification.data === 'string') {
        return JSON.parse(notification.data);
    }

    return notification.data;
};

const getColorClass = (color: string) => {
    const colorMap: Record<string, string> = {
        'blue-500': 'text-blue-500',
        'amber-500': 'text-amber-500',
        'green-500': 'text-green-500',
        'red-500': 'text-red-500',
    };

    return colorMap[color] || 'text-gray-500';
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <BellIcon class="h-5 w-5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] text-primary-foreground"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-80 p-0" align="end">
            <Command>
                <CommandList>
                    <CommandGroup>
                        <div
                            class="flex items-center justify-between px-3 py-2"
                        >
                            <span
                                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >Notifications</span
                            >
                            <Button
                                v-if="unreadCount > 0"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="markAllAsRead"
                            >
                                <CheckCheck class="mr-1 h-3.5 w-3.5" />
                                Mark all read
                            </Button>
                        </div>
                        <ScrollArea class="h-80">
                            <div
                                v-if="notifications.length === 0"
                                class="flex flex-col items-center justify-center gap-2 py-12 text-center"
                            >
                                <BellOffIcon
                                    class="h-12 w-12 text-muted-foreground"
                                />
                                <p class="text-sm text-muted-foreground">
                                    No notifications
                                </p>
                            </div>
                            <CommandItem
                                v-for="notification in notifications"
                                v-else
                                :key="notification.id"
                                class="flex cursor-pointer flex-col items-start gap-2 p-4"
                                @select="markAsRead(notification.id)"
                            >
                                <div class="flex w-full items-start gap-3">
                                    <Icon
                                        :icon="
                                            getNotificationData(notification)
                                                .icon || 'mdi:bell'
                                        "
                                        :class="
                                            getColorClass(
                                                getNotificationData(
                                                    notification,
                                                ).color || 'gray-500',
                                            )
                                        "
                                        class="mt-0.5 h-5 w-5 flex-shrink-0"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex w-full items-center justify-between"
                                        >
                                            <span
                                                class="truncate text-sm font-medium"
                                            >
                                                {{
                                                    getNotificationData(
                                                        notification,
                                                    ).title
                                                }}
                                            </span>
                                            <span
                                                class="ml-2 flex-shrink-0 text-xs text-muted-foreground"
                                            >
                                                {{
                                                    new Date(
                                                        notification.created_at,
                                                    ).toLocaleDateString()
                                                }}
                                            </span>
                                        </div>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            {{
                                                getNotificationData(
                                                    notification,
                                                ).message
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </CommandItem>
                        </ScrollArea>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
