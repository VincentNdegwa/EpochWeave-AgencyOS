<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { BellIcon, BellOffIcon } from '@lucide/vue';
import { ref } from 'vue';
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
const notifications = page.props.notifications as Notification[];
const open = ref(false);

const unreadCount = notifications.length;

const getNotificationData = (notification: Notification) => {
    if (typeof notification.data === 'string') {
        return JSON.parse(notification.data);
    }

    return notification.data;
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
                    <CommandGroup heading="Notifications">
                        <ScrollArea class="h-80">
                            <div
                                v-if="notifications.length === 0"
                                class="flex flex-col items-center justify-center gap-2 py-12 text-center"
                            >
                                <BellOffIcon class="h-12 w-12 text-muted-foreground" />
                                <p class="text-sm text-muted-foreground">No notifications</p>
                            </div>
                            <CommandItem
                                v-for="notification in notifications"
                                v-else
                                :key="notification.id"
                                class="flex flex-col items-start gap-1 p-4"
                            >
                                <div class="flex w-full items-center justify-between">
                                    <span class="text-sm font-medium">
                                        {{ getNotificationData(notification).title || 'Notification' }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ new Date(notification.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ getNotificationData(notification).message || getNotificationData(notification) }}
                                </p>
                            </CommandItem>
                        </ScrollArea>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
