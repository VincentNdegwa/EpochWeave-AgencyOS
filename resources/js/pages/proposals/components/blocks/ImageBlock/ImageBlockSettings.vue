<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ImageBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  blockId: string;
}>();

const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data = computed<ImageBlockData | null>(() => (block.value?.data as ImageBlockData) ?? null);

const updateData = (changes: Partial<ImageBlockData>) => {
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
    <div class="grid gap-2">
      <Label for="image-url">Image URL</Label>
      <Input
        id="image-url"
        type="url"
        :model-value="data?.url ?? ''"
        placeholder="https://example.com/hero.jpg"
        @update:modelValue="(value) => updateData({ url: String(value ?? '') })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="image-alt">Alt text</Label>
      <Input id="image-alt" :model-value="data?.alt_text ?? ''" @update:modelValue="(value) => updateData({ alt_text: value ? String(value) : null })" />
    </div>

    <div class="grid gap-2">
      <Label for="caption">Caption</Label>
      <Input id="caption" :model-value="data?.caption ?? ''" @update:modelValue="(value) => updateData({ caption: value ? String(value) : null })" />
    </div>

    <div class="grid gap-2">
      <Label>Alignment</Label>
      <Select :value="data?.alignment ?? 'center'" @update:value="(value: string) => updateData({ alignment: value as ImageBlockData['alignment'] })">
        <SelectTrigger>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="left">Left</SelectItem>
          <SelectItem value="center">Center</SelectItem>
          <SelectItem value="right">Right</SelectItem>
          <SelectItem value="full">Full width</SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div class="grid gap-2">
      <Label for="width">Width (%)</Label>
      <Input
        id="width"
        type="number"
        min="10"
        max="100"
        :model-value="data?.width_percent ?? 100"
        @update:modelValue="(value) => updateData({ width_percent: Number(value ?? data?.width_percent ?? 100) })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="link">Link URL</Label>
      <Input id="link" type="url" :model-value="data?.link_url ?? ''" @update:modelValue="(value) => updateData({ link_url: value ? String(value) : null })" />
    </div>
  </div>
  <p v-else class="text-sm text-muted-foreground">Block data unavailable.</p>
</template>
