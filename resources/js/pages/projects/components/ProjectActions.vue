<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Archive,
    CheckCircle2,
    ChevronDown,
    Edit,
    Link as LinkIcon,
    MoreHorizontal,
    Pause,
    Play,
    Trash2,
    XCircle,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Project } from '@/types/models/project';
import type { ProjectStatus } from '@/types/models/project_status';
import ProjectFormDialog from '../dialogs/ProjectFormDialog.vue';

interface Props {
    project: Project;
    project_statuses: ProjectStatus[];
    variant?: 'dropdown' | 'split';
    size?: 'sm' | 'default' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'dropdown',
    size: 'sm',
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

const status = computed(
    () => props.project.status?.automation_trigger ?? 'planning',
);

const getStatusId = (trigger: string): number | undefined =>
    props.project_statuses.find((s) => s.automation_trigger === trigger)?.id;

const viewAction: ActionItem = {
    label: 'View',
    icon: LinkIcon,
    handler: () => {
        router.visit(ProjectController.show(props.project.id).url);
    },
};

const editAction: ActionItem = {
    label: 'Edit',
    icon: Edit,
    handler: () => {
        isEditDialogOpen.value = true;
    },
};

const activateAction: ActionItem = {
    label: 'Activate Project',
    icon: Play,
    primary: true,
    handler: () => {
        const activeStatusId = getStatusId('active');

        if (activeStatusId) {
            router.patch(ProjectController.updateStatus(props.project.id).url, {
                project_status_id: activeStatusId,
            });
        }
    },
};

const pauseAction: ActionItem = {
    label: 'Place on Hold',
    icon: Pause,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Place on Hold',
                description:
                    'Are you sure you want to place this project on hold?',
                confirmText: 'Place on Hold',
                cancelText: 'Cancel',
            })
        ) {
            const pausedStatusId = getStatusId('paused');

            if (pausedStatusId) {
                router.patch(ProjectController.updateStatus(props.project.id).url, {
                    project_status_id: pausedStatusId,
                });
            }
        }
    },
};

const completeAction: ActionItem = {
    label: 'Complete Project',
    icon: CheckCircle2,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Complete Project',
                description:
                    'This will mark the project as completed and seal financials.',
                confirmText: 'Complete',
                cancelText: 'Cancel',
            })
        ) {
            const completedStatusId = getStatusId('completed');

            if (completedStatusId) {
                router.patch(ProjectController.updateStatus(props.project.id).url, {
                    project_status_id: completedStatusId,
                });
            }
        }
    },
};

const cancelAction: ActionItem = {
    label: 'Cancel Project',
    icon: XCircle,
    destructive: true,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Cancel Project',
                description:
                    'Are you sure you want to cancel this project? This will cancel pending tasks.',
                confirmText: 'Cancel Project',
                cancelText: 'Keep Project',
                variant: 'destructive',
            })
        ) {
            const cancelledStatusId = getStatusId('cancelled');

            if (cancelledStatusId) {
                router.patch(ProjectController.updateStatus(props.project.id).url, {
                    project_status_id: cancelledStatusId,
                });
            }
        }
    },
};

const archiveAction: ActionItem = {
    label: 'Archive Project',
    icon: Archive,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Archive Project',
                description:
                    'This will archive the project. It will no longer appear in active lists.',
                confirmText: 'Archive',
                cancelText: 'Cancel',
            })
        ) {
            router.patch(ProjectController.archive(props.project.id).url);
        }
    },
};

const deleteAction: ActionItem = {
    label: 'Delete',
    icon: Trash2,
    destructive: true,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Delete Project',
                description:
                    'Are you sure you want to delete this project? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.delete(ProjectController.destroy(props.project.id).url);
        }
    },
};

const allActions = computed((): ActionItem[] => {
    switch (status.value) {
        case 'planning':
            return [
                viewAction,
                editAction,
                activateAction,
                cancelAction,
                deleteAction,
            ];
        case 'active':
            return [
                viewAction,
                editAction,
                pauseAction,
                completeAction,
                cancelAction,
            ];
        case 'paused':
            return [
                viewAction,
                editAction,
                activateAction,
                cancelAction,
            ];
        case 'completed':
            if (!props.project.archived_at) {
                return [viewAction, editAction, archiveAction];
            }

            return [viewAction, editAction];
        case 'cancelled':
            if (!props.project.archived_at) {
                return [viewAction, editAction, archiveAction];
            }

            return [viewAction, editAction];
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
                    variant="outline"
                    :size="size === 'icon' ? 'sm' : size"
                    :class="[
                        sizeClasses.iconButton,
                        primaryAction ? 'rounded-l-none px-2' : '',
                    ]"
                    @mousedown.stop
                    @mouseup.stop
                >
                    <ChevronDown :class="sizeClasses.iconSize" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56">
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

    <DropdownMenu v-else>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                :class="sizeClasses.iconButton"
                size="icon"
                @mousedown.stop
                @mouseup.stop
            >
                <MoreHorizontal :class="sizeClasses.iconSize" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
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
                    <Link :href="ProjectController.show(project.id).url">
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

    <ProjectFormDialog
        :open="isEditDialogOpen"
        :project="props.project"
        :project_statuses="props.project_statuses"
        @update:open="isEditDialogOpen = $event"
        @success="isEditDialogOpen = false"
    />
</template>
