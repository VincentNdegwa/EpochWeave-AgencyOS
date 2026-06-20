<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Hash,
    Plus,
    UserRound,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import type { Account, AccountContact } from '@/types/models/account';
import AccountActions from './components/AccountActions.vue';
import { createContactColumns } from './contacts-datatable/columns';
import ContactsDataTable from './contacts-datatable/data-table.vue';
import ContactFormDialog from './dialogs/ContactFormDialog.vue';

const props = defineProps<{
    account: Account;
    activities: { id: number; type: string; description: string; created_at: string; user?: { id: number; name: string } | null }[];
}>();

const { getVariant, getLabel } = useAccountStatuses();
const { format: formatCurrency } = useCurrency();

const contactDialogOpen = ref(false);
const editingContact = ref<AccountContact | null>(null);

const contactColumns = createContactColumns(props.account.id);

const openNewContactDialog = () => {
    editingContact.value = null;
    contactDialogOpen.value = true;
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

    <div class="flex flex-col">
        <!-- Header -->
        <div class="sticky top-0 z-30 border-b pb-4 border-border bg-background/95 backdrop-blur-sm print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            :variant="getVariant(props.account.status)"
                            class="rounded-md text-[11px]"
                        >
                            {{ getLabel(props.account.status) }}
                        </Badge>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ props.account.company_name }}
                        <span class="font-normal text-muted-foreground">· {{ formatCurrency(props.account.lifetime_value) }}</span>
                    </h1>
                </div>
                <AccountActions
                    :account="props.account"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <!-- Stats -->
        <div class="border-b border-border bg-background print:hidden">
            <div class="grid grid-cols-2 divide-x divide-border sm:grid-cols-4">
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Lifetime Value</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums text-foreground">{{ formatCurrency(props.account.lifetime_value) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Website</p>
                    <p class="mt-0.5 text-base font-bold text-foreground truncate">
                        <a
                            v-if="props.account.website"
                            :href="props.account.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="hover:underline"
                        >
                            {{ props.account.website.replace(/^https?:\/\//, '') }}
                        </a>
                        <span v-else class="text-muted-foreground">Not provided</span>
                    </p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Contacts</p>
                    <p class="mt-0.5 text-xl font-bold text-foreground">{{ props.account.contacts?.length || 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">People</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Status</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ getLabel(props.account.status) }}</p>
                    <p class="text-[10px] text-muted-foreground">Account</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 pt-6">

        <!-- Contacts Section -->
        <Card class="gap-0 py-0 shadow-none">
            <!-- Card Header -->
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex items-center gap-2">
                    <UserRound class="h-4 w-4 text-muted-foreground" />
                    <h2 class="text-sm font-semibold">Contacts</h2>
                    <span
                        class="rounded-md bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground"
                    >
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
                    v-if="
                        props.account.contacts &&
                        props.account.contacts.length > 0
                    "
                    :columns="contactColumns"
                    :data="props.account.contacts"
                />
                <div
                    v-else
                    class="flex flex-col items-center gap-2 py-12 text-muted-foreground"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-muted"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                    <p class="text-sm font-medium">No contacts yet</p>
                    <p class="text-xs">
                        Add the first contact for this account.
                    </p>
                </div>
            </div>
        </Card>

        <!-- Activity Timeline -->
        <Card class="gap-0 py-0 shadow-none">
            <div class="flex items-center gap-2 px-5 py-4">
                <h2 class="text-sm font-semibold">Activity</h2>
            </div>
            <Separator />
            <div class="px-5 py-4">
                <ActivityTimeline :activities="props.activities" />
            </div>
        </Card>

        <ContactFormDialog
            v-model:open="contactDialogOpen"
            :account-id="props.account.id"
            :contact="editingContact"
        />
    </div>
    </div>
</template>
