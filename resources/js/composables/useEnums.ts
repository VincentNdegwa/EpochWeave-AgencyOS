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
    projectStatuses: Record<string, EnumOption & { hexColor: string }>;
    taskPriorities: Record<string, EnumOption & { hexColor: string }>;
}

export function useEnums() {
    const page = usePage();
    const enums = page.props.enums as Enums;

    return {
        accountStatuses: enums?.accountStatuses || {},
        billingTypes: enums?.billingTypes || {},
        billingFrequencies: enums?.billingFrequencies || {},
        projectStatuses: enums?.projectStatuses || {},
        taskPriorities: enums?.taskPriorities || {},
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

export function useProjectStatuses() {
    const { projectStatuses } = useEnums();

    return {
        all: projectStatuses,
        getByValue: (
            value: string,
        ): (EnumOption & { hexColor: string }) | undefined =>
            projectStatuses[value],
        getLabel: (value: string): string =>
            projectStatuses[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            projectStatuses[value]?.variant || 'default',
        getColor: (value: string): string =>
            projectStatuses[value]?.color || '',
        getHexColor: (value: string): string =>
            projectStatuses[value]?.hexColor || '#6b7280',
        values: Object.values(projectStatuses),
    };
}

export function useTaskPriorities() {
    const { taskPriorities } = useEnums();

    return {
        all: taskPriorities,
        getByValue: (
            value: string,
        ): (EnumOption & { hexColor: string }) | undefined =>
            taskPriorities[value],
        getLabel: (value: string): string =>
            taskPriorities[value]?.label || value,
        getVariant: (value: string): BadgeVariants['variant'] =>
            taskPriorities[value]?.variant || 'default',
        getColor: (value: string): string => taskPriorities[value]?.color || '',
        getHexColor: (value: string): string =>
            taskPriorities[value]?.hexColor || '#6b7280',
        values: Object.values(taskPriorities),
    };
}
