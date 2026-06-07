<script setup lang="ts">
import { computed, ref } from 'vue';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import {
  RulerIcon,
  PaletteIcon,
  EyeOffIcon,
  StickyNoteIcon,
  SeparatorHorizontalIcon,
} from '@lucide/vue';
import type { BaseBlock, BlockMeta, PaddingSize } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { ColorPresets } from '@/components/ui/color-presets';

const props = defineProps<{ blockId: string }>();

const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const meta  = computed<BlockMeta>(() => block.value?.meta ?? {
  padding_top:      'md',
  padding_bottom:   'md',
  background_color: null,
  border_top:       false,
  border_bottom:    false,
  is_hidden:        false,
  notes:            null,
});

const updateMeta = (changes: Partial<BlockMeta>) => {
  if (!block.value) return;
  store.updateBlockMeta(block.value.id, { ...meta.value, ...changes });
};

// ── Padding options ───────────────────────────────────────────
type PaddingOption = { label: string; value: PaddingSize; px: string }

const paddingOptions: PaddingOption[] = [
  { label: 'None',   value: 'none', px: '0'  },
  { label: 'S',      value: 'sm',   px: '12' },
  { label: 'M',      value: 'md',   px: '24' },
  { label: 'L',      value: 'lg',   px: '40' },
  { label: 'XL',     value: 'xl',   px: '64' },
]


const backgroundColorModel = computed({
  get: () => meta.value.background_color ?? 'transparent',
  set: (value: string) => {
    updateMeta({ background_color: value === 'transparent' ? null : value });
  },
});

// ── Notes expanded state ──────────────────────────────────────
const notesOpen = ref(!!(meta.value.notes?.length))
</script>

