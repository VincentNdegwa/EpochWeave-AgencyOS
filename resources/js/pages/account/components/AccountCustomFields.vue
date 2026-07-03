<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { CustomFieldValue } from '@/types/models/account';

const props = defineProps<{
    values: CustomFieldValue[];
}>();

const displayValue = (value: CustomFieldValue): string => {
    if (value.value !== undefined && value.value !== null) {
        return String(value.value);
    }

    if (value.value_text !== null) {
return value.value_text;
}

    if (value.value_number !== null) {
return String(value.value_number);
}

    if (value.value_boolean !== null) {
return value.value_boolean ? 'Yes' : 'No';
}

    if (value.value_date !== null) {
return new Date(value.value_date).toLocaleDateString();
}

    if (value.value_json !== null) {
return JSON.stringify(value.value_json);
}

    return '-';
};
</script>

<template>
    <div v-if="props.values.length === 0" class="py-8 text-center text-sm text-muted-foreground">
        No custom fields.
    </div>
    <Table v-else>
        <TableHeader>
            <TableRow>
                <TableHead>Field</TableHead>
                <TableHead>Type</TableHead>
                <TableHead class="text-right">Value</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="field in props.values" :key="field.id">
                <TableCell class="font-medium">
                    {{ field.definition?.name || `Field #${field.id}` }}
                </TableCell>
                <TableCell class="capitalize">
                    {{ field.definition?.field_type || '-' }}
                </TableCell>
                <TableCell class="text-right">{{ displayValue(field) }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
