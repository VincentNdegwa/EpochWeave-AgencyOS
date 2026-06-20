export function useDateFormat() {
    const formatDate = (value?: string | null): string => {
        if (!value) {
return '—';
}

        return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
    };

    const formatDateTime = (value?: string | null): string => {
        if (!value) {
return '—';
}

        return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
    };

    const formatDateLong = (value?: string | null): string => {
        if (!value) {
return '—';
}

        return new Intl.DateTimeFormat(undefined, { dateStyle: 'long' }).format(new Date(value));
    };

    const formatDateInput = (value?: Date | string | null): string => {
        if (!value) {
return '';
}

        const date = typeof value === 'string' ? new Date(value) : value;

        return date.toISOString().split('T')[0];
    };

    return {
        formatDate,
        formatDateTime,
        formatDateLong,
        formatDateInput,
    };
}
