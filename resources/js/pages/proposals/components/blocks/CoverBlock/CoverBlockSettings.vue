<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
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
import type { CoverBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  blockId: string;
}>();

const store = useProposalBuilderStore();
const isUploading = ref(false);

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data = computed<CoverBlockData | null>(() => (block.value?.data as CoverBlockData) ?? null);
const imageMode = ref<'url' | 'upload'>('url');

const updateData = (changes: Partial<CoverBlockData>) => {
  if (!block.value || !data.value) {
    return;
  }
  store.updateBlockData(block.value.id, {
    ...data.value,
    ...changes,
  });
};

const handleBackgroundFile = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) {
    return;
  }
  imageMode.value = 'upload';
  isUploading.value = true;
  const reader = new FileReader();
  reader.onload = () => {
    updateData({ background_value: typeof reader.result === 'string' ? reader.result : '' });
    isUploading.value = false;
  };
  reader.onerror = () => {
    isUploading.value = false;
  };
  reader.readAsDataURL(file);
};
</script>

<template>
  <div v-if="data" class="space-y-4">
    <div class="grid gap-2">
      <Label for="cover-heading">Heading</Label>
      <Input
        id="cover-heading"
        :model-value="data?.heading ?? ''"
        @update:modelValue="(value) => updateData({ heading: String(value ?? '') })"
      />
    </div>

    <div class="grid gap-2">
      <Label for="cover-subheading">Subheading</Label>
      <Input
        id="cover-subheading"
        :model-value="data?.subheading ?? ''"
        @update:modelValue="(value) => updateData({ subheading: value ? String(value) : null })"
      />
    </div>

    <div class="grid gap-2">
      <Label>Background type</Label>
      <Select
        :model-value="data?.background_type ?? 'color'"
        @update:modelValue="(value) => updateData({ background_type: (value ?? 'color') as CoverBlockData['background_type'] })"
      >
        <SelectTrigger>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="color">Color</SelectItem>
          <SelectItem value="image">Image</SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div class="grid gap-2">
      <Label>Background value</Label>
      <Input
        v-if="(data?.background_type ?? 'color') === 'color'"
        type="color"
        :model-value="data?.background_value ?? '#ffffff'"
        @update:modelValue="(value) => updateData({ background_value: String(value ?? '#ffffff') })"
      />
      <div v-else class="space-y-2">
        <div class="flex gap-2 rounded-md border border-border bg-muted/40 p-1 text-sm">
          <Button
            type="button"
            variant="ghost"
            class="flex-1 px-3 py-1.5"
            :class="imageMode === 'url' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'"
            @click="imageMode = 'url'"
          >
            Link URL
          </Button>
          <Button
            type="button"
            variant="ghost"
            class="flex-1 px-3 py-1.5"
            :class="imageMode === 'upload' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'"
            @click="imageMode = 'upload'"
          >
            Upload image
          </Button>
        </div>
        <template v-if="imageMode === 'url'">
          <Input
            type="url"
            placeholder="https://example.com/background.jpg"
            :model-value="data?.background_value ?? ''"
            @update:modelValue="(value) => updateData({ background_value: String(value ?? '') })"
          />
          <p class="text-xs text-muted-foreground">Paste an externally hosted image link.</p>
        </template>
        <template v-else>
          <input
            class="h-9 w-full cursor-pointer rounded-md border border-dashed border-border bg-background/70 px-3 py-2 text-sm"
            type="file"
            accept="image/*"
            @change="handleBackgroundFile"
          />
          <p class="text-xs text-muted-foreground">Upload an image; a preview will display immediately.</p>
          <p v-if="isUploading" class="text-xs text-muted-foreground">Uploading…</p>
        </template>
      </div>
    </div>

    <div class="grid gap-2">
      <Label for="text-color">Text color</Label>
      <Input id="text-color" type="color" :model-value="data?.text_color ?? '#000000'" @update:modelValue="(value) => updateData({ text_color: String(value ?? '#000000') })" />
    </div>

    <div class="space-y-3">
      <label class="flex items-center justify-between text-sm">
        <span>Show workspace logo</span>
        <Switch :modelValue="data?.show_logo ?? false" @update:modelValue="(checked: boolean) => updateData({ show_logo: checked })" />
      </label>
      <label class="flex items-center justify-between text-sm">
        <span>Show date</span>
        <Switch :modelValue="data?.show_date ?? false" @update:modelValue="(checked: boolean) => updateData({ show_date: checked })" />
      </label>
      <label class="flex items-center justify-between text-sm">
        <span>Show proposal number</span>
        <Switch :modelValue="data?.show_proposal_number ?? false" @update:modelValue="(checked: boolean) => updateData({ show_proposal_number: checked })" />
      </label>
    </div>
  </div>
  <p v-else class="text-sm text-muted-foreground">Block data unavailable.</p>
</template>
