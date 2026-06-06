<script setup lang="ts">
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import { ImageIcon, LinkIcon, TypeIcon, ToggleLeftIcon } from '@lucide/vue';
import type { CoverBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{ blockId: string }>();

const store  = useProposalBuilderStore();
const isUploading = ref(false);

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data  = computed<CoverBlockData | null>(() => (block.value?.data as CoverBlockData) ?? null);

const updateData = (changes: Partial<CoverBlockData>) => {
  if (!block.value || !data.value) return;
  store.updateBlockData(block.value.id, { ...data.value, ...changes });
};

const handleBackgroundFile = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;
  isUploading.value = true;
  const reader = new FileReader();
  reader.onload  = () => {
    updateData({ background_value: typeof reader.result === 'string' ? reader.result : '' });
    isUploading.value = false;
  };
  reader.onerror = () => { isUploading.value = false; };
  reader.readAsDataURL(file);
};

const bgTab = computed(() => data.value?.background_type ?? 'color');

const onBgTabChange = (value: string) => {
  updateData({
    background_type: value as CoverBlockData['background_type'],
    background_value: value === 'color' ? '#0F172A' : '',
  });
};
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <TypeIcon class="h-3 w-3" />
        Content
      </p>

      <div class="space-y-3">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Heading</Label>
          <Input
            :model-value="data.heading"
            placeholder="Enter a proposal heading…"
            class="h-8 text-sm"
            @update:modelValue="(v) => updateData({ heading: String(v ?? '') })"
          />
        </div>

        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Subheading <span class="opacity-50">(optional)</span></Label>
          <textarea
            :value="data.subheading ?? ''"
            rows="2"
            placeholder="A brief description or tagline…"
            class="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm leading-relaxed placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
            @input="(e) => updateData({ subheading: (e.target as HTMLTextAreaElement).value || null })"
          />
        </div>
      </div>
    </div>

    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <ImageIcon class="h-3 w-3" />
        Background
      </p>

      <Tabs :model-value="bgTab" class="w-full" @update:modelValue="onBgTabChange">
        <TabsList class="grid w-full grid-cols-2 h-8 rounded-md">
          <TabsTrigger value="color" class="text-xs rounded-sm">Solid color</TabsTrigger>
          <TabsTrigger value="image" class="text-xs rounded-sm">Image</TabsTrigger>
        </TabsList>
      </Tabs>

      <div v-if="bgTab === 'color'" class="mt-3">
        <div class="flex items-center gap-3">
          <div class="relative flex-shrink-0">
            <div
              class="h-9 w-9 rounded-md border border-border shadow-sm cursor-pointer overflow-hidden"
              :style="{ backgroundColor: data.background_value }"
            >
              <input
                type="color"
                :value="data.background_value"
                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                @input="(e) => updateData({ background_value: (e.target as HTMLInputElement).value })"
              />
            </div>
          </div>
          <Input
            :model-value="data.background_value"
            placeholder="#0F172A"
            class="h-9 font-mono text-xs flex-1"
            @update:modelValue="(v) => updateData({ background_value: String(v ?? '#0F172A') })"
          />
        </div>

        <div class="mt-3">
          <p class="mb-2 text-[11px] text-muted-foreground">Presets</p>
          <div class="flex flex-wrap gap-2">
            <Tooltip v-for="swatch in swatches" :key="swatch.value">
              <TooltipTrigger as-child>
                <button
                  type="button"
                  class="h-6 w-6 rounded-md border-2 transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-ring"
                  :style="{ backgroundColor: swatch.value }"
                  :class="data.background_value === swatch.value
                    ? 'border-primary scale-110'
                    : 'border-transparent'"
                  @click="updateData({ background_value: swatch.value })"
                />
              </TooltipTrigger>
              <TooltipContent side="bottom" class="text-xs">{{ swatch.label }}</TooltipContent>
            </Tooltip>
          </div>
        </div>
      </div>

      <div v-else class="mt-3 space-y-3">
        <div class="grid gap-1.5">
          <Label class="flex items-center gap-1 text-xs text-muted-foreground">
            <LinkIcon class="h-3 w-3" /> Image URL
          </Label>
          <Input
            type="url"
            :model-value="data.background_value?.startsWith('data:') ? '' : (data.background_value ?? '')"
            placeholder="https://example.com/cover.jpg"
            class="h-8 text-xs"
            @update:modelValue="(v) => updateData({ background_value: String(v ?? '') })"
          />
        </div>

        <div class="flex items-center gap-2 text-xs text-muted-foreground">
          <div class="h-px flex-1 bg-border" />
          <span>or</span>
          <div class="h-px flex-1 bg-border" />
        </div>

        <label
          class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border bg-muted/30 px-4 py-5 text-center transition hover:border-primary hover:bg-muted/60"
        >
          <ImageIcon class="h-5 w-5 text-muted-foreground group-hover:text-primary" />
          <span class="text-xs text-muted-foreground group-hover:text-foreground">
            <span v-if="isUploading">Uploading…</span>
            <span v-else>Click to upload · PNG, JPG, WEBP</span>
          </span>
          <input
            type="file"
            accept="image/*"
            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
            @change="handleBackgroundFile"
          />
        </label>

        <div
          v-if="data.background_value && data.background_value.length > 0"
          class="relative overflow-hidden rounded-md border border-border"
        >
          <img
            :src="data.background_value"
            alt="Background preview"
            class="h-20 w-full object-cover"
          />
          <button
            type="button"
            class="absolute right-1.5 top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-background/80 text-muted-foreground hover:text-destructive"
            @click="updateData({ background_value: '' })"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <div class="py-3 border-b border-border">
      <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        Text colour
      </p>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="flex h-9 flex-1 items-center justify-center gap-2 rounded-md border text-xs font-medium transition"
          :class="data.text_color === '#FFFFFF'
            ? 'border-primary bg-primary text-primary-foreground'
            : 'border-border bg-background text-foreground hover:bg-muted'"
          @click="updateData({ text_color: '#FFFFFF' })"
        >
          <span class="inline-block h-3 w-3 rounded-full bg-white border border-border" />
          Light
        </button>
        <button
          type="button"
          class="flex h-9 flex-1 items-center justify-center gap-2 rounded-md border text-xs font-medium transition"
          :class="data.text_color === '#0F172A'
            ? 'border-primary bg-primary text-primary-foreground'
            : 'border-border bg-background text-foreground hover:bg-muted'"
          @click="updateData({ text_color: '#0F172A' })"
        >
          <span class="inline-block h-3 w-3 rounded-full bg-slate-900 border border-border" />
          Dark
        </button>

        <div class="relative flex-shrink-0">
          <div
            class="h-9 w-9 rounded-md border border-border cursor-pointer overflow-hidden shadow-sm"
            :style="{ backgroundColor: data.text_color }"
          >
            <input
              type="color"
              :value="data.text_color"
              class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
              @input="(e) => updateData({ text_color: (e.target as HTMLInputElement).value })"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <ToggleLeftIcon class="h-3 w-3" />
        Visible elements
      </p>

      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <label
          v-for="toggle in toggles"
          :key="toggle.key"
          class="flex cursor-pointer items-center justify-between px-3 py-2.5 hover:bg-muted/50 transition-colors"
        >
          <div class="flex flex-col gap-0.5">
            <span class="text-xs font-medium text-foreground">{{ toggle.label }}</span>
            <span class="text-[11px] text-muted-foreground">{{ toggle.hint }}</span>
          </div>
          <Switch
            :model-value="(data as any)[toggle.key] ?? false"
            @update:modelValue="(v: boolean) => updateData({ [toggle.key]: v } as Partial<CoverBlockData>)"
          />
        </label>
      </div>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>

<script lang="ts">
const swatches = [
  { value: '#0F172A', label: 'Slate 900' },
  { value: '#1E293B', label: 'Slate 800' },
  { value: '#0F766E', label: 'Teal 700' },
  { value: '#1D4ED8', label: 'Blue 700' },
  { value: '#7C3AED', label: 'Violet 600' },
  { value: '#BE185D', label: 'Pink 700' },
  { value: '#B45309', label: 'Amber 700' },
  { value: '#FFFFFF', label: 'White' },
];

const toggles = [
  {
    key: 'show_logo',
    label: 'Workspace logo',
    hint: 'Displays your logo in the top-left corner',
  },
  {
    key: 'show_date',
    label: 'Proposal date',
    hint: 'Shows today\'s date on the cover',
  },
  {
    key: 'show_proposal_number',
    label: 'Proposal number',
    hint: 'Shows the proposal reference ID',
  },
];
</script>