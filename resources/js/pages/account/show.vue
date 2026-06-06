<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Edit,
    Trash2,
    Building2,
    Globe,
    DollarSign,
    Users,
    Plus,
} from '@lucide/vue';
import { ref } from 'vue';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useCurrency } from '@/composables/useCurrency';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import type { Account, AccountContact } from '@/types/models/account';
import { createContactColumns } from './contacts-datatable/columns';
import ContactsDataTable from './contacts-datatable/data-table.vue';
import ContactFormDialog from './dialogs/ContactFormDialog.vue';

const props = defineProps<{
    account: Account;
}>();

const { getVariant, getLabel } = useAccountStatuses();
const { format: formatCurrency } = useCurrency();

const contactDialogOpen = ref(false);
const editingContact = ref<AccountContact | null>(null);

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
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ props.account.company_name }}
                    </h1>
                    <p class="text-muted-foreground">
                        Account details and contacts
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Link :href="AccountController.edit(props.account.id).url">
                    <Button variant="outline">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                </Link>
                <Button variant="destructive">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <!-- Account Details -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Status</CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <Badge :variant="getVariant(props.account.status)">
                        {{ getLabel(props.account.status) }}
                    </Badge>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Website</CardTitle>
                    <Globe class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        <a
                            v-if="props.account.website"
                            :href="props.account.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 hover:underline"
                        >
                            Visit
                        </a>
                        <span v-else class="text-muted-foreground">—</span>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Lifetime Value</CardTitle
                    >
                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(props.account.lifetime_value) }}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Contacts</CardTitle>
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.account.contacts?.length || 0 }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Contacts Section -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Contacts</CardTitle>
                <Button @click="openNewContactDialog" size="sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Contact
                </Button>
            </CardHeader>
            <CardContent>
                <ContactsDataTable
                    v-if="
                        props.account.contacts &&
                        props.account.contacts.length > 0
                    "
                    :columns="contactColumns"
                    :data="props.account.contacts"
                />
                <div v-else class="py-8 text-center text-muted-foreground">
                    No contacts added yet.
                </div>
            </CardContent>
        </Card>

        <!-- Contact Form Dialog -->
        <ContactFormDialog
            v-model:open="contactDialogOpen"
            :account-id="props.account.id"
            :contact="editingContact"
        />
    </div>
</template>
