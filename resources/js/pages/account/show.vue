<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Plus, UserRound, Users } from '@lucide/vue';
import { ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { StatBar } from '@/components/ui/stat-bar';
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
    activities: {
        id: number;
        type: string;
        description: string;
        created_at: string;
        user?: { id: number; name: string } | null;
    }[];
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
        <div
            class="sticky top-0 z-30 bg-background/95 pb-4 backdrop-blur-sm print:hidden"
        >
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
                        <span class="font-normal text-muted-foreground"
                            >·
                            {{
                                formatCurrency(props.account.lifetime_value)
                            }}</span
                        >
                    </h1>
                </div>
                <AccountActions
                    :account="props.account"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <StatBar
            :items="[
                {
                    label: 'Lifetime Value',
                    value: formatCurrency(props.account.lifetime_value),
                },
                {
                    label: 'Website',
                    value: props.account.website
                        ? props.account.website.replace(/^https?:\/\//, '')
                        : 'Not provided',
                },
                {
                    label: 'Contacts',
                    value: props.account.contacts?.length || 0,
                },
                { label: 'Status', value: getLabel(props.account.status) },
            ]"
        />

        <div class="space-y-4 pt-4">
            <!-- Contacts Section -->
            <div class="space-y-4 border-b p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <UserRound class="h-4 w-4 text-muted-foreground" />
                        <h3
                            class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            Contacts
                        </h3>
                        <span
                            class="rounded-md bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground"
                        >
                            {{ props.account.contacts?.length || 0 }}
                        </span>
                    </div>
                    <Button
                        size="sm"
                        class="gap-2"
                        @click="openNewContactDialog"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Add Contact
                    </Button>
                </div>
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

            <!-- Activity Timeline -->
            <div class="space-y-4 border-b p-5">
                <h3
                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                >
                    Activity
                </h3>
                <ActivityTimeline :activities="props.activities" />
            </div>
        </div>

        <ContactFormDialog
            v-model:open="contactDialogOpen"
            :account-id="props.account.id"
            :contact="editingContact"
        />
    </div>
</template>
