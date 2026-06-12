<script setup lang="ts">
import {
  ImageIcon,
  LayoutGridIcon,
  SlidersHorizontalIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  PlusIcon,
  Trash2Icon,
  UploadIcon,
  LinkIcon,
} from '@lucide/vue';
import { nanoid } from 'nanoid';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { ImageBlockData, ImageItem } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<ImageBlockData>(props.blockId);

const expandedImageId = ref<string | null>(
  data.value?.images?.[0]?.id ?? null,
);

const updateImage = (id: string, changes: Partial<ImageItem>) => {
  if (!data.value) {
return;
}

  updateData({
    images: data.value.images.map((img) =>
      img.id === id ? { ...img, ...changes } : img,
    ),
  });
};

const addImage = () => {
  if (!data.value) {
return;
}

  const newImg: ImageItem = {
    id: nanoid(8),
    url: '',
    alt_text: null,
    caption: null,
    link_url: null,
  };
  updateData({ images: [...data.value.images, newImg] });
  expandedImageId.value = newImg.id;
};

const removeImage = (id: string) => {
  if (!data.value) {
return;
}

  updateData({ images: data.value.images.filter((img) => img.id !== id) });

  if (expandedImageId.value === id) {
    expandedImageId.value = data.value.images[0]?.id ?? null;
  }
};

// ── Per-image upload ──────────────────────────────────────────
const uploadingId = ref<string | null>(null);
const handleFileUpload = (id: string, event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];

  if (!file) {
return;
}

  uploadingId.value = id;
  const reader = new FileReader();
  reader.onload = () => {
    updateImage(id, { url: typeof reader.result === 'string' ? reader.result : '' });
    uploadingId.value = null;
  };
  reader.onerror = () => {
 uploadingId.value = null; 
};
  reader.readAsDataURL(file);
};

const imageInputMode = ref<Record<string, 'url' | 'upload'>>({});
const getMode = (id: string) => imageInputMode.value[id] ?? 'upload';
const setMode = (id: string, mode: 'url' | 'upload') => {
  imageInputMode.value = { ...imageInputMode.value, [id]: mode };
};

// ── Options ───────────────────────────────────────────────────
const columnOptions = [
  { value: 1, label: '1 col', icon: '▬' },
  { value: 2, label: '2 col', icon: '▬▬' },
  { value: 3, label: '3 col', icon: '▬▬▬' },
] as const;

const alignmentOptions = [
  { value: 'left',   label: 'Left' },
  { value: 'center', label: 'Center' },
  { value: 'right',  label: 'Right' },
  { value: 'full',   label: 'Full' },
] as const;

