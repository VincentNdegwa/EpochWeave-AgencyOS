<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Key, Lock, MoreHorizontal, Pencil, Star, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { AccountContact } from '@/types/models/account';
import ContactFormDialog from '../dialogs/ContactFormDialog.vue';

interface Props {
    contact: AccountContact;
    accountId: number;
}

const props = defineProps<Props>();

const isEditDialogOpen = ref(false);

const handleEdit = () => {
    isEditDialogOpen.value = true;
};

const handleSetPrimary = () => {
    router.patch(
        `/accounts/${props.accountId}/contacts/${props.contact.id}/primary`,
    );
};

const handleGrantPortal = () => {
    router.post(
        `/accounts/${props.accountId}/contacts/${props.contact.id}/portal`,
    );
};

const handleRevokePortal = async () => {
    const { confirm } = await import('@/composables/useConfirmation');

    if (
        await confirm({
            title: 'Revoke Portal Access',
            description:
                'Are you sure you want to revoke portal access for this contact? This action cannot be undone.',
            confirmText: 'Revoke',
            cancelText: 'Cancel',
            variant: 'destructive',
        })
    ) {
        router.delete(
            `/accounts/${props.accountId}/contacts/${props.contact.id}/portal`,
        );
    }
};

const handleDelete = async () => {
    const { confirm } = await import('@/composables/useConfirmation');

    if (
        await confirm({
            title: 'Delete Contact',
            description:
                'Are you sure you want to delete this contact? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'destructive',
        })
    ) {
        router.delete(
            `/accounts/${props.accountId}/contacts/${props.contact.id}`,
        );
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0" @click.stop>
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem @click="handleEdit">
                <Pencil class="mr-2 h-4 w-4" />
                Edit
            </DropdownMenuItem>
            <DropdownMenuItem
                v-if="!contact.is_primary"
                @click="handleSetPrimary"
            >
                <Star class="mr-2 h-4 w-4" />
                Set as Primary
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-if="!contact.client_profile_id"
                @click="handleGrantPortal"
            >
                <Key class="mr-2 h-4 w-4" />
                Grant Portal Access
            </DropdownMenuItem>
            <DropdownMenuItem
                v-if="contact.client_profile_id"
                class="text-destructive"
                @click="handleRevokePortal"
            >
                <Lock class="mr-2 h-4 w-4" />
                Revoke Portal Access
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-destructive" @click="handleDelete">
                <Trash2 class="mr-2 h-4 w-4" />
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <ContactFormDialog
        :open="isEditDialogOpen"
        :account-id="props.accountId"
        :contact="props.contact"
        @update:open="isEditDialogOpen = $event"
    />
</template>
