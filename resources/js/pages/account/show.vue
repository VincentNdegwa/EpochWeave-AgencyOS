<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { DollarSign, Edit, ExternalLink, Globe, Plus, Trash2, UserRound, Users } from '@lucide/vue';
import { computed, ref } from 'vue';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import type { Account, AccountContact } from '@/types/models/account';
import { createContactColumns } from './contacts-datatable/columns';
import ContactsDataTable from './contacts-datatable/data-table.vue';
import AccountFormDialog from './dialogs/AccountFormDialog.vue';
import ContactFormDialog from './dialogs/ContactFormDialog.vue';

const props = defineProps<{
    account: Account;
}>();

const { getVariant, getLabel } = useAccountStatuses();
const { format: formatCurrency } = useCurrency();

const accountDialogOpen = ref(false);
const contactDialogOpen = ref(false);
const editingContact = ref<AccountContact | null>(null);

const companyInitials = computed(() => {
    return props.account.company_name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
});

const contactColumns = createContactColumns(
    (contact) => {
        editingContact.value = contact;
        contactDialogOpen.value = true;
    },
    (contact) => {
        router.delete(`/accounts/${props.account.id}/contacts/${contact.id}`);
    },
);

const openNewContactDialog = () => {
    editingContact.value = null;
    contactDialogOpen.value = true;
};

const deleteAccount = async () => {
    const { confirm } = await import('@/composables/useConfirmation');

    if (
        await confirm({
            title: 'Delete Account',
            description: 'Are you sure you want to delete this account? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'destructive',
        })
    ) {
        router.delete(AccountController.destroy(props.account.id).url);
    }
};

defineOptions({
    layout: {
        title: 'Account Details',
        description: 'View account information',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Accounts',
                href: accountIndex(),
            },
            {
                title: 'Account Details',
            },
        ],
    },
});
</script>

<template>
    <Head title="Account Details" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Company Avatar -->
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-lg font-semibold text-primary">
                    {{ companyInitials }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-semibold tracking-tight">{{ props.account.company_name }}</h1>
                        <Badge :variant="getVariant(props.account.status)" class="text-xs">
                            {{ getLabel(props.account.status) }}
                        </Badge>
                    </div>
                    <p class="mt-0.5 text-sm text-muted-foreground">Account details and contacts</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <Button type="button" variant="outline" size="sm" class="gap-2" @click="accountDialogOpen = true">
                    <Edit class="h-3.5 w-3.5" />
                    Edit
                </Button>
                <Button type="button" variant="ghost" size="sm" class="gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive" @click="deleteAccount">
                    <Trash2 class="h-3.5 w-3.5" />
                    Delete
                </Button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Website -->
            <Card class="gap-0 py-0 shadow-none">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Website</p>
                            <div class="mt-2">
                                <a
                                    v-if="props.account.website"
                                    :href="props.account.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
                                >
                                    <span class="truncate">{{ props.account.website.replace(/^https?:\/\//, '') }}</span>
                                    <ExternalLink class="h-3.5 w-3.5 shrink-0" />
                                </a>
                                <span v-else class="text-sm text-muted-foreground">Not provided</span>
                            </div>
                        </div>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10">
                            <Globe class="h-4 w-4 text-blue-500" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Lifetime Value -->
            <Card class="gap-0 py-0 shadow-none">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Lifetime Value</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">
                                {{ formatCurrency(props.account.lifetime_value) }}
                            </p>
                        </div>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                            <DollarSign class="h-4 w-4 text-emerald-500" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Total Contacts -->
            <Card class="gap-0 py-0 shadow-none">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Contacts</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">
                                {{ props.account.contacts?.length || 0 }}
                            </p>
                        </div>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/10">
                            <Users class="h-4 w-4 text-violet-500" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Contacts Section -->
        <Card class="gap-0 py-0 shadow-none">
            <!-- Card Header -->
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex items-center gap-2">
                    <UserRound class="h-4 w-4 text-muted-foreground" />
                    <h2 class="text-sm font-semibold">Contacts</h2>
                    <span class="rounded-md bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground">
                        {{ props.account.contacts?.length || 0 }}
                    </span>
                </div>
                <Button size="sm" class="gap-2" @click="openNewContactDialog">
                    <Plus class="h-3.5 w-3.5" />
                    Add Contact
                </Button>
            </div>

            <Separator />

            <!-- Contacts Table -->
            <div class="p-0">
                <ContactsDataTable
                    v-if="props.account.contacts && props.account.contacts.length > 0"
                    :columns="contactColumns"
                    :data="props.account.contacts"
                />
                <div v-else class="flex flex-col items-center gap-2 py-12 text-muted-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                        <Users class="h-5 w-5" />
                    </div>
                    <p class="text-sm font-medium">No contacts yet</p>
                    <p class="text-xs">Add the first contact for this account.</p>
                </div>
            </div>
        </Card>

        <!-- Dialogs -->
        <ContactFormDialog
            v-model:open="contactDialogOpen"
            :account-id="props.account.id"
            :contact="editingContact"
        />
        <AccountFormDialog
            :open="accountDialogOpen"
            :account="props.account"
            @update:open="accountDialogOpen = $event"
        />
    </div>
</template>
