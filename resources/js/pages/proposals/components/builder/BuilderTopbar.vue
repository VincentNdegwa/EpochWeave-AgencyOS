<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { ArrowLeft, Eye, Edit, Send, MoreVertical } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import BuilderSaveStatus from '@/pages/proposals/components/builder/BuilderSaveStatus.vue';
import BuilderActionMenu from '@/pages/proposals/components/builder/BuilderActionMenu.vue';

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
  }>(),
  {
    proposalId: null,
    isPreview: false,
  }
);

const title = defineModel<string>('title', { default: 'Untitled proposal' });
const editableRef = ref<HTMLDivElement | null>(null);

const previewButtonText = computed(() => props.isPreview ? 'Edit' : 'Preview');
const previewButtonIcon = computed(() => props.isPreview ? Edit : Eye);

const handleInput = () => {
  if (!editableRef.value) {
    return;
  }
  const nextValue = editableRef.value.textContent?.trim();
  title.value = nextValue && nextValue.length > 0 ? nextValue : 'Untitled proposal';
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
  }
);
</script>

<template>
  <header class="flex h-14 items-center justify-between border-b border-border bg-background px-4">
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
      <Button size="sm" class="hidden gap-2 md:inline-flex" :disabled="mode === 'create'">
        <Send class="h-4 w-4" />
        Send
      </Button>
      <BuilderActionMenu>
        <Button variant="outline" size="icon">
          <MoreVertical class="h-4 w-4" />
          <span class="sr-only">Open actions</span>
        </Button>
      </BuilderActionMenu>
    </div>
  </header>
</template>
