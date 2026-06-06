import { ref } from 'vue';

interface ConfirmationState {
    open: boolean;
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
    resolve: (value: boolean) => void;
}

const state = ref<ConfirmationState>({
    open: false,
    title: '',
    description: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'default',
    resolve: () => {},
});

export function confirm({
    title,
    description,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    variant = 'default',
}: {
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}): Promise<boolean> {
    return new Promise((resolve) => {
        state.value = {
            open: true,
            title,
            description,
            confirmText,
            cancelText,
            variant,
            resolve,
        };
    });
}

export function useConfirmation() {
    const handleConfirm = () => {
        state.value.resolve(true);
        state.value.open = false;
    };

    const handleCancel = () => {
        state.value.resolve(false);
        state.value.open = false;
    };

    return {
        state,
        confirm,
        handleConfirm,
        handleCancel,
    };
}
