<script setup lang="ts">
import { PlusIcon } from '@lucide/vue';
import { computed } from 'vue';
import { createDefaultBlock } from '@/composables/blockFactory';
import { useBlockRegistry } from '@/composables/useBlockRegistry';
import BlockWrapper from '@/pages/proposals/components/canvas/BlockWrapper.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, ColumnBlockData } from '@/types/proposal-builder';

const props = defineProps<{
  data: ColumnBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store    = useProposalBuilderStore();
const registry = useBlockRegistry();
const factory  = createDefaultBlock;

const updateData = (changes: Partial<ColumnBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const selectChildBlock = (childBlockId: string) => {
  store.selectBlock(childBlockId);
};

const isChildSelected = (childBlockId: string) => {
  return store.selectedBlockId === childBlockId;
};

// ── Column grid class ─────────────────────────────────────────
const gridClass = computed(() => {
  const gapMap = { sm: 'gap-3', md: 'gap-6', lg: 'gap-10' };
  const gap    = gapMap[props.data.gap ?? 'md'];

  if (props.data.columns === 1) {
return `grid grid-cols-1 ${gap}`;
}

  if (props.data.columns === 2) {
return `grid grid-cols-2 ${gap}`;
}

  return `grid grid-cols-3 ${gap}`;
});

// ── Column vertical alignment ─────────────────────────────────
const alignClass = computed(() => ({
  start:  'items-start',
  center: 'items-center',
  end:    'items-end',
}[props.data.vertical_align ?? 'start']));

// ── Per-column block manipulation ─────────────────────────────
const addBlockToColumn = (colIndex: number, type: BaseBlock['type']) => {
  const newBlock   = factory(type);
  const children   = props.data.children.map((col, i) =>
    i === colIndex ? [...col, newBlock] : col,
  );
  updateData({ children });
};

const removeBlockFromColumn = (colIndex: number, blockId: string) => {
  const children = props.data.children.map((col, i) =>
    i === colIndex ? col.filter((b) => b.id !== blockId) : col,
  );
  updateData({ children });
};

const updateColumnBlock = (colIndex: number, blockId: string, data: any) => {
  // Use the store's recursive update method to handle nested block updates
  store.updateBlockDataRecursive(blockId, data);
};

// Resolve component for a child block
const resolveComponent = (block: BaseBlock) => registry.resolveComponent(block);

// ── Column width styles (for custom ratios) ───────────────────
const colWidthStyle = (colIndex: number) => {
  if (!props.data.column_widths || props.data.columns !== 2) {
return {};
}

  const widths = props.data.column_widths;

  return { flex: `0 0 ${widths[colIndex] ?? 50}%`, maxWidth: `${widths[colIndex] ?? 50}%` };
};

// Allowed child block types — not all blocks make sense inside a column
const allowedChildTypes = [
  'rich_text', 'image', 'logo', 'divider', 'spacer',
  'pricing_table', 'timeline', 'team_member', 'testimonial',
  'terms', 'cta', 'video_embed', 'file_attachment', 'callout',
] as const;
</script>

<template>
  <section class="px-8 py-6">
    <div
      :class="[
        data.columns > 1
          ? (data.column_widths ? 'flex' : gridClass)
          : gridClass,
        alignClass,
      ]"
    >
      <!-- Each column -->
      <div
        v-for="(column, colIndex) in data.children"
        :key="colIndex"
        class="min-w-0 flex-1"
        :style="data.column_widths ? colWidthStyle(colIndex) : {}"
      >
        <!-- Column label (edit mode only) -->
        <div
          v-if="!isLocked"
          class="mb-2 flex items-center gap-1.5"
        >
          <div class="h-px flex-1 bg-border" />
          <span class="text-[10px] font-medium text-muted-foreground/60 uppercase tracking-wider">
            Col {{ colIndex + 1 }}
          </span>
          <div class="h-px flex-1 bg-border" />
        </div>

        <!-- Blocks inside this column -->
        <div class="space-y-2">
          <div
            v-for="childBlock in column"
            :key="childBlock.id"
          >
            <!-- Render child block using the registry with BlockWrapper for meta styles -->
            <div 
              class="relative group/child"
              :class="{ 'ring-2 ring-primary ring-offset-1': isChildSelected(childBlock.id) }"
              data-nested-block="true"
              @click.stop="selectChildBlock(childBlock.id)"
            >
              <BlockWrapper 
                :block="childBlock" 
                :is-locked="isLocked"
              >
                <component
                  :is="resolveComponent(childBlock)"
                  :data="childBlock.data"
                  :block="childBlock"
                  :is-locked="isLocked"
                  @update:data="(d: any) => updateColumnBlock(colIndex, childBlock.id, d)"
                />
              </BlockWrapper>

              <!-- Remove child button (edit mode) -->
              <button
                v-if="!isLocked"
                type="button"
                class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center
                       rounded bg-background/90 shadow border border-border
                       text-muted-foreground opacity-0 transition hover:text-destructive
                       group-hover/child:opacity-100 z-10"
                @click="removeBlockFromColumn(colIndex, childBlock.id)"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Add block to column (edit mode) -->
          <div v-if="!isLocked">
            <!-- Empty column placeholder -->
            <div
              v-if="column.length === 0"
              class="flex flex-col items-center justify-center gap-2 rounded-lg
                     border-2 border-dashed border-border bg-muted/20 py-8 text-center"
            >
              <p class="text-xs text-muted-foreground">Empty column</p>
            </div>

            <!-- Add block picker — simple dropdown -->
            <div class="relative mt-1 flex justify-center">
              <details class="group/add">
                <summary
                  class="flex cursor-pointer list-none items-center gap-1.5 rounded-md
                         border border-dashed border-border px-3 py-1.5 text-xs
                         text-muted-foreground transition hover:border-primary hover:text-foreground"
                >
                  <PlusIcon class="h-3 w-3" />
                  Add block
                </summary>
                <div
                  class="absolute left-1/2 z-30 mt-1 w-44 -translate-x-1/2 rounded-lg border
                         border-border bg-background shadow-lg overflow-hidden"
                >
                  <button
                    v-for="type in allowedChildTypes"
                    :key="type"
                    type="button"
                    class="flex w-full items-center px-3 py-2 text-left text-xs
                           text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="addBlockToColumn(colIndex, type as BaseBlock['type'])"
                  >
                    {{ type.replace(/_/g, ' ') }}
                  </button>
                </div>
              </details>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>