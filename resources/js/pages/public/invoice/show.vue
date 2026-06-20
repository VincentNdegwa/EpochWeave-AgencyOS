<script setup lang="ts">
import InvoiceDocument from '@/pages/invoices/components/InvoiceDocument.vue';
import type { Invoice } from '@/types/models/invoice';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    invoice: Invoice;
}>();

</script>

<template>
        <Head :title="`Invoice · ${props.invoice.invoice_number}`" />

        <div>
            <InvoiceDocument :invoice="invoice" />
        </div>
</template>
