<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { MessageSquare, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import type { Comment } from '@/types/models/comment';

const props = defineProps<{
    taskId: number;
    comments: Comment[];
}>();

const body = ref('');
const processing = ref(false);

const submitComment = () => {
    if (!body.value.trim()) {
return;
}

    processing.value = true;

    router.post(`/tasks/${props.taskId}/comments`, {
        body: body.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            body.value = '';
            processing.value = false;
        },
    });
};

const deleteComment = (commentId: number) => {
    router.delete(`/tasks/${props.taskId}/comments/${commentId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <MessageSquare class="h-4 w-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold">Comments</h3>
            <span class="text-xs text-muted-foreground">({{ comments.length }})</span>
        </div>

        <div class="space-y-3">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="rounded-lg border p-4"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                            {{ comment.user?.name?.charAt(0) || '?' }}
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ comment.user?.name || 'Unknown' }}</p>
                            <p class="text-xs text-muted-foreground">{{ new Date(comment.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                    <Button
                        size="icon"
                        variant="ghost"
                        class="h-7 w-7 text-muted-foreground hover:text-destructive"
                        @click="deleteComment(comment.id)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </Button>
                </div>
                <p class="mt-2 text-sm text-foreground">{{ comment.body }}</p>
            </div>

            <p v-if="!comments.length" class="text-sm text-muted-foreground">No comments yet.</p>
        </div>

        <div class="space-y-2">
            <Textarea
                v-model="body"
                placeholder="Add a comment..."
                rows="3"
            />
            <div class="flex justify-end">
                <Button
                    size="sm"
                    :disabled="!body.trim() || processing"
                    @click="submitComment"
                >
                    Post Comment
                </Button>
            </div>
        </div>
    </div>
</template>