const aspectOptions = [
  { value: 'auto',     label: 'Auto' },
  { value: 'square',   label: 'Square' },
  { value: 'video',    label: '16:9' },
  { value: 'portrait', label: '3:4' },
] as const;
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- ══════════════════════════════════════════════
         SECTION 1 — IMAGES
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <ImageIcon class="h-3 w-3" />
        Images
        <span class="ml-auto text-[11px] font-normal text-muted-foreground">
          {{ data.images.length }} / {{ data.columns === 1 ? 1 : data.columns === 2 ? 4 : 6 }}
        </span>
      </p>

      <!-- Per-image accordion -->
      <div class="space-y-2">
        <Collapsible
          v-for="(img, index) in data.images"
          :key="img.id"
          :open="expandedImageId === img.id"
          @update:open="(open) => { expandedImageId = open ? img.id : null }"
        >
          <div class="rounded-lg border border-border overflow-hidden">

            <!-- Accordion header -->
            <CollapsibleTrigger
              class="flex w-full items-center gap-2 px-3 py-2.5
                     bg-muted/30 hover:bg-muted/60 transition-colors"
            >
              <!-- Thumbnail preview -->
              <div class="h-7 w-10 flex-shrink-0 overflow-hidden rounded border border-border bg-muted">
                <img
                  v-if="img.url"
                  :src="img.url"
                  :alt="img.alt_text ?? ''"
                  class="h-full w-full object-cover"
                />
                <div v-else class="flex h-full w-full items-center justify-center">
                  <ImageIcon class="h-3 w-3 text-muted-foreground/50" />
                </div>
              </div>

              <span class="flex-1 text-xs font-medium text-foreground text-left">
                {{ img.alt_text || `Image ${index + 1}` }}
              </span>

              <div class="flex items-center gap-1">
                <!-- Remove image -->
                <button
                  v-if="data.images.length > 1"
                  type="button"
                  class="flex h-5 w-5 items-center justify-center rounded
                         text-muted-foreground hover:text-destructive transition-colors"
                  @click.stop="removeImage(img.id)"
                >
                  <Trash2Icon class="h-3 w-3" />
                </button>
                <ChevronDownIcon
                  v-if="expandedImageId === img.id"
                  class="h-3.5 w-3.5 text-muted-foreground"
                />
                <ChevronRightIcon
                  v-else
                  class="h-3.5 w-3.5 text-muted-foreground"
                />
              </div>
            </CollapsibleTrigger>

            <!-- Accordion content -->
            <CollapsibleContent>
              <div class="space-y-3 p-3 border-t border-border bg-background">

                <!-- URL / Upload toggle -->
                <div class="flex gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                  <button
                    type="button"
                    class="flex flex-1 items-center justify-center gap-1.5 rounded
                           py-1.5 text-xs font-medium transition"
                    :class="getMode(img.id) === 'upload'
                      ? 'bg-background text-foreground shadow-sm'
                      : 'text-muted-foreground hover:text-foreground'"
                    @click="setMode(img.id, 'upload')"
                  >
                    <UploadIcon class="h-3 w-3" /> Upload
                  </button>
                  <button
                    type="button"
                    class="flex flex-1 items-center justify-center gap-1.5 rounded
                           py-1.5 text-xs font-medium transition"
                    :class="getMode(img.id) === 'url'
                      ? 'bg-background text-foreground shadow-sm'
                      : 'text-muted-foreground hover:text-foreground'"
                    @click="setMode(img.id, 'url')"
                  >
                    <LinkIcon class="h-3 w-3" /> URL
                  </button>
                </div>

                <!-- Upload zone -->
                <template v-if="getMode(img.id) === 'upload'">
                  <label
                    class="relative flex cursor-pointer flex-col items-center justify-center
                           gap-1.5 rounded-lg border-2 border-dashed border-border
                           bg-muted/30 py-4 text-center transition
                           hover:border-primary hover:bg-muted/50"
                  >
                    <span v-if="uploadingId === img.id" class="text-xs text-muted-foreground">
                      Uploading…
                    </span>
                    <template v-else>
                      <UploadIcon class="h-4 w-4 text-muted-foreground" />
                      <span class="text-xs text-muted-foreground">
                        Click or drag to upload
                      </span>
                    </template>
                    <input
                      type="file"
                      accept="image/*"
                      class="sr-only"
                      @change="(e) => handleFileUpload(img.id, e)"
                    />
                  </label>

                  <!-- Current image url read-only preview -->
                  <div
                    v-if="img.url"
                    class="flex items-center gap-2 rounded-md bg-muted/40 px-2.5 py-2"
                  >
                    <img
                      :src="img.url"
                      :alt="img.alt_text ?? ''"
                      class="h-8 w-12 flex-shrink-0 rounded object-cover border border-border"
                    />
                    <span class="flex-1 truncate text-[11px] text-muted-foreground">
                      Image uploaded
                    </span>
                    <button
                      type="button"
                      class="text-muted-foreground hover:text-destructive transition-colors"
                      @click="updateImage(img.id, { url: '' })"
                    >
                      <Trash2Icon class="h-3 w-3" />
                    </button>
                  </div>
                </template>

                <!-- URL input -->
                <template v-else>
                  <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground">Image URL</Label>
                    <Input
                      type="url"
                      :model-value="img.url"
                      placeholder="https://example.com/image.jpg"
                      class="h-8 text-xs"
                      @update:model-value="(v) => updateImage(img.id, { url: String(v ?? '') })"
                    />
                  </div>
                </template>

                <!-- Alt text -->
                <div class="grid gap-1.5">
                  <Label class="text-xs text-muted-foreground">
                    Alt text
                    <span class="opacity-50">(accessibility)</span>
                  </Label>
                  <Input
                    :model-value="img.alt_text ?? ''"
                    placeholder="Describe the image…"
                    class="h-8 text-xs"
                    @update:model-value="(v) => updateImage(img.id, { alt_text: v ? String(v) : null })"
                  />
                </div>

                <!-- Caption -->
                <div class="grid gap-1.5">
                  <Label class="text-xs text-muted-foreground">
                    Caption <span class="opacity-50">(optional)</span>
                  </Label>
                  <Input
                    :model-value="img.caption ?? ''"
                    placeholder="Add a caption…"
                    class="h-8 text-xs"
                    @update:model-value="(v) => updateImage(img.id, { caption: v ? String(v) : null })"
                  />
                </div>

                <!-- Link URL -->
                <div class="grid gap-1.5">
                  <Label class="flex items-center gap-1 text-xs text-muted-foreground">
                    <LinkIcon class="h-3 w-3" />
                    Link URL <span class="opacity-50">(optional)</span>
                  </Label>
                  <Input
                    type="url"
                    :model-value="img.link_url ?? ''"
                    placeholder="https://…"
                    class="h-8 text-xs"
                    @update:model-value="(v) => updateImage(img.id, { link_url: v ? String(v) : null })"
                  />
                </div>

              </div>
            </CollapsibleContent>
          </div>
        </Collapsible>
      </div>

      <!-- Add image button -->
      <Button
        type="button"
        variant="outline"
        size="sm"
        class="mt-3 w-full gap-1.5 text-xs border-dashed"
        @click="addImage"
      >
        <PlusIcon class="h-3.5 w-3.5" />
        Add image
      </Button>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 2 — LAYOUT
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <LayoutGridIcon class="h-3 w-3" />
        Layout
      </p>

      <!-- Columns -->
      <div class="grid gap-2 mb-4">
        <Label class="text-xs text-muted-foreground">Columns</Label>
        <div class="grid grid-cols-3 gap-1.5">
          <button
            v-for="col in columnOptions"
            :key="col.value"
            type="button"
            class="flex flex-col items-center gap-1.5 rounded-md border py-2.5 text-xs
                   font-medium transition"
            :class="(data.columns ?? 1) === col.value
              ? 'border-primary bg-primary/5 text-primary'
              : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
            @click="updateData({ columns: col.value })"
          >
            <!-- Mini grid preview -->
            <div class="flex gap-0.5">
              <span
                v-for="n in col.value"
                :key="n"
                class="block h-3 rounded-sm"
                :class="(data.columns ?? 1) === col.value ? 'bg-primary/60' : 'bg-muted-foreground/30'"
                :style="{ width: col.value === 1 ? '24px' : col.value === 2 ? '11px' : '7px' }"
              />
            </div>
            {{ col.label }}
          </button>
        </div>
      </div>

      <!-- Alignment (only for single column) -->
      <div v-if="(data.columns ?? 1) === 1" class="grid gap-2 mb-4">
        <Label class="text-xs text-muted-foreground">Alignment</Label>
        <div class="grid grid-cols-4 gap-1">
          <button
            v-for="opt in alignmentOptions"
            :key="opt.value"
            type="button"
            class="rounded-md border py-1.5 text-center text-xs font-medium transition"
            :class="(data.alignment ?? 'center') === opt.value
              ? 'border-primary bg-primary/5 text-primary'
              : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
            @click="updateData({ alignment: opt.value })"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- Width (single + non-full alignment) -->
      <div
        v-if="(data.columns ?? 1) === 1 && (data.alignment ?? 'center') !== 'full'"
        class="grid gap-2"
      >
        <Label class="flex items-center justify-between text-xs text-muted-foreground">
          Width
          <span class="font-mono text-foreground">{{ data.width_percent ?? 100 }}%</span>
        </Label>
        <input
          type="range"
          min="20"
          max="100"
          step="5"
          :value="data.width_percent ?? 100"
          class="w-full accent-primary"
          @input="(e) => updateData({ width_percent: Number((e.target as HTMLInputElement).value) })"
        />
        <div class="flex justify-between text-[10px] text-muted-foreground">
          <span>20%</span>
          <span>100%</span>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 3 — DISPLAY
    ══════════════════════════════════════════════ -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <SlidersHorizontalIcon class="h-3 w-3" />
        Display
      </p>

      <!-- Aspect ratio -->
      <div class="grid gap-2 mb-4">
        <Label class="text-xs text-muted-foreground">Aspect ratio</Label>
        <div class="grid grid-cols-4 gap-1">
          <button
            v-for="opt in aspectOptions"
            :key="opt.value"
            type="button"
            class="rounded-md border py-1.5 text-center text-xs font-medium transition"
            :class="(data.aspect_ratio ?? 'auto') === opt.value
              ? 'border-primary bg-primary/5 text-primary'
              : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
            @click="updateData({ aspect_ratio: opt.value })"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- Toggles -->
      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Rounded corners</p>
            <p class="text-[11px] text-muted-foreground">Apply border-radius to images</p>
          </div>
          <Switch
            :model-value="data.rounded ?? true"
            @update:model-value="(v: boolean) => updateData({ rounded: v })"
          />
        </label>
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Show captions</p>
            <p class="text-[11px] text-muted-foreground">Display caption text below each image</p>
          </div>
          <Switch
            :model-value="data.show_captions ?? true"
            @update:model-value="(v: boolean) => updateData({ show_captions: v })"
          />
        </label>
      </div>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>