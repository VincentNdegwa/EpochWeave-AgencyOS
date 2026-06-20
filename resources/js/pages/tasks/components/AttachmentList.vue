<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Paperclip, Trash2, Upload } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import type { Attachment } from '@/types/models/attachment';

const props = defineProps<{
    taskId: number;
    attachments: Attachment[];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const processing = ref(false);

const triggerUpload = () => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
return;
}

    processing.value = true;
    const formData = new FormData();
    formData.append('file', file);

    router.post(`/tasks/${props.taskId}/attachments`, formData, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;

            if (fileInput.value) {
fileInput.value.value = '';
}
        },
    });
};

const deleteAttachment = (attachmentId: number) => {
    router.delete(`/tasks/${props.taskId}/attachments/${attachmentId}`, {
        preserveScroll: true,
    });
};

const formatSize = (bytes: number): string => {
    if (bytes === 0) {
return '0 B';
}

    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / k ** i).toFixed(1))} ${sizes[i]}`;
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Paperclip class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Attachments</h3>
                <span class="text-xs text-muted-foreground">({{ attachments.length }})</span>
            </div>
            <Button
                size="sm"
                variant="outline"
                class="gap-1"
                :disabled="processing"
                @click="triggerUpload"
            >
                <Upload class="h-3.5 w-3.5" />
                Upload
            </Button>
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                @change="handleFileChange"
            />
        </div>

        <div class="space-y-2">
            <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center justify-between rounded-lg border p-3"
            >
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">{{ attachment.original_name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatSize(attachment.size) }}
                        <span v-if="attachment.uploader">&middot; {{ attachment.uploader.name }}</span>
                    </p>
                </div>
                <Button
                    size="icon"
                    variant="ghost"
                    class="h-7 w-7 shrink-0 text-muted-foreground hover:text-destructive"
                    @click="deleteAttachment(attachment.id)"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </Button>
            </div>

            <p v-if="!attachments.length" class="text-sm text-muted-foreground">No attachments yet.</p>
        </div>
    </div>
</template>