<template>
  <div v-if="block" class="flex flex-col gap-0 text-sm">

    <!-- ══════════════════════════════════════════════
         SECTION 1 — SPACING
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <RulerIcon class="h-3 w-3" />
        Spacing
      </p>

      <!-- Padding top -->
      <div class="mb-3">
        <div class="mb-1.5 flex items-center justify-between">
          <span class="text-xs text-muted-foreground">Top</span>
          <span class="text-[11px] font-mono text-muted-foreground">
            {{ paddingOptions.find(o => o.value === meta.padding_top)?.px ?? '24' }}px
          </span>
        </div>
        <div class="grid grid-cols-5 gap-1">
          <Tooltip v-for="opt in paddingOptions" :key="opt.value">
            <TooltipTrigger as-child>
              <button
                type="button"
                class="flex flex-col items-center gap-1 rounded-md border py-2
                       text-[11px] font-medium transition"
                :class="meta.padding_top === opt.value
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
                @click="updateMeta({ padding_top: opt.value })"
              >
                <!-- Visual bar representing padding size -->
                <span
                  class="block w-4 rounded-sm transition-all"
                  :class="meta.padding_top === opt.value ? 'bg-primary/50' : 'bg-muted-foreground/30'"
                  :style="{
                    height: opt.value === 'none' ? '1px'
                          : opt.value === 'sm'   ? '3px'
                          : opt.value === 'md'   ? '5px'
                          : opt.value === 'lg'   ? '7px'
                          :                        '10px'
                  }"
                />
                {{ opt.label }}
              </button>
            </TooltipTrigger>
            <TooltipContent side="bottom" class="text-xs">
              {{ opt.label === 'None' ? 'No padding' : `${opt.px}px top padding` }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>

      <!-- Padding bottom -->
      <div>
        <div class="mb-1.5 flex items-center justify-between">
          <span class="text-xs text-muted-foreground">Bottom</span>
          <span class="text-[11px] font-mono text-muted-foreground">
            {{ paddingOptions.find(o => o.value === meta.padding_bottom)?.px ?? '24' }}px
          </span>
        </div>
        <div class="grid grid-cols-5 gap-1">
          <Tooltip v-for="opt in paddingOptions" :key="opt.value">
            <TooltipTrigger as-child>
              <button
                type="button"
                class="flex flex-col items-center gap-1 rounded-md border py-2
                       text-[11px] font-medium transition"
                :class="meta.padding_bottom === opt.value
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
                @click="updateMeta({ padding_bottom: opt.value })"
              >
                <span
                  class="block w-4 rounded-sm transition-all"
                  :class="meta.padding_bottom === opt.value ? 'bg-primary/50' : 'bg-muted-foreground/30'"
                  :style="{
                    height: opt.value === 'none' ? '1px'
                          : opt.value === 'sm'   ? '3px'
                          : opt.value === 'md'   ? '5px'
                          : opt.value === 'lg'   ? '7px'
                          :                        '10px'
                  }"
                />
                {{ opt.label }}
              </button>
            </TooltipTrigger>
            <TooltipContent side="bottom" class="text-xs">
              {{ opt.label === 'None' ? 'No padding' : `${opt.px}px bottom padding` }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 2 — BACKGROUND
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <PaletteIcon class="h-3 w-3" />
        Background
      </p>

      <ColorPresets
        v-model:model-value="backgroundColorModel"
        placeholder="transparent"
        :supports-transparent="true"
      />
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 3 — DIVIDERS
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <SeparatorHorizontalIcon class="h-3 w-3" />
        Dividers
      </p>

      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Border above</p>
            <p class="text-[11px] text-muted-foreground">Thin separator line above block</p>
          </div>
          <Switch
            :model-value="meta.border_top ?? false"
            @update:model-value="(v: boolean) => updateMeta({ border_top: v })"
          />
        </label>
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Border below</p>
            <p class="text-[11px] text-muted-foreground">Thin separator line below block</p>
          </div>
          <Switch
            :model-value="meta.border_bottom ?? false"
            @update:model-value="(v: boolean) => updateMeta({ border_bottom: v })"
          />
        </label>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 4 — VISIBILITY
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <EyeOffIcon class="h-3 w-3" />
        Visibility
      </p>

      <label
        class="flex cursor-pointer items-center justify-between rounded-lg border
               px-3 py-2.5 transition-colors hover:bg-muted/50"
        :class="meta.is_hidden
          ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950/30'
          : 'border-border'"
      >
        <div>
          <p class="text-xs font-medium text-foreground">Hide this block</p>
          <p class="text-[11px] text-muted-foreground">
            {{ meta.is_hidden
              ? 'Hidden — not visible to client'
              : 'Visible — shown in client preview' }}
          </p>
        </div>
        <Switch
          :model-value="meta.is_hidden ?? false"
          @update:model-value="(v: boolean) => updateMeta({ is_hidden: v })"
        />
      </label>

      <p v-if="meta.is_hidden" class="mt-2 text-[11px] text-amber-600 dark:text-amber-400">
        This block is hidden. It will not appear in the client portal or PDF export.
      </p>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 5 — INTERNAL NOTES
    ══════════════════════════════════════════════ -->
    <div class="py-3">
      <button
        type="button"
        class="mb-3 flex w-full items-center justify-between text-[11px] font-semibold
               uppercase tracking-wider text-muted-foreground hover:text-foreground transition-colors"
        @click="notesOpen = !notesOpen"
      >
        <span class="flex items-center gap-1.5">
          <StickyNoteIcon class="h-3 w-3" />
          Internal notes
          <span
            v-if="meta.notes?.length"
            class="inline-flex h-4 min-w-4 items-center justify-center rounded-full
                   bg-primary/15 px-1 text-[10px] font-semibold text-primary"
          >
            •
          </span>
        </span>
        <span class="text-[11px] normal-case font-normal">
          {{ notesOpen ? 'Hide' : 'Show' }}
        </span>
      </button>

      <template v-if="notesOpen">
        <Textarea
          :model-value="meta.notes ?? ''"
          placeholder="Add an internal note about this block — only visible to your team, never to the client…"
          rows="3"
          class="resize-none text-xs leading-relaxed placeholder:text-muted-foreground/60"
          @update:model-value="(v) => updateMeta({ notes: v ? String(v) : null })"
        />
        <p class="mt-1.5 text-[11px] text-muted-foreground">
          Never shown to the client in any view.
        </p>
      </template>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block not found.</p>
</template>