<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronDown,
    ClipboardCheck,
    Edit,
    Link as LinkIcon,
    MoreHorizontal,
    Play,
    RefreshCw,
    Trash2,
    XCircle,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Tag } from '@/types/models/tag';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TaskFormDialog from '../dialogs/TaskFormDialog.vue';

interface Props {
    task: Task;
    taskStatuses?: TaskStatus[];
    tags?: Tag[];
    variant?: 'dropdown' | 'split';
    size?: 'sm' | 'default' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'dropdown',
    size: 'sm',
    taskStatuses: () => [],
    tags: () => [],
});

const isEditDialogOpen = ref(false);

interface ActionItem {
    label: string;
    icon: any;
    destructive?: boolean;
    primary?: boolean;
    handler: () => void;
}

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return {
                button: 'h-8 text-xs gap-1.5',
                iconButton: 'h-8 w-8',
                iconSize: 'h-3.5 w-3.5',
                menuItem: 'gap-2 text-xs',
            };
        case 'icon':
            return {
                button: 'h-8 text-xs gap-1.5',
                iconButton: 'h-8 w-8',
                iconSize: 'h-4 w-4',
                menuItem: 'gap-2 text-xs',
            };
        default:
            return {
                button: 'h-9 text-sm gap-2',
                iconButton: 'h-9 w-9',
                iconSize: 'h-4 w-4',
                menuItem: 'gap-2 text-sm',
            };
    }
});

const currentTrigger = computed(() => {
    const found = props.taskStatuses.find(
        (s) => s.id === props.task.task_status_id,
    );

    return found?.automation_trigger ?? 'unstarted';
});

const getStatusId = (trigger: string): number | undefined =>
    props.taskStatuses.find((s) => s.automation_trigger === trigger)?.id;

const transitionTo = (trigger: string) => {
    const statusId = getStatusId(trigger);

    if (!statusId) {
        return;
    }

    router.patch(
        TaskController.updateStatus(props.task.id).url,
        { task_status_id: statusId },
        { preserveScroll: true },
    );
};

const viewAction: ActionItem = {
    label: 'View',
    icon: LinkIcon,
    handler: () => {
        router.visit(TaskController.show(props.task.id).url);
    },
};

const editAction: ActionItem = {
    label: 'Edit',
    icon: Edit,
    handler: () => {
        isEditDialogOpen.value = true;
    },
};

const startWorkAction: ActionItem = {
    label: 'Start Work',
    icon: Play,
    primary: true,
    handler: () => transitionTo('active'),
};

const submitForReviewAction: ActionItem = {
    label: 'Submit for Review',
    icon: ClipboardCheck,
    primary: true,
    handler: () => transitionTo('review'),
};

const approveAction: ActionItem = {
    label: 'Approve & Complete',
    icon: CheckCircle2,
    primary: true,
    handler: () => transitionTo('completed'),
};

const sendBackAction: ActionItem = {
    label: 'Send Back for Edits',
    icon: RefreshCw,
    handler: () => transitionTo('active'),
};

const cancelAction: ActionItem = {
    label: 'Cancel Task',
    icon: XCircle,
    destructive: true,
    handler: () => transitionTo('cancelled'),
};

const deleteAction: ActionItem = {
    label: 'Delete',
    icon: Trash2,
    destructive: true,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Delete Task',
                description:
                    'Are you sure you want to delete this task? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.delete(TaskController.destroy(props.task.id).url);
        }
    },
};

const allActions = computed((): ActionItem[] => {
    switch (currentTrigger.value) {
        case 'backlog':
        case 'unstarted':
            return [viewAction, startWorkAction, editAction, deleteAction];
        case 'active':
            return [
                viewAction,
                submitForReviewAction,
                editAction,
                cancelAction,
                deleteAction,
            ];
        case 'review':
            return [
                viewAction,
                approveAction,
                sendBackAction,
                editAction,
                cancelAction,
                deleteAction,
            ];
        case 'completed':
        case 'cancelled':
            return [viewAction, editAction, deleteAction];
        default:
            return [viewAction, editAction, deleteAction];
    }
});

const dropdownActions = computed((): ActionItem[] => {
    if (props.variant === 'split') {
        return allActions.value.filter(
            (a) => a.label !== 'View' && a.label !== 'Delete',
        );
    }

    return allActions.value;
});

const primaryAction = computed((): ActionItem | null => {
    if (props.variant !== 'split') {
        return null;
    }

    const candidates = dropdownActions.value.filter(
        (a) => a.label !== 'View' && a.label !== 'Delete',
    );

    return candidates.find((a) => a.primary) ?? candidates[0] ?? null;
});

const splitDropdownItems = computed((): ActionItem[] => {
    if (props.variant !== 'split') {
        return [];
    }

    const primary = primaryAction.value;

    return dropdownActions.value.filter((a) => a !== primary);
});
</script>

<template>
    <!-- Split variant: primary button + dropdown -->
    <div v-if="variant === 'split'" class="flex items-center">
        <Button
            v-if="primaryAction"
            :size="size === 'icon' ? 'sm' : size"
            :class="[sizeClasses.button, 'rounded-r-none border-r-0']"
            @mousedown.stop
            @mouseup.stop
            @click.stop="primaryAction.handler"
        >
            <component :is="primaryAction.icon" :class="sizeClasses.iconSize" />
            <span class="hidden sm:inline">{{ primaryAction.label }}</span>
        </Button>

        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button
                    :size="size === 'icon' ? 'sm' : size"
                    variant="outline"
                    :class="[
                        sizeClasses.iconButton,
                        primaryAction ? 'rounded-l-none px-2' : '',
                    ]"
                    @mousedown.stop
                    @mouseup.stop
                >
                    <ChevronDown
                        v-if="primaryAction"
                        :class="sizeClasses.iconSize"
                    />
                    <MoreHorizontal v-else :class="sizeClasses.iconSize" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <template
                    v-for="(item, idx) in splitDropdownItems"
                    :key="item.label"
                >
                    <DropdownMenuSeparator v-if="idx > 0 && item.destructive" />
                    <DropdownMenuItem
                        :class="[
                            sizeClasses.menuItem,
                            item.destructive
                                ? 'text-destructive focus:text-destructive'
                                : '',
                        ]"
                        @click="item.handler"
                    >
                        <component
                            :is="item.icon"
                            :class="sizeClasses.iconSize"
                        />
                        {{ item.label }}
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>

    <!-- Dropdown variant -->
    <DropdownMenu v-else>
        <DropdownMenuTrigger as-child>
            <Button
                :size="size === 'icon' ? 'sm' : size"
                variant="ghost"
                :class="size === 'icon' ? 'h-8 w-8 p-0' : ''"
                @mousedown.stop
                @mouseup.stop
                @click.stop
            >
                <MoreHorizontal :class="sizeClasses.iconSize" />
                <span v-if="size !== 'icon'" class="ml-2">Actions</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <template v-for="(item, idx) in dropdownActions" :key="item.label">
                <DropdownMenuSeparator
                    v-if="
                        idx > 0 && (item.destructive || item.label === 'Delete')
                    "
                />
                <DropdownMenuItem
                    v-if="item.label === 'View'"
                    :class="sizeClasses.menuItem"
                    as-child
                >
                    <Link :href="TaskController.show(task.id).url">
                        <component
                            :is="item.icon"
                            :class="sizeClasses.iconSize"
                        />
                        {{ item.label }}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem
                    v-else
                    :class="[
                        sizeClasses.menuItem,
                        item.destructive
                            ? 'text-destructive focus:text-destructive'
                            : '',
                    ]"
                    @click="item.handler"
                >
                    <component :is="item.icon" :class="sizeClasses.iconSize" />
                    {{ item.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>

    <TaskFormDialog
        :open="isEditDialogOpen"
        :task="props.task"
        :task_statuses="props.taskStatuses"
        :tags="props.tags"
        @update:open="isEditDialogOpen = $event"
        @success="isEditDialogOpen = false"
    />
</template>
