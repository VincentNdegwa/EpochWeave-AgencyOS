<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Ban,
    Bell,
    ChevronDown,
    Copy,
    CreditCard,
    Download,
    Link as LinkIcon,
    MoreHorizontal,
    Pencil,
    RotateCcw,
    Send,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Invoice } from '@/types/models/invoice';
import type { InvoiceStatus } from '@/types/models/invoice_status';
import RecordPaymentDialog from '../dialogs/RecordPaymentDialog.vue';
import RecordRefundDialog from '../dialogs/RecordRefundDialog.vue';

interface Props {
    invoice: Invoice;
    invoice_statuses: InvoiceStatus[];
    variant?: 'dropdown' | 'split';
    size?: 'sm' | 'default' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'dropdown',
    size: 'sm',
});

const recordPaymentOpen = ref(false);
const recordRefundOpen = ref(false);

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

const status = computed(() => props.invoice.invoice_status?.automation_trigger ?? 'draft');

const viewAction: ActionItem = {
    label: 'View',
    icon: LinkIcon,
    handler: () => {
        router.visit(InvoiceController.show(props.invoice.id).url);
    },
};

const editAction: ActionItem = {
    label: 'Edit Invoice',
    icon: Pencil,
    handler: () => {
        router.visit(InvoiceController.edit(props.invoice.id).url);
    },
};

const sendAction: ActionItem = {
    label: 'Send Invoice',
    icon: Send,
    handler: () => {
        router.post(`/invoices/${props.invoice.id}/send`);
    },
};

const recordPaymentAction: ActionItem = {
    label: 'Record Payment',
    icon: CreditCard,
    primary: true,
    handler: () => {
        recordPaymentOpen.value = true;
    },
};

const resendEmailAction: ActionItem = {
    label: 'Resend',
    icon: Send,
    handler: () => {
        router.post(`/invoices/${props.invoice.id}/send`);
    },
};

const copyPublicLinkAction: ActionItem = {
    label: 'Copy Public Link',
    icon: LinkIcon,
    handler: () => {
        const url = `${window.location.origin}/invoices/${props.invoice.token}/public`;
        navigator.clipboard.writeText(url).then(() => {
            toast.success('Public link copied to clipboard');
        }).catch(() => {
            toast.error('Failed to copy public link');
        });
    },
};

const voidAction: ActionItem = {
    label: 'Void',
    icon: Ban,
    destructive: true,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Void Invoice',
                description:
                    'Are you sure you want to void this invoice? This action cannot be undone.',
                confirmText: 'Void',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {

            const voidStatus = props.invoice_statuses.find(
                (s) => s.automation_trigger === 'voided',
            );

            if (voidStatus) {
                router.patch(`/invoices/${props.invoice.id}/status`, {
                    invoice_status_id: voidStatus.id,
                });
            }
        }
    },
};

const downloadReceiptAction: ActionItem = {
    label: 'Download Receipt',
    icon: Download,
    primary: true,
    handler: () => {
        window.open(InvoiceController.downloadReceipt(props.invoice.id).url, '_blank');
    },
};

const recordRefundAction: ActionItem = {
    label: 'Issue Credit Note',
    icon: RotateCcw,
    handler: () => {
        recordRefundOpen.value = true;
    },
};

const duplicateAction: ActionItem = {
    label: 'Duplicate',
    icon: Copy,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Duplicate Invoice',
                description: 'This will create a copy of this invoice as a new draft.',
                confirmText: 'Duplicate',
                cancelText: 'Cancel',
            })
        ) {
            router.post(InvoiceController.duplicate(props.invoice.id).url);
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
                title: 'Delete Invoice',
                description:
                    'Are you sure you want to delete this invoice? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                variant: 'destructive',
            })
        ) {
            router.delete(InvoiceController.destroy(props.invoice.id).url);
        }
    },
};

const sendLateReminderAction: ActionItem = {
    label: 'Send Late Reminder',
    icon: Bell,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Send Late Reminder',
                description: 'This will send a late payment reminder email to the invoice contact.',
                confirmText: 'Send',
                cancelText: 'Cancel',
            })
        ) {
            router.post(InvoiceController.sendReminder(props.invoice.id).url);
        }
    },
};

const duplicateToDraftAction: ActionItem = {
    label: 'Duplicate to Draft',
    icon: Copy,
    primary: true,
    handler: async () => {
        const { confirm } = await import('@/composables/useConfirmation');

        if (
            await confirm({
                title: 'Duplicate to Draft',
                description: 'This will create a copy of this invoice as a new draft.',
                confirmText: 'Duplicate',
                cancelText: 'Cancel',
            })
        ) {
            router.post(InvoiceController.duplicate(props.invoice.id).url);
        }
    },
};

const allActions = computed((): ActionItem[] => {
    switch (status.value) {
        case 'draft':
            return [
                viewAction,
                editAction,
                sendAction,
                deleteAction,
            ];
        case 'sent':
            return [
                viewAction,
                recordPaymentAction,
                resendEmailAction,
                copyPublicLinkAction,
                voidAction,
            ];
        case 'paid':
            return [
                viewAction,
                downloadReceiptAction,
                recordRefundAction,
                duplicateAction,
            ];
        case 'overdue':
            return [
                viewAction,
                recordPaymentAction,
                sendLateReminderAction,
                copyPublicLinkAction,
                voidAction,
            ];
        case 'voided':
            return [
                viewAction,
                duplicateToDraftAction,
            ];
        default:
            return [viewAction];
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
            :class="[
                sizeClasses.button,
                'rounded-r-none border-r-0',
            ]"
            @mousedown.stop
            @mouseup.stop
            @click.stop="primaryAction.handler"
        >
            <component :is="primaryAction.icon" :class="sizeClasses.iconSize" />
            {{ primaryAction.label }}
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
                <template v-for="(item, idx) in splitDropdownItems" :key="item.label">
                    <DropdownMenuSeparator v-if="idx > 0 && item.destructive" />
                    <DropdownMenuItem
                        :class="[
                            sizeClasses.menuItem,
                            item.destructive ? 'text-destructive focus:text-destructive' : '',
                        ]"
                        @click="item.handler"
                    >
                        <component :is="item.icon" :class="sizeClasses.iconSize" />
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
                    v-if="idx > 0 && (item.destructive || item.label === 'Delete')"
                />
                <DropdownMenuItem
                    v-if="item.label === 'View'"
                    :class="sizeClasses.menuItem"
                    as-child
                >
                    <Link :href="InvoiceController.show(invoice.id).url">
                        <component :is="item.icon" :class="sizeClasses.iconSize" />
                        {{ item.label }}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem
                    v-else
                    :class="[
                        sizeClasses.menuItem,
                        item.destructive ? 'text-destructive focus:text-destructive' : '',
                    ]"
                    @click="item.handler"
                >
                    <component :is="item.icon" :class="sizeClasses.iconSize" />
                    {{ item.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>

    <RecordPaymentDialog
        :open="recordPaymentOpen"
        :invoice="props.invoice"
        @update:open="recordPaymentOpen = $event"
    />
    <RecordRefundDialog
        :open="recordRefundOpen"
        :invoice="props.invoice"
        @update:open="recordRefundOpen = $event"
    />
</template>
