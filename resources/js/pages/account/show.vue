<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
import type { Account, AccountContact, Address } from '@/types/models/account';
import AccountActions from './components/AccountActions.vue';
import { createContactColumns } from './contacts-datatable/columns';
import ContactsDataTable from './contacts-datatable/data-table.vue';
import ContactFormDialog from './dialogs/ContactFormDialog.vue';
import AddressFormDialog from './dialogs/AddressFormDialog.vue';

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

const addressDialogOpen = ref(false);
const editingAddress = ref<Address | null>(null);

const contactColumns = createContactColumns(props.account.id);

const openNewAddressDialog = () => {
    editingAddress.value = null;
    addressDialogOpen.value = true;
};

const openEditAddressDialog = (address: Address) => {
    editingAddress.value = address;
    addressDialogOpen.value = true;
};

const deleteAddress = (addressId: number) => {
    if (confirm('Delete this address?')) {
        router.delete(`/addresses/${addressId}`);
    }
};

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

            <!-- Addresses Section -->
            <div class="space-y-4 border-b p-5">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Addresses
                    </h3>
                    <Button size="sm" class="gap-2" @click="openNewAddressDialog">
                        <Plus class="h-3.5 w-3.5" />
                        Add Address
                    </Button>
                </div>
                <div
                    v-if="props.account.addresses && props.account.addresses.length > 0"
                    class="space-y-2"
                >
                    <div
                        v-for="address in props.account.addresses"
                        :key="address.id"
                        class="rounded-lg border border-border bg-muted/30 p-3 text-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-medium capitalize">{{ address.type }}</span>
                                <span
                                    v-if="address.is_primary"
                                    class="rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary"
                                >Primary</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="openEditAddressDialog(address)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    class="text-destructive"
                                    @click="deleteAddress(address.id)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </div>
                        <div class="mt-1 text-muted-foreground">
                            {{ address.street_1 }}
                            <span v-if="address.street_2">, {{ address.street_2 }}</span><br />
                            {{ address.city }}<span v-if="address.state">, {{ address.state }}</span>
                            <span v-if="address.postal_code"> {{ address.postal_code }}</span><br />
                            {{ address.country }}
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-muted-foreground">
                    No addresses on file.
                </div>
            </div>

            <!-- Social Profiles Section -->
            <div class="space-y-4 border-b p-5">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Social Profiles
                    </h3>
                </div>
                <div
                    v-if="props.account.socialProfiles && props.account.socialProfiles.length > 0"
                    class="flex flex-wrap gap-2"
                >
                    <a
                        v-for="profile in props.account.socialProfiles"
                        :key="profile.id"
                        :href="profile.url"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-muted/30 px-3 py-1.5 text-sm hover:bg-muted/50"
                    >
                        {{ profile.platform }}
                        <span
                            v-if="profile.is_verified"
                            class="rounded bg-primary/10 px-1 py-0.5 text-[10px] font-medium text-primary"
                        >Verified</span>
                    </a>
                </div>
                <div v-else class="text-sm text-muted-foreground">
                    No social profiles linked.
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

        <AddressFormDialog
            v-model:open="addressDialogOpen"
            addressable-type="App\Models\Account"
            :addressable-id="props.account.id"
            :address="editingAddress"
        />
    </div>
</template>
