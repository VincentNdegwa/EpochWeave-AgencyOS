<script setup lang="ts">
import { computed } from 'vue';
import InvoiceDocument from '@/pages/invoices/components/InvoiceDocument.vue';
import { useInvoiceStatuses } from '@/composables/useEnums';
import type { Invoice } from '@/types/models/invoice';

const props = defineProps<{
    invoice: Invoice;
}>();

</script>

<template>
        <div>
            <InvoiceDocument :invoice="invoice" />
        </div>
</template>
