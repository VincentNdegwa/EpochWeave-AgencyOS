import type { Ref } from 'vue';
import { onBeforeUnmount, ref } from 'vue';
import { destroy, store } from '@/routes/uploads';

type UploadKey = string;

type UploadResponse = Record<string, string>;

type PendingUpload = {
    url: string;
    committed: boolean;
};

const csrfToken = (): string => {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    return token ?? '';
};

export type UseTemporaryUploadsReturn = {
    isUploading: Ref<boolean>;
    errors: Ref<string[]>;
    pending: Ref<Record<UploadKey, PendingUpload>>;
    upload: (filesByKey: Record<UploadKey, File>) => Promise<UploadResponse>;
    remove: (key: UploadKey) => Promise<void>;
    commit: (key: UploadKey) => void;
    commitAll: () => void;
    cleanup: () => Promise<void>;
};

export const useTemporaryUploads = (): UseTemporaryUploadsReturn => {
    const isUploading = ref(false);
    const errors = ref<string[]>([]);
    const pending = ref<Record<UploadKey, PendingUpload>>({});

    const upload = async (
        filesByKey: Record<UploadKey, File>,
    ): Promise<UploadResponse> => {
        errors.value = [];

        const entries = Object.entries(filesByKey).filter(([, file]) => !!file);

        if (entries.length === 0) {
            return {};
        }

        isUploading.value = true;

        try {
            await Promise.all(entries.map(([key]) => remove(key)));

            const formData = new FormData();
            formData.append('_token', csrfToken());

            for (const [key, file] of entries) {
                formData.append(`files[${key}]`, file);
            }

            const response = await fetch(store().url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                errors.value.push('Upload failed');
                return {};
            }

            const json = (await response.json()) as UploadResponse;

            for (const [key, url] of Object.entries(json)) {
                pending.value = {
                    ...pending.value,
                    [key]: {
                        url,
                        committed: false,
                    },
                };
            }

            return json;
        } catch {
            errors.value.push('Upload failed');
            return {};
        } finally {
            isUploading.value = false;
        }
    };

    const remove = async (key: UploadKey): Promise<void> => {
        const item = pending.value[key];

        if (!item || item.committed) {
            pending.value = { ...pending.value };
            delete pending.value[key];
            return;
        }

        try {
            await fetch(destroy(key).url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
            });
        } finally {
            pending.value = { ...pending.value };
            delete pending.value[key];
        }
    };

    const commit = (key: UploadKey): void => {
        const item = pending.value[key];

        if (!item) {
            return;
        }

        pending.value = {
            ...pending.value,
            [key]: {
                ...item,
                committed: true,
            },
        };
    };

    const commitAll = (): void => {
        for (const key of Object.keys(pending.value)) {
            commit(key);
        }
    };

    const cleanup = async (): Promise<void> => {
        const keys = Object.keys(pending.value);

        for (const key of keys) {
            await remove(key);
        }
    };

    onBeforeUnmount(() => {
        void cleanup();
    });

    return {
        isUploading,
        errors,
        pending,
        upload,
        remove,
        commit,
        commitAll,
        cleanup,
    };
};
