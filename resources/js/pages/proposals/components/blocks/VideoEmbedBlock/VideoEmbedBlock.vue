<script setup lang="ts">
import { VideoIcon, LinkIcon } from '@lucide/vue';
import { computed } from 'vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, VideoEmbedBlockData } from '@/types/proposal-builder';

const props = defineProps<{
  data: VideoEmbedBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<VideoEmbedBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const embedUrl = computed(() => {
  const url = props.data.url;

  if (!url) {
return null;
}

  if (props.data.platform === 'youtube') {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);

    if (match) {
return `https://www.youtube.com/embed/${match[1]}`;
}
  }

  if (props.data.platform === 'vimeo') {
    const match = url.match(/vimeo\.com\/(\d+)/);

    if (match) {
return `https://player.vimeo.com/video/${match[1]}`;
}
  }

  return url; // custom / direct
});

const hasVideo = computed(() => !!embedUrl.value);
</script>

<template>
  <section class="px-8 py-6">

    <!-- Video embed -->
    <div v-if="hasVideo" class="space-y-3">
      <div class="overflow-hidden rounded-xl border border-border aspect-video">
        <iframe
          :src="embedUrl!"
          class="h-full w-full border-0"
          allowfullscreen
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        />
      </div>
      <div v-if="data.title || data.caption" class="space-y-0.5">
        <p v-if="data.title" class="text-sm font-medium text-foreground">{{ data.title }}</p>
        <p v-if="data.caption" class="text-xs text-muted-foreground">{{ data.caption }}</p>
      </div>
    </div>

    <!-- Empty state (edit mode) -->
    <div
      v-else-if="!isLocked"
      class="flex flex-col items-center justify-center gap-3 rounded-xl border-2
             border-dashed border-border bg-muted/30 px-8 py-12 text-center"
    >
      <div class="flex h-12 w-12 items-center justify-center rounded-full border border-border bg-background text-muted-foreground">
        <VideoIcon class="h-5 w-5" />
      </div>
      <div>
        <p class="text-sm font-medium text-foreground">Paste a video URL</p>
        <p class="mt-0.5 text-xs text-muted-foreground">YouTube, Vimeo, or a direct link</p>
      </div>
      <div class="flex w-full max-w-sm items-center gap-2 rounded-lg border border-border bg-background px-3">
        <LinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground" />
        <input
          type="url"
          placeholder="https://youtube.com/watch?v=…"
          class="h-9 flex-1 border-none bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground/60"
          @input="updateData({ url: ($event.target as HTMLInputElement).value })"
        />
      </div>
    </div>

    <!-- Empty locked -->
    <div v-else class="flex flex-col items-center justify-center gap-2 rounded-xl border border-border py-10 text-center">
      <VideoIcon class="h-5 w-5 text-muted-foreground/40" />
      <p class="text-sm text-muted-foreground">No video provided.</p>
    </div>

  </section>
</template>