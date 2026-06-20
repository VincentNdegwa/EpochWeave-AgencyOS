<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref, computed, watch, watchEffect } from 'vue';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import { dashboard } from '@/routes';
import { useBuilderDataStore } from '@/stores/builderData';
import type { Invoice } from '@/types/models/invoice';
import InvoiceLineItemDialog from './components/InvoiceLineItemDialog.vue';

const props = defineProps<{ invoice: Invoice }>();

const { format: fmtCurrency } = useCurrency();
const { formatDateInput } = useDateFormat();
const builderDataStore = useBuilderDataStore();
const { accounts, accountContacts, projects, products } = storeToRefs(builderDataStore);

builderDataStore.fetchAccounts();
builderDataStore.fetchProducts();

interface LineItem {
    id: string;
    product_id: number | null;
    item_name: string;
    description: string;
    unit_label: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
    discount_type: 'none' | 'percentage' | 'fixed';
    discount_value: number;
    discount_amount: number;
    tax_type: 'none' | 'percentage' | 'fixed';
    tax_value: number;
    total_tax_amount: number;
    total: number;
}

interface InvoiceForm {
    account_id: string;
    account_contact_id: string;
    project_id: string;
    issue_date: string;
    due_date: string;
    notes: string;
    line_items: LineItem[];
}

const form = ref<InvoiceForm>({
    account_id: props.invoice.account_id?.toString() || '',
    account_contact_id: props.invoice.account_contact_id?.toString() || '',
    project_id: props.invoice.project_id?.toString() || '',
    issue_date: props.invoice.issue_date
        ? formatDateInput(new Date(props.invoice.issue_date))
        : formatDateInput(new Date()),
    due_date: props.invoice.due_date
        ? formatDateInput(new Date(props.invoice.due_date))
        : '',
    notes: props.invoice.notes || '',
    line_items: (props.invoice.items || []).map((item) => ({
        id: item.id?.toString() || crypto.randomUUID(),
        product_id: item.product_id ?? null,
        item_name: item.item_name,
        description: item.description || '',
        unit_label: item.unit_label || 'Pcs',
        quantity: item.quantity,
        unit_price: item.unit_price,
        subtotal: item.subtotal,
        discount_type: (item.discount_type || 'none') as 'none' | 'percentage' | 'fixed',
        discount_value: item.discount_value || 0,
        discount_amount: item.discount_amount || 0,
        tax_type: (item.tax_type || 'none') as 'none' | 'percentage' | 'fixed',
        tax_value: item.tax_value || 0,
        total_tax_amount: item.total_tax_amount || 0,
        total: item.total,
    })),
});

if (form.value.account_id) {
    builderDataStore.fetchAccountContacts({ account_id: form.value.account_id });
    builderDataStore.fetchProjects({ account_id: form.value.account_id });
}

const isSaving = ref(false);
const errors = ref<Record<string, string>>({});
const dialogOpen = ref(false);
const editingItemIndex = ref<number | null>(null);
const editingItem = ref<Partial<LineItem>>({});

watch(
    () => form.value.account_id,
    (accountId) => {
        if (accountId) {
            builderDataStore.fetchAccountContacts({ account_id: accountId });
            builderDataStore.fetchProjects({ account_id: accountId });
        } else {
            form.value.account_contact_id = '';
            form.value.project_id = '';
        }
    },
);

watch(
    () => form.value.account_contact_id,
    (contactId) => {
        if (contactId) {
            form.value.project_id = '';
        }
    },
);

const addLineItem = () => {
    editingItemIndex.value = null;
    editingItem.value = {};
    dialogOpen.value = true;
};

const editLineItem = (index: number) => {
    editingItemIndex.value = index;
    editingItem.value = { ...form.value.line_items[index] };
    dialogOpen.value = true;
};

const removeLineItem = (index: number) => {
    form.value.line_items.splice(index, 1);
};

const handleDialogSave = (item: LineItem) => {
    if (editingItemIndex.value !== null) {
        form.value.line_items[editingItemIndex.value] = item;
    } else {
        form.value.line_items.push(item);
    }

    dialogOpen.value = false;
};

const handleDialogCancel = () => {
    dialogOpen.value = false;
};

const subtotal = computed((): number =>
    form.value.line_items.reduce((sum, item) => sum + item.subtotal, 0),
);

const discountTotal = computed((): number =>
    form.value.line_items.reduce((sum, item) => sum + item.discount_amount, 0),
);

const taxTotal = computed((): number =>
    form.value.line_items.reduce((sum, item) => sum + item.total_tax_amount, 0),
);

