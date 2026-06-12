<script setup lang="ts">
import { Eye, Edit, MoreVertical, Save } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import BuilderActionMenu from '@/pages/proposals/components/builder/BuilderActionMenu.vue';
import BuilderSaveStatus from '@/pages/proposals/components/builder/BuilderSaveStatus.vue';

defineEmits<{
    (e: 'toggle-preview'): void;
}>();

const props = withDefaults(
    defineProps<{
        mode: 'create' | 'edit';
        status: string;
        isDirty: boolean;
        isSaving: boolean;
        isPreview: boolean;
        proposalId?: number | null;
        builderMode: string;
        onSave?: () => Promise<void>;
    }>(),
    {
        proposalId: null,
        isPreview: false,
        builderMode: 'proposal',
        onSave: undefined,
    },
);

const title = defineModel<string>('title', { default: 'Untitled proposal' });
const editableRef = ref<HTMLDivElement | null>(null);

const previewButtonText = computed(() =>
    props.isPreview ? 'Edit' : 'Preview',
);
const previewButtonIcon = computed(() => (props.isPreview ? Edit : Eye));

const isTemplateMode = computed(() => props.builderMode === 'template');
const saveButtonText = computed(() =>
    props.mode === 'create' ? 'Create' : 'Save',
);

const handleSave = async () => {
    if (props.onSave) {
        await props.onSave();
    }
};

const handleInput = () => {
    if (!editableRef.value) {
        return;
    }

    const nextValue = editableRef.value.textContent?.trim();
    title.value =
        nextValue && nextValue.length > 0 ? nextValue : 'Untitled proposal';
};

onMounted(() => {
    if (editableRef.value) {
        editableRef.value.textContent = title.value;
    }
});

watch(
    () => title.value,
    (value) => {
        if (editableRef.value && editableRef.value.textContent !== value) {
            editableRef.value.textContent = value;
        }
    },
);
</script>

<template>
    <header
        class="flex h-14 items-center justify-between border-b border-border bg-background px-4"
    >
        <div class="flex items-center gap-3">
            <div>
                <div
                    ref="editableRef"
                    class="min-w-48 text-lg font-semibold text-foreground outline-none"
                    contenteditable
                    role="textbox"
                    spellcheck="false"
                    @keydown.enter.prevent
                    @input="handleInput"
                ></div>
                <BuilderSaveStatus :is-dirty="isDirty" :is-saving="isSaving" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <Button
                variant="ghost"
                size="sm"
                class="hidden items-center gap-2 text-foreground md:inline-flex"
                @click="$emit('toggle-preview')"
            >
                <component :is="previewButtonIcon" class="h-4 w-4" />
                {{ previewButtonText }}
            </Button>

            <!-- Template mode: Show Save button -->
            <Button
                v-if="isTemplateMode"
                size="sm"
                class="hidden gap-2 md:inline-flex"
                :disabled="isSaving"
                @click="handleSave"
            >
                <Save v-if="!isSaving" class="h-4 w-4" />
                <div
                    v-else
                    class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"
                ></div>
                {{ isSaving ? 'Saving...' : saveButtonText }}
            </Button>

            <!-- Proposal mode: Show Create or Send button -->
            <Button
                v-else
                size="sm"
                class="hidden gap-2 md:inline-flex"
                @click="handleSave"
                :disabled="isSaving"
            >
                <Save v-if="!isSaving" class="h-4 w-4" />
                <div
                    v-else
                    class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"
                ></div>
                {{
                    mode === 'create'
                        ? isSaving
                            ? 'Creating...'
                            : 'Create'
                        : isSaving
                          ? 'Saving...'
                          : 'Send'
                }}
            </Button>

            <!-- Hide action menu in template mode -->
            <BuilderActionMenu v-if="!isTemplateMode">
                <Button variant="outline" size="icon">
                    <MoreVertical class="h-4 w-4" />
                    <span class="sr-only">Open actions</span>
                </Button>
            </BuilderActionMenu>
        </div>
    </header>
</template>
