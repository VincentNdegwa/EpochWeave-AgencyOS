<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { BaseBlock } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  blockId: string;
}>();

const store = useProposalBuilderStore();

const paddingOptions: { label: string; value: BaseBlock['meta']['padding_top'] }[] = [
  { label: 'None', value: 'none' },
  { label: 'Small', value: 'sm' },
  { label: 'Medium', value: 'md' },
  { label: 'Large', value: 'lg' },
];

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const meta = computed(() => block.value?.meta ?? {
  padding_top: 'md',
  padding_bottom: 'md',
});

const updateMeta = (changes: Partial<BaseBlock['meta']>) => {
  if (!block.value) {
    return;
  }
  store.updateBlockMeta(block.value.id, {
    ...block.value.meta,
    ...changes,
  });
};
</script>

<template>
  <div v-if="block" class="space-y-4">
    <div class="grid gap-2">
      <Label>Padding top</Label>
      <Select :value="meta.padding_top" @update:value="(value: string) => updateMeta({ padding_top: value as BaseBlock['meta']['padding_top'] })">
        <SelectTrigger>
          <SelectValue placeholder="Select" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem v-for="option in paddingOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div class="grid gap-2">
      <Label>Padding bottom</Label>
      <Select :value="meta.padding_bottom" @update:value="(value: string) => updateMeta({ padding_bottom: value as BaseBlock['meta']['padding_bottom'] })">
        <SelectTrigger>
          <SelectValue placeholder="Select" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem v-for="option in paddingOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
  </div>
  <p v-else class="text-sm text-muted-foreground">Block not found.</p>
</template>