const grandTotal = computed((): number =>
    form.value.line_items.reduce((sum, item) => sum + item.total, 0),
);

const handleSave = async () => {
    if (isSaving.value) {
return;
}

    isSaving.value = true;
    errors.value = {};

    try {
        await router.patch(InvoiceController.update(props.invoice.id).url, form.value as any, {
            onError: (pageErrors) => {
                errors.value = pageErrors as Record<string, string>;
                isSaving.value = false;
            },
        });
    } catch (error) {
        console.error('Failed to update invoice:', error);
        isSaving.value = false;
    }
};

const goBack = () => {
    router.visit(InvoiceController.show(props.invoice.id).url);
};

watchEffect(() => {
    setLayoutProps({
        title: 'Edit Invoice',
        description: `Editing invoice #${props.invoice.invoice_number || props.invoice.id}`,
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Invoices', href: InvoiceController.index() },
            { title: `#${props.invoice.invoice_number || props.invoice.id}` },
        ],
    });
});

</script>

<template>
    <Head title="Edit Invoice" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Edit Invoice</h4>
                <p class="text-muted-foreground">
                    Update invoice details and line items.
                </p>
            </div>
            <div class="flex gap-2">
                <Button type="button" variant="outline" @click="goBack">
                    Cancel
                </Button>
                <Button type="button" :disabled="isSaving" @click="handleSave">
                    {{ isSaving ? 'Saving...' : 'Update Invoice' }}
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <Card class="bg-background">
                    <CardHeader>
                        <h5 class="font-semibold">Invoice Details</h5>
                        <p class="text-sm text-muted-foreground">
                            Client and project information
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="account_id" required>Account</Label>
                                <Select v-model="form.account_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select an account" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="account in accounts"
                                            :key="account.id"
                                            :value="account.id.toString()"
                                        >
                                            {{ account.company_name || account.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.account_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="account_contact_id">Contact</Label>
                                <Select v-model="form.account_contact_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a contact" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="contact in accountContacts"
                                            :key="contact.id"
                                            :value="contact.id.toString()"
                                        >
                                            {{ contact.first_name }} {{ contact.last_name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.account_contact_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="project_id">Project</Label>
                                <Select v-model="form.project_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select a project" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="project in projects"
                                            :key="project.id"
                                            :value="project.id.toString()"
                                        >
                                            {{ project.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.project_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="issue_date" required>Issue Date</Label>
                                <Input id="issue_date" type="date" v-model="form.issue_date" />
                                <InputError :message="errors.issue_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="due_date" required>Due Date</Label>
                                <Input id="due_date" type="date" v-model="form.due_date" />
                                <InputError :message="errors.due_date" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-background">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="font-semibold">Line Items</h5>
                                <p class="text-sm text-muted-foreground">
                                    Products and services to invoice
                                </p>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addLineItem">
                                + Add Item
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="form.line_items.length === 0" class="py-8 text-center text-muted-foreground">
                            No line items yet. Click "Add Item" to start.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(item, index) in form.line_items"
                                :key="item.id"
                                class="flex items-center gap-4 rounded-lg border p-3"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ item.item_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ item.quantity }} {{ item.unit_label }} × {{ fmtCurrency(item.unit_price) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">{{ fmtCurrency(item.total) }}</p>
                                    <p v-if="item.discount_type !== 'none'" class="text-xs text-red-500">
                                        −{{ fmtCurrency(item.discount_amount) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button type="button" variant="ghost" size="sm" @click="editLineItem(index)">
                                        Edit
                                    </Button>
                                    <Button type="button" variant="ghost" size="sm" class="text-destructive" @click="removeLineItem(index)">
                                        Remove
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-background">
                    <CardHeader>
                        <h5 class="font-semibold">Notes</h5>
                        <p class="text-sm text-muted-foreground">
                            Additional information for the client
                        </p>
                    </CardHeader>
                    <CardContent>
                        <Textarea v-model="form.notes" placeholder="Additional notes for the invoice..." rows="4" />
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card class="sticky top-6 bg-background">
                    <CardHeader>
                        <h5 class="font-semibold">Summary</h5>
                        <p class="text-sm text-muted-foreground">
                            Invoice totals
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>{{ fmtCurrency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Discount</span>
                                <span class="text-red-500">−{{ fmtCurrency(discountTotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Tax</span>
                                <span>{{ fmtCurrency(taxTotal) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between text-base font-semibold">
                                <span>Total</span>
                                <span>{{ fmtCurrency(grandTotal) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <InvoiceLineItemDialog
        :open="dialogOpen"
        :item="editingItem"
        :products="products"
        @save="handleDialogSave"
        @cancel="handleDialogCancel"
    />
</template>
