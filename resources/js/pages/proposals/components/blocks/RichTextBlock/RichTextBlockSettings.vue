<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { RichTextBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  blockId: string;
}>();

const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data = computed<RichTextBlockData | null>(() => (block.value?.data as RichTextBlockData) ?? null);

const updateData = (changes: Partial<RichTextBlockData>) => {
  if (!block.value || !data.value) {
    return;
  }
  store.updateBlockData(block.value.id, {
    ...data.value,
    ...changes,
  });
};
</script>

<template>
  <div v-if="data" class="space-y-4">

    <label class="flex items-center justify-between text-sm">
      <span>Show title</span>
      <Switch :modelValue="data?.show_title ?? true" @update:modelValue="(checked: boolean) => updateData({ show_title: checked })" />
    </label>

    <div v-if="data?.show_title ?? true" class="grid gap-2">
      <Label for="rich-text-title">Block title</Label>
      <Input
        id="rich-text-title"
        :model-value="data.title ?? ''"
        placeholder="Section title"
        @update:modelValue="(value) => updateData({ title: value ? String(value) : null })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="rich-text-title-color">Title color</Label>
      <Input
        id="rich-text-title-color"
        type="color"
        :model-value="data.title_color ?? '#111827'"
        @update:modelValue="(value) => updateData({ title_color: String(value ?? '#111827') })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="rich-text-content-color">Content color</Label>
      <Input
        id="rich-text-content-color"
        type="color"
        :model-value="data.content_color ?? '#374151'"
        @update:modelValue="(value) => updateData({ content_color: String(value ?? '#374151') })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="rich-text-background-color">Background color</Label>
      <Input
        id="rich-text-background-color"
        type="color"
        :model-value="data.background_color ?? '#ffffff'"
        @update:modelValue="(value) => updateData({ background_color: String(value ?? '#ffffff') })"
      />
      <p class="text-xs text-muted-foreground">Applies directly to this block so you can contrast text colors per section.</p>
    </div>
  </div>
  <p v-else class="text-sm text-muted-foreground">Block data unavailable.</p>
</template>
