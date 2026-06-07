<script setup lang="ts">
import { ref, computed } from 'vue';
import { ImageIcon, PlusIcon, XIcon, UploadIcon } from '@lucide/vue';
import { nanoid } from 'nanoid';
import type { BaseBlock, ImageBlockData, ImageItem } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: ImageBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const uploadingId = ref<string | null>(null);
const dragOverId  = ref<string | null>(null);

const updateData = (changes: Partial<ImageBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const updateImage = (id: string, changes: Partial<ImageItem>) => {
  updateData({
    images: props.data.images.map((img) =>
      img.id === id ? { ...img, ...changes } : img,
    ),
  });
};

const addImage = () => {
  updateData({
    images: [
      ...props.data.images,
      { id: nanoid(8), url: '', alt_text: null, caption: null, link_url: null },
    ],
  });
};

const removeImage = (id: string) => {
  updateData({ images: props.data.images.filter((img) => img.id !== id) });
};

// ── File upload per image slot ────────────────────────────────
const handleFileUpload = (id: string, event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;
  uploadingId.value = id;
  const reader = new FileReader();
  reader.onload = () => {
    updateImage(id, { url: typeof reader.result === 'string' ? reader.result : '' });
    uploadingId.value = null;
  };
  reader.onerror = () => { uploadingId.value = null; };
  reader.readAsDataURL(file);
};

// ── Drag-over highlight for drop zones ───────────────────────
const handleDragOver = (id: string, e: DragEvent) => {
  e.preventDefault();
  dragOverId.value = id;
};

const handleDragLeave = () => { dragOverId.value = null; };

const handleDrop = (id: string, e: DragEvent) => {
  e.preventDefault();
  dragOverId.value = null;
  const file = e.dataTransfer?.files?.[0];
  if (!file || !file.type.startsWith('image/')) return;
  uploadingId.value = id;
  const reader = new FileReader();
  reader.onload = () => {
    updateImage(id, { url: typeof reader.result === 'string' ? reader.result : '' });
    uploadingId.value = null;
  };
  reader.onerror = () => { uploadingId.value = null; };
  reader.readAsDataURL(file);
};

// ── Computed styles ───────────────────────────────────────────
const gridClass = computed(() => {
  if (props.data.columns === 1) return '';
  if (props.data.columns === 2) return 'grid grid-cols-2 gap-4';
  return 'grid grid-cols-3 gap-4';
});

const wrapperClass = computed(() => {
  if (props.data.columns > 1) return '';
  const align: Record<string, string> = {
    left:   'mr-auto',
    center: 'mx-auto',
    right:  'ml-auto',
    full:   'w-full',
  };
  return align[props.data.alignment] ?? 'mx-auto';
});

const wrapperStyle = computed(() => {
  if (props.data.columns > 1 || props.data.alignment === 'full') return {};
  return { width: `${props.data.width_percent ?? 100}%` };
});

const aspectClass = computed(() => {
  const map: Record<string, string> = {
    auto:     '',
    square:   'aspect-square',
    video:    'aspect-video',
    portrait: 'aspect-[3/4]',
  };
  return map[props.data.aspect_ratio ?? 'auto'] ?? '';
});

const imageClass = computed(() => [
  'w-full object-cover',
  aspectClass.value,
  props.data.rounded ? 'rounded-lg' : '',
].filter(Boolean).join(' '));

const hasImages = computed(() => props.data.images.some((img) => img.url.length > 0));
</script>

<template>
  <section class="px-6">

    <!-- ── Populated state ────────────────────────────────────── -->
    <template v-if="hasImages || isLocked">

      <!-- Single image wrapper (alignment + width apply) -->
      <div
        v-if="data.columns === 1"
        :class="wrapperClass"
        :style="wrapperStyle"
      >
        <template v-for="img in data.images" :key="img.id">
          <div v-if="img.url" class="group/img relative">
            <a
              v-if="img.link_url && isLocked"
              :href="img.link_url"
              target="_blank"
              rel="noopener"
            >
              <img :src="img.url" :alt="img.alt_text ?? ''" :class="imageClass" />
            </a>
            <img v-else :src="img.url" :alt="img.alt_text ?? ''" :class="imageClass" />

            <!-- Remove button (edit mode) -->
            <button
              v-if="!isLocked"
              type="button"
              class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center
                     rounded-full bg-background/90 text-muted-foreground opacity-0
                     shadow transition hover:text-destructive
                     group-hover/img:opacity-100"
              @click="removeImage(img.id)"
            >
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <!-- Caption -->
          <p
            v-if="img.caption && data.show_captions"
            class="mt-2 text-center text-xs text-muted-foreground"
          >
            {{ img.caption }}
          </p>
        </template>
      </div>

      <!-- Multi-column grid -->
      <div v-else :class="gridClass">
        <div
          v-for="img in data.images"
          :key="img.id"
          class="group/img relative"
        >
          <template v-if="img.url">
            <a
              v-if="img.link_url && isLocked"
              :href="img.link_url"
              target="_blank"
              rel="noopener"
            >
              <img :src="img.url" :alt="img.alt_text ?? ''" :class="imageClass" />
            </a>
            <img v-else :src="img.url" :alt="img.alt_text ?? ''" :class="imageClass" />

            <!-- Remove (edit) -->
            <button
              v-if="!isLocked"
              type="button"
              class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center
                     rounded-full bg-background/90 text-muted-foreground opacity-0
                     shadow transition hover:text-destructive
                     group-hover/img:opacity-100"
              @click="removeImage(img.id)"
            >
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </template>

          <!-- Empty slot in grid (edit mode) -->
          <label
            v-else-if="!isLocked"
            class="flex cursor-pointer flex-col items-center justify-center gap-2
                   rounded-lg border-2 border-dashed border-border bg-muted/30 p-6
                   text-center transition hover:border-primary hover:bg-muted/50"
            :class="[
              aspectClass,
              dragOverId === img.id ? 'border-primary bg-primary/5' : '',
            ]"
            @dragover="(e) => handleDragOver(img.id, e)"
            @dragleave="handleDragLeave"
            @drop="(e) => handleDrop(img.id, e)"
          >
            <UploadIcon
              v-if="uploadingId !== img.id"
              class="h-5 w-5 text-muted-foreground"
            />
            <span v-if="uploadingId === img.id" class="text-xs text-muted-foreground">
              Uploading…
            </span>
            <span v-else class="text-xs text-muted-foreground">
              Drop or click to upload
            </span>
            <input
              type="file"
              accept="image/*"
              class="sr-only"
              @change="(e) => handleFileUpload(img.id, e)"
            />
          </label>

          <!-- Caption -->
          <p
            v-if="img.caption && data.show_captions"
            class="mt-1.5 text-center text-xs text-muted-foreground"
          >
            {{ img.caption }}
          </p>
        </div>

        <!-- Add another slot (edit mode, grid only) -->
        <button
          v-if="!isLocked"
          type="button"
          class="flex flex-col items-center justify-center gap-2 rounded-lg
                 border-2 border-dashed border-border bg-muted/20 p-6 text-center
                 transition hover:border-primary hover:bg-muted/40"
          :class="aspectClass"
          @click="addImage"
        >
          <PlusIcon class="h-5 w-5 text-muted-foreground" />
          <span class="text-xs text-muted-foreground">Add image</span>
        </button>
      </div>
    </template>

    <!-- ── Empty state (edit mode, no images yet) ─────────────── -->
    <label
      v-else-if="!isLocked"
      class="group/drop flex cursor-pointer flex-col items-center justify-center
             gap-3 rounded-xl border-2 border-dashed border-border bg-muted/30
             px-8 py-14 text-center transition
             hover:border-primary hover:bg-muted/50"
      :class="dragOverId === 'root' ? 'border-primary bg-primary/5' : ''"
      @dragover="(e) => { e.preventDefault(); dragOverId = 'root'; }"
      @dragleave="handleDragLeave"
      @drop="(e) => {
        e.preventDefault();
        dragOverId = null;
        const file = e.dataTransfer?.files?.[0];
        if (file?.type.startsWith('image/')) {
          addImage();
          handleDrop(data.images[data.images.length - 1]?.id ?? '', e);
        }
      }"
    >
      <div class="flex h-12 w-12 items-center justify-center rounded-full
                  bg-background shadow-sm border border-border
                  group-hover/drop:border-primary group-hover/drop:text-primary
                  text-muted-foreground transition">
        <ImageIcon class="h-5 w-5" />
      </div>
      <div>
        <p class="text-sm font-medium text-foreground">Drop an image here</p>
        <p class="mt-0.5 text-xs text-muted-foreground">or click to browse your files</p>
      </div>
      <p class="text-[11px] text-muted-foreground">PNG, JPG, WEBP, GIF · max 10 MB</p>
      <input
        type="file"
        accept="image/*"
        class="sr-only"
        @change="(e) => {
          const file = (e.target as HTMLInputElement).files?.[0];
          if (!file) return;
          const newId = nanoid(8);
          updateData({ images: [{ id: newId, url: '', alt_text: null, caption: null, link_url: null }] });
          handleFileUpload(newId, e);
        }"
      />
    </label>

    <!-- ── Locked empty ────────────────────────────────────────── -->
    <div
      v-else
      class="rounded-lg border border-border px-8 py-10 text-center text-sm text-muted-foreground"
    >
      <ImageIcon class="mx-auto mb-2 h-5 w-5 opacity-40" />
      No image provided.
    </div>

  </section>
</template>