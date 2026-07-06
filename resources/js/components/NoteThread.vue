<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MessageSquare, Pencil, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import type { Note } from '@/types/models/note';

const props = defineProps<{
    noteableType: string;
    noteableId: number;
    notes: Note[];
}>();

const body = ref('');
const isInternal = ref(false);
const editingNoteId = ref<number | null>(null);
const processing = ref(false);

const resetForm = () => {
    body.value = '';
    isInternal.value = false;
    editingNoteId.value = null;
};

const submitNote = () => {
    if (!body.value.trim()) {
        return;
    }

    processing.value = true;

    const payload = {
        noteable_type: props.noteableType,
        noteable_id: props.noteableId,
        body: body.value,
        is_internal: isInternal.value,
    };

    const finish = () => {
        resetForm();
        processing.value = false;
    };

    if (editingNoteId.value) {
        router.put(
            `/notes/${editingNoteId.value}`,
            {
                body: body.value,
                is_internal: isInternal.value,
            },
            {
                preserveScroll: true,
                onFinish: finish,
            },
        );
    } else {
        router.post('/notes', payload, {
            preserveScroll: true,
            onFinish: finish,
        });
    }
};

const editNote = (note: Note) => {
    editingNoteId.value = note.id;
    body.value = note.body;
    isInternal.value = note.is_internal;
};

const cancelEdit = () => {
    resetForm();
};

const deleteNote = (noteId: number) => {
    router.delete(`/notes/${noteId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <MessageSquare class="h-4 w-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold">Notes</h3>
            <span class="text-xs text-muted-foreground"
                >({{ notes.length }})</span
            >
        </div>

        <div class="space-y-3">
            <div
                v-for="note in notes"
                :key="note.id"
                class="rounded-lg border p-4"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                        >
                            {{ note.user?.name?.charAt(0) || '?' }}
                        </div>
                        <div>
                            <p class="text-sm font-medium">
                                {{ note.user?.name || 'Unknown' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    new Date(
                                        note.created_at,
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <Button
                            size="icon"
                            variant="ghost"
                            class="h-7 w-7 text-muted-foreground"
                            @click="editNote(note)"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="h-7 w-7 text-muted-foreground hover:text-destructive"
                            @click="deleteNote(note.id)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
                <p class="mt-2 text-sm text-foreground">{{ note.body }}</p>
                <Badge
                    v-if="note.is_internal"
                    variant="secondary"
                    class="mt-2 text-[10px]"
                >
                    Internal
                </Badge>
            </div>

            <p v-if="!notes.length" class="text-sm text-muted-foreground">
                No notes yet.
            </p>
        </div>

        <div class="space-y-2">
            <Textarea
                v-model="body"
                :placeholder="editingNoteId ? 'Edit note...' : 'Add a note...'"
                rows="3"
            />
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Checkbox id="internal-note" v-model="isInternal" />
                    <label
                        for="internal-note"
                        class="text-sm text-muted-foreground"
                    >
                        Internal note
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="editingNoteId"
                        size="sm"
                        variant="ghost"
                        @click="cancelEdit"
                    >
                        <X class="h-3.5 w-3.5" />
                        Cancel
                    </Button>
                    <Button
                        size="sm"
                        :disabled="!body.trim() || processing"
                        @click="submitNote"
                    >
                        {{ editingNoteId ? 'Update Note' : 'Add Note' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
