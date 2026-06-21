<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ChevronDown,
    Edit,
    FileText,
    FolderKanban,
    Link as LinkIcon,
    MoreHorizontal,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import ProjectFormDialog from '@/pages/projects/dialogs/ProjectFormDialog.vue';
import ProposalFormDialog from '@/pages/proposals/dialogs/ProposalFormDialog.vue';
import type { Account } from '@/types/models/account';
import AccountFormDialog from '../dialogs/AccountFormDialog.vue';

interface Props {
    account: Account;
    variant?: 'dropdown' | 'split';
    size?: 'sm' | 'default' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'dropdown',
    size: 'sm',
});

const isEditDialogOpen = ref(false);
const isProposalDialogOpen = ref(false);
const isProjectDialogOpen = ref(false);

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

const viewAction: ActionItem = {
    label: 'View',
    icon: LinkIcon,
    handler: () => {
        router.visit(AccountController.show(props.account.id).url);
    },
};

const editAction: ActionItem = {
    label: 'Edit Account',
    icon: Edit,
    handler: () => {
        isEditDialogOpen.value = true;
    },
};

const createProposalAction: ActionItem = {
    label: 'Create Proposal',
    icon: FileText,
    handler: () => {
        isProposalDialogOpen.value = true;
    },
};

const createProjectAction: ActionItem = {
    label: 'Create Project',
    icon: FolderKanban,
    handler: () => {
        isProjectDialogOpen.value = true;
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
                title: 'Delete Account',
                description:
                    'Are you sure you want to delete this account? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.delete(AccountController.destroy(props.account.id).url);
        }
    },
};

const allActions = computed((): ActionItem[] => {
    return [
        viewAction,
        editAction,
        createProposalAction,
        createProjectAction,
        deleteAction,
    ];
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
            @click.stop="primaryAction.handler()"
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
                    @click.stop
                >
                    <ChevronDown
                        v-if="primaryAction"
                        :class="sizeClasses.iconSize"
                    />
                    <MoreHorizontal v-else :class="sizeClasses.iconSize" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    v-for="action in splitDropdownItems"
                    :key="action.label"
                    :class="[
                        sizeClasses.menuItem,
                        action.destructive ? 'text-destructive' : '',
                    ]"
                    @click="action.handler()"
                >
                    <component
                        :is="action.icon"
                        :class="sizeClasses.iconSize"
                    />
                    {{ action.label }}
                </DropdownMenuItem>
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
            <DropdownMenuItem
                v-for="action in dropdownActions"
                :key="action.label"
                :class="[
                    sizeClasses.menuItem,
                    action.destructive ? 'text-destructive' : '',
                ]"
                @click="action.handler()"
            >
                <component :is="action.icon" :class="sizeClasses.iconSize" />
                {{ action.label }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <AccountFormDialog
        :open="isEditDialogOpen"
        :account="props.account"
        @update:open="isEditDialogOpen = $event"
    />
    <ProposalFormDialog
        :open="isProposalDialogOpen"
        :account="props.account"
        @update:open="isProposalDialogOpen = $event"
        @success="isProposalDialogOpen = false"
    />
    <ProjectFormDialog
        :open="isProjectDialogOpen"
        :account="props.account"
        @update:open="isProjectDialogOpen = $event"
        @success="isProjectDialogOpen = false"
    />
</template>
