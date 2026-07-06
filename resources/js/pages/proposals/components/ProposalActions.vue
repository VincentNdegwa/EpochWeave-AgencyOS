<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronDown,
    Copy,
    Edit,
    Eye,
    Link as LinkIcon,
    MoreHorizontal,
    Send,
    Trash2,
    Undo2,
    XCircle,
} from '@lucide/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Proposal } from '@/types/models/proposal';
import type { ProposalStatusModel } from '@/types/models/proposal';

interface Props {
    proposal: Proposal;
    proposal_statuses: ProposalStatusModel[];
    variant?: 'dropdown' | 'split';
    size?: 'sm' | 'default' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'dropdown',
    size: 'sm',
});

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
    () => props.proposal.proposal_status?.automation_trigger ?? 'draft',
);

const draftStatus = computed(() =>
    props.proposal_statuses.find((s) => s.automation_trigger === 'draft'),
);

const viewAction: ActionItem = {
    label: 'View',
    icon: LinkIcon,
    handler: () => {
        router.visit(ProposalController.show(props.proposal.id).url);
    },
};

const editAction: ActionItem = {
    label: 'Edit',
    icon: Edit,
    handler: () => {
        router.visit(ProposalController.edit(props.proposal.id).url);
    },
};

const clientViewAction: ActionItem = {
    label: 'Preview Client View',
    icon: Eye,
    handler: () => {
        window.open(
            `${window.location.origin}/proposals/${props.proposal.token}/public`,
            '_blank',
        );
    },
};

const copyLinkAction: ActionItem = {
    label: 'Copy Secure Link',
    icon: LinkIcon,
    handler: () => {
        const url = `${window.location.origin}/proposals/${props.proposal.token}/public`;
        navigator.clipboard
            .writeText(url)
            .then(() => {
                toast.success('Secure link copied to clipboard');
            })
            .catch(() => {
                toast.error('Failed to copy link');
            });
    },
};

const sendAction: ActionItem = {
    label: 'Send Proposal',
    icon: Send,
    primary: true,
    handler: () => {
        router.post(`/proposals/${props.proposal.id}/send`);
    },
};

const resendAction: ActionItem = {
    label: 'Resend Notification',
    icon: Send,
    handler: () => {
        router.post(`/proposals/${props.proposal.id}/send`);
    },
};

const markAcceptedAction: ActionItem = {
    label: 'Mark as Accepted',
    icon: CheckCircle2,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Mark as Accepted',
                description:
                    'This will mark the proposal as accepted and may trigger project creation.',
                confirmText: 'Accept',
                cancelText: 'Cancel',
            })
        ) {
            router.post(ProposalController.accept(props.proposal.id).url);
        }
    },
};

const markDeclinedAction: ActionItem = {
    label: 'Mark as Declined',
    icon: XCircle,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Mark as Declined',
                description:
                    'Are you sure you want to mark this proposal as declined?',
                confirmText: 'Decline',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.post(ProposalController.decline(props.proposal.id).url);
        }
    },
};

const revertToDraftAction: ActionItem = {
    label: 'Revert to Draft',
    icon: Undo2,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Revert to Draft',
                description:
                    'This will revert the proposal to draft status. The client will no longer be able to view it.',
                confirmText: 'Revert',
                cancelText: 'Cancel',
            })
        ) {
            const draftStatusId = draftStatus.value?.id;

            if (draftStatusId) {
                router.patch(`/proposals/${props.proposal.id}/move`, {
                    target_status_id: draftStatusId,
                });
            }
        }
    },
};

const duplicateToDraftAction: ActionItem = {
    label: 'Duplicate to Draft',
    icon: Copy,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Duplicate to Draft',
                description:
                    'This will create a copy of this proposal as a new draft.',
                confirmText: 'Duplicate',
                cancelText: 'Cancel',
            })
        ) {
            router.post(`/proposals/${props.proposal.id}/duplicate`);
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
                title: 'Delete Proposal',
                description:
                    'Are you sure you want to delete this proposal? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.delete(ProposalController.destroy(props.proposal.id).url);
        }
    },
};

const allActions = computed((): ActionItem[] => {
    switch (status.value) {
        case 'draft':
            return [
                viewAction,
                editAction,
                clientViewAction,
                sendAction,
                markAcceptedAction,
                markDeclinedAction,
                deleteAction,
            ];
        case 'sent':
            return [
                viewAction,
                clientViewAction,
                copyLinkAction,
                resendAction,
                markAcceptedAction,
                markDeclinedAction,
                revertToDraftAction,
            ];
        case 'accepted':
            return [
                viewAction,
                clientViewAction,
                copyLinkAction,
                duplicateToDraftAction,
            ];
        case 'declined':
            return [
                viewAction,
                clientViewAction,
                copyLinkAction,
                duplicateToDraftAction,
                deleteAction,
            ];
        case 'expired':
            return [
                viewAction,
                clientViewAction,
                copyLinkAction,
                duplicateToDraftAction,
                revertToDraftAction,
                deleteAction,
            ];
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
                    <Link :href="ProposalController.show(proposal.id).url">
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
</template>
