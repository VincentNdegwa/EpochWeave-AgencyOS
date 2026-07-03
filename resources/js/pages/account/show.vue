<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import {
    Building2,
    ExternalLinkIcon,
    FileText,
    FolderOpen,
    HashIcon,
    Mail,
    MapPin,
    Phone,
    Plus,
    Receipt,
    Share2,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import CommentThread from '@/components/CommentThread.vue';
import NoteThread from '@/components/NoteThread.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { StatBar } from '@/components/ui/stat-bar';
import { confirm } from '@/composables/useConfirmation';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import { destroy as destroyEngagement } from '@/routes/engagements';
import type { Account, AccountContact, Address, Engagement } from '@/types/models/account';
import AccountActions from './components/AccountActions.vue';
import AccountCustomFields from './components/AccountCustomFields.vue';
import AccountEngagements from './components/AccountEngagements.vue';
import AccountInvoices from './components/AccountInvoices.vue';
import AccountProjects from './components/AccountProjects.vue';
import AccountProposals from './components/AccountProposals.vue';
import { createContactColumns } from './contacts-datatable/columns';
import ContactsDataTable from './contacts-datatable/data-table.vue';
import AddressFormDialog from './dialogs/AddressFormDialog.vue';
import ContactFormDialog from './dialogs/ContactFormDialog.vue';
import EngagementFormDialog from './dialogs/EngagementFormDialog.vue';

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

const { format: formatCurrency } = useCurrency();
const { formatDate } = useDateFormat();
const { getLabel, getVariant } = useAccountStatuses();

const contactDialogOpen = ref(false);
const editingContact = ref<AccountContact | null>(null);

const addressDialogOpen = ref(false);
const editingAddress = ref<Address | null>(null);

const engagementDialogOpen = ref(false);
const editingEngagement = ref<Engagement | null>(null);

const contactColumns = createContactColumns(props.account.id);

const activeTab = ref('overview');

const tabs = [
    { key: 'overview', label: 'Overview' },
    { key: 'contacts', label: 'Contacts' },
    { key: 'proposals', label: 'Proposals' },
    { key: 'projects', label: 'Projects' },
    { key: 'invoices', label: 'Invoices' },
    { key: 'engagements', label: 'Engagements' },
    { key: 'comments', label: 'Comments' },
    { key: 'notes', label: 'Notes' },
];

const outstandingBalance = computed(() => {
    return (props.account.invoices ?? []).reduce(
        (sum, invoice) => sum + (invoice.grand_total - invoice.amount_paid),
        0,
    );
});

const openNewAddressDialog = () => {
    editingAddress.value = null;
    addressDialogOpen.value = true;
};

const openEditAddressDialog = (address: Address) => {
    editingAddress.value = address;
    addressDialogOpen.value = true;
};

const deleteAddress = async (addressId: number) => {
    const confirmed = await confirm({
        title: 'Delete Address',
        description: 'Are you sure you want to delete this address? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(`/addresses/${addressId}`);
    }
};

const openNewContactDialog = () => {
    editingContact.value = null;
    contactDialogOpen.value = true;
};

const openNewEngagementDialog = () => {
    editingEngagement.value = null;
    engagementDialogOpen.value = true;
};

const openEditEngagementDialog = (engagement: Engagement) => {
    editingEngagement.value = engagement;
    engagementDialogOpen.value = true;
};

const deleteEngagement = async (engagement: Engagement) => {
    const confirmed = await confirm({
        title: 'Delete Engagement',
        description: 'Are you sure you want to delete this engagement? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        router.delete(destroyEngagement(engagement).url);
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
                        <span
                            class="inline-flex items-center gap-1 rounded-md border border-border bg-muted/50 px-2 py-0.5 font-mono text-[11px] text-muted-foreground"
                        >
                            <HashIcon class="h-2.5 w-2.5" />{{ props.account.id }}
                        </span>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ props.account.company_name }}
                        <span class="font-normal text-muted-foreground"
                            >· {{ formatCurrency(props.account.lifetime_value) }}</span
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
                    label: 'Contacts',
                    value: props.account.contacts?.length ?? 0,
                },
                {
                    label: 'Open Invoices',
                    value: (props.account.invoices ?? []).length,
                },
                {
                    label: 'Outstanding',
                    value: formatCurrency(outstandingBalance),
                },
            ]"
        />

        <!-- Tabs -->
        <div class="border-b border-border bg-background">
            <div class="flex">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="border-b-2 px-4 py-3 text-sm font-medium transition-colors"
                    :class="
                        activeTab === tab.key
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground'
                    "
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div class="space-y-4 pt-4">
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-2">
                        <!-- Company Details -->
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Details
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Status</span>
                                    <span class="font-medium">{{ getLabel(props.account.status) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Industry</span>
                                    <span class="font-medium">{{ props.account.industry?.name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Lead Source</span>
                                    <span class="font-medium">{{ props.account.leadSource?.name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Company Size</span>
                                    <span class="font-medium">{{ props.account.companySize?.label ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Employees</span>
                                    <span class="font-medium">{{ props.account.employee_count?.toLocaleString() ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Founded</span>
                                    <span class="font-medium">{{ formatDate(props.account.founded_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Annual Revenue</span>
                                    <span class="font-medium">{{ formatCurrency(props.account.annual_revenue ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Created</span>
                                    <span class="font-medium">{{ formatDate(props.account.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Description
                            </h3>
                            <p
                                class="text-sm leading-relaxed whitespace-pre-wrap text-foreground/90"
                            >
                                {{ props.account.description || 'No description provided.' }}
                            </p>
                        </div>


                        <!-- Addresses -->
                        <div class="space-y-4 border-b p-5">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Addresses
                                </h3>
                                <Button
                                    size="sm"
                                    class="gap-2"
                                    @click="openNewAddressDialog"
                                >
                                    <Plus class="h-3.5 w-3.5" />
                                    Add Address
                                </Button>
                            </div>
                            <div v-if="props.account.addresses?.length" class="space-y-2">
                                <div
                                    v-for="address in props.account.addresses"
                                    :key="address.id"
                                    class="flex items-start justify-between gap-4 rounded-md p-2 text-sm transition-colors hover:bg-muted"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <MapPin class="h-4 w-4 text-muted-foreground" />
                                            <span class="font-medium capitalize">{{ address.type }}</span>
                                            <Badge v-if="address.is_primary" variant="outline" class="text-[10px]">Primary</Badge>
                                        </div>
                                        <div class="mt-1 text-muted-foreground">
                                            {{ address.street_1 }}
                                            <span v-if="address.street_2">, {{ address.street_2 }}</span><br />
                                            {{ address.city }}<span v-if="address.state">, {{ address.state }}</span>
                                            <span v-if="address.postal_code"> {{ address.postal_code }}</span><br />
                                            {{ address.country }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
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
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                No addresses on file.
                            </div>
                        </div>

                        <!-- Social Profiles -->
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Social Profiles
                            </h3>
                            <div v-if="props.account.socialProfiles?.length" class="flex flex-wrap gap-2">
                                <a
                                    v-for="profile in props.account.socialProfiles"
                                    :key="profile.id"
                                    :href="profile.url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted"
                                >
                                    <Share2 class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ profile.platform }}
                                    <Badge v-if="profile.is_verified" variant="outline" class="text-[10px]">Verified</Badge>
                                </a>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                No social profiles linked.
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Custom Fields
                            </h3>
                            <AccountCustomFields
                                :values="props.account.customFieldValues ?? []"
                            />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="space-y-6">
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Contact
                            </p>
                            <div class="space-y-1">
                                <div class="flex items-center gap-3 py-1.5">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-muted"
                                    >
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-muted-foreground">Phone</p>
                                        <p class="truncate text-sm font-medium">
                                            {{ props.account.phone ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 py-1.5">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-muted"
                                    >
                                        <Building2 class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-muted-foreground">Website</p>
                                        <a
                                            v-if="props.account.website"
                                            :href="props.account.website"
                                            target="_blank"
                                            class="truncate text-sm font-medium text-primary hover:underline"
                                        >
                                            {{ props.account.website.replace(/^https?:\/\//, '') }}
                                        </a>
                                        <p v-else class="truncate text-sm font-medium">—</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 py-1.5">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-muted"
                                    >
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-muted-foreground">Primary Email</p>
                                        <p class="truncate text-sm font-medium">
                                            {{ props.account.contacts?.find((c) => c.is_primary)?.email ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Linked
                            </p>
                            <div class="space-y-0.5">
                                <Link
                                    v-if="props.account.proposals?.[0]"
                                    :href="`/proposals/${props.account.proposals[0].id}`"
                                    class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                                >
                                    <FileText class="h-4 w-4 text-muted-foreground" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-muted-foreground">Latest Proposal</p>
                                        <p
                                            class="truncate text-sm font-medium transition-colors group-hover:text-primary"
                                        >
                                            {{ props.account.proposals[0].title }}
                                        </p>
                                    </div>
                                    <ExternalLinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50" />
                                </Link>

                                <Link
                                    v-if="props.account.projects?.[0]"
                                    :href="`/projects/${props.account.projects[0].id}`"
                                    class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                                >
                                    <FolderOpen class="h-4 w-4 text-muted-foreground" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-muted-foreground">Latest Project</p>
                                        <p
                                            class="truncate text-sm font-medium transition-colors group-hover:text-primary"
                                        >
                                            {{ props.account.projects[0].name }}
                                        </p>
                                    </div>
                                    <ExternalLinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50" />
                                </Link>

                                <Link
                                    v-if="props.account.invoices?.[0]"
                                    :href="`/invoices/${props.account.invoices[0].id}`"
                                    class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                                >
                                    <Receipt class="h-4 w-4 text-muted-foreground" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-muted-foreground">Latest Invoice</p>
                                        <p
                                            class="truncate text-sm font-medium transition-colors group-hover:text-primary"
                                        >
                                            {{ props.account.invoices[0].invoice_number ?? `#${props.account.invoices[0].id}` }}
                                        </p>
                                    </div>
                                    <ExternalLinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50" />
                                </Link>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Activity
                            </p>
                            <ActivityTimeline :activities="props.activities" />
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Contacts Tab -->
            <div v-else-if="activeTab === 'contacts'" class="space-y-4">
                <div class="flex justify-end">
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
                    v-if="props.account.contacts?.length"
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
                    <p class="text-xs">Add the first contact for this account.</p>
                </div>
            </div>

            <!-- Proposals Tab -->
            <div v-else-if="activeTab === 'proposals'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Proposals
                    </h3>
                    <AccountProposals :proposals="props.account.proposals ?? []" />
                </div>
            </div>

            <!-- Projects Tab -->
            <div v-else-if="activeTab === 'projects'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Projects
                    </h3>
                    <AccountProjects :projects="props.account.projects ?? []" />
                </div>
            </div>

            <!-- Invoices Tab -->
            <div v-else-if="activeTab === 'invoices'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Invoices
                    </h3>
                    <AccountInvoices :invoices="props.account.invoices ?? []" />
                </div>
            </div>

            <!-- Engagements Tab -->
            <div v-else-if="activeTab === 'engagements'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            Engagements
                        </h3>
                        <Button size="sm" @click="openNewEngagementDialog">
                            Log Engagement
                        </Button>
                    </div>
                    <AccountEngagements
                        :engagements="props.account.engagements ?? []"
                        @edit="openEditEngagementDialog"
                        @delete="deleteEngagement"
                    />
                </div>
            </div>

            <!-- Comments Tab -->
            <div v-else-if="activeTab === 'comments'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Comments
                    </h3>
                    <CommentThread
                        commentable-type="account"
                        :commentable-id="props.account.id"
                        :comments="props.account.comments ?? []"
                    />
                </div>
            </div>

            <!-- Notes Tab -->
            <div v-else-if="activeTab === 'notes'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Notes
                    </h3>
                    <NoteThread
                        noteable-type="account"
                        :noteable-id="props.account.id"
                        :notes="props.account.notes ?? []"
                    />
                </div>
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

        <EngagementFormDialog
            v-model:open="engagementDialogOpen"
            :account="props.account"
            :engagement="editingEngagement"
        />
    </div>
</template>
