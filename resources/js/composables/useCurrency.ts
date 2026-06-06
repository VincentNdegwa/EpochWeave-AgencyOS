import { usePage } from '@inertiajs/vue3';

export function useCurrency() {
    const page = usePage();
    const workspace = page.props.workspace as { currency?: string } | null;

    const currency = workspace?.currency || 'USD';

    const format = (
        value: number,
        options?: Intl.NumberFormatOptions,
    ): string => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency,
            ...options,
        }).format(value);
    };

    return {
        currency,
        format,
    };
}
