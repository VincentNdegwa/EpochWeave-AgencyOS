import { usePage } from '@inertiajs/vue3';
import type { BadgeVariants } from '@/components/ui/badge';

export interface EnumOption {
    value: string;
    label: string;
    color: string;
    variant: BadgeVariants['variant'];
}

export function useBillingFrequencies() {
    const { billingFrequencies } = useEnums();

    return {
        all: billingFrequencies,
        getByValue: (value: string): EnumOption | undefined =>
            billingFrequencies[value],
        getLabel: (value: string): string =>
            billingFrequencies[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            billingFrequencies[value]?.variant || 'default',
        getColor: (value: string): string =>
            billingFrequencies[value]?.color || '',
        values: Object.values(billingFrequencies),
    };
}

export interface Enums {
    accountStatuses: Record<string, EnumOption>;
    billingTypes: Record<string, EnumOption>;
    billingFrequencies: Record<string, EnumOption>;
    invoiceStatuses: Record<string, EnumOption & { hexColor: string }>;
}

export function useEnums() {
    const page = usePage();
    const enums = page.props.enums as Enums;

    return {
        accountStatuses: enums?.accountStatuses || {},
        billingTypes: enums?.billingTypes || {},
        billingFrequencies: enums?.billingFrequencies || {},
        invoiceStatuses: enums?.invoiceStatuses || {},
    };
}

export function useAccountStatuses() {
    const { accountStatuses } = useEnums();

    return {
        all: accountStatuses,
        getByValue: (value: string): EnumOption | undefined =>
            accountStatuses[value],
        getLabel: (value: string): string =>
            accountStatuses[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            accountStatuses[value]?.variant || 'default',
        getColor: (value: string): string =>
            accountStatuses[value]?.color || '',
        values: Object.values(accountStatuses),
    };
}

export function useBillingTypes() {
    const { billingTypes } = useEnums();

    return {
        all: billingTypes,
        getByValue: (value: string): EnumOption | undefined =>
            billingTypes[value],
        getLabel: (value: string): string =>
            billingTypes[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            billingTypes[value]?.variant || 'default',
        getColor: (value: string): string => billingTypes[value]?.color || '',
        values: Object.values(billingTypes),
    };
}

export function useInvoiceStatuses() {
    const { invoiceStatuses } = useEnums();

    return {
        all: invoiceStatuses,
        getByValue: (value: string): (EnumOption & { hexColor: string }) | undefined =>
            invoiceStatuses[value],
        getLabel: (value: string): string =>
            invoiceStatuses[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            invoiceStatuses[value]?.variant || 'default',
        getColor: (value: string): string =>
            invoiceStatuses[value]?.color || '',
        getHexColor: (value: string): string =>
            invoiceStatuses[value]?.hexColor || '#6b7280',
        values: Object.values(invoiceStatuses),
    };
}
