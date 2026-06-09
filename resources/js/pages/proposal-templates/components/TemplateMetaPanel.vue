<script setup lang="ts">
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  FileTextIcon,
  ImageIcon,
  LinkIcon,
  UploadIcon,
} from '@lucide/vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useTemporaryUploads } from '@/composables/useTemporaryUploads';

const builderStore = useProposalBuilderStore();
const { proposal, templateSettings } = storeToRefs(builderStore);

const { upload, isUploading } = useTemporaryUploads();

const thumbnailTab = ref<'upload' | 'url'>('upload');
const uploadError = ref<string | null>(null);

const templateName = computed<string>({
  get: () => proposal.value.title,
  set: (value: string) => {
    proposal.value.title = value?.trim() ? value : 'Untitled template';
  },
});

const templateDescription = computed<string>({
  get: () => templateSettings.value.description ?? '',
  set: (value: string) => {
    templateSettings.value.description = value || null;
  },
});

const thumbnailUrl = computed<string>({
  get: () => templateSettings.value.thumbnailUrl ?? '',
  set: (value: string) => {
    templateSettings.value.thumbnailUrl = value || null;
  },
});

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (!file) return;
  
  try {
    uploadError.value = null;
    const result = await upload({ thumbnail: file });
    if (result?.thumbnail) {
      thumbnailUrl.value = result.thumbnail;
    }
  } catch (error) {
    uploadError.value = 'Failed to upload image';
    console.error('Upload error:', error);
  }
  
  // Reset file input
  target.value = '';
};

const clearThumbnail = () => {
  thumbnailUrl.value = '';
};

const isValidUrl = (url: string) => {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

const urlError = computed<string | null>(() => {
  if (thumbnailTab.value === 'url' && thumbnailUrl.value && !isValidUrl(thumbnailUrl.value)) {
    return 'Please enter a valid URL';
  }
  return null;
});
</script>

<template>
  <div class="flex flex-col gap-0 text-sm">
    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <FileTextIcon class="h-3 w-3" />
        Template
      </p>

      <div class="mb-3 grid gap-1.5">
        <Label class="text-xs text-muted-foreground">Template Name</Label>
        <Input
          v-model="templateName"
          placeholder="e.g. Brand Identity Package"
          class="h-8 text-sm font-medium"
        />
      </div>

      <div class="grid gap-1.5">
        <Label class="text-xs text-muted-foreground">Description</Label>
        <Textarea
          v-model="templateDescription"
          placeholder="Describe what this template is best for..."
          class="min-h-[60px] text-sm resize-none"
        />
      </div>
    </div>

    <div class="px-4 py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <ImageIcon class="h-3 w-3" />
        Thumbnail
      </p>

      <Tabs v-model="thumbnailTab" class="w-full">
        <TabsList class="grid w-full grid-cols-2">
          <TabsTrigger value="upload" class="text-xs">Upload</TabsTrigger>
          <TabsTrigger value="url" class="text-xs">URL</TabsTrigger>
        </TabsList>

        <TabsContent value="upload" class="mt-3 space-y-3">
          <div class="grid gap-1.5">
            <Label class="flex items-center gap-1 text-xs text-muted-foreground">
              <UploadIcon class="h-3 w-3" />
              Upload Image
            </Label>
            
            <div class="relative">
              <Input
                type="file"
                accept="image/*"
                @change="handleFileUpload"
                :disabled="isUploading"
                class="h-8 text-sm file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
              />
              <div
                v-if="isUploading"
                class="absolute inset-0 flex items-center justify-center bg-background/80 rounded-md"
              >
                <div class="text-xs text-muted-foreground">Uploading...</div>
              </div>
            </div>

            <div v-if="uploadError" class="text-xs text-destructive">
              {{ uploadError }}
            </div>

            <div v-if="thumbnailUrl" class="space-y-2">
              <div class="relative rounded-md border border-border overflow-hidden">
                <img
                  :src="thumbnailUrl"
                  alt="Thumbnail preview"
                  class="w-full h-24 object-cover"
                  @error="uploadError = 'Failed to load image'"
                />
              </div>
              <Button
                variant="outline"
                size="sm"
                @click="clearThumbnail"
                class="w-full h-7 text-xs"
              >
                Clear Image
              </Button>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="url" class="mt-3 space-y-3">
          <div class="grid gap-1.5">
            <Label class="flex items-center gap-1 text-xs text-muted-foreground">
              <LinkIcon class="h-3 w-3" />
              Image URL
            </Label>
            
            <Input
              v-model="thumbnailUrl"
              type="url"
              placeholder="https://example.com/image.jpg"
              class="h-8 text-sm"
              :class="urlError ? 'border-destructive' : ''"
            />

            <div v-if="urlError" class="text-xs text-destructive">
              {{ urlError }}
            </div>

            <div v-if="thumbnailUrl && isValidUrl(thumbnailUrl)" class="space-y-2">
              <div class="relative rounded-md border border-border overflow-hidden">
                <img
                  :src="thumbnailUrl"
                  alt="Thumbnail preview"
                  class="w-full h-24 object-cover"
                  @error="urlError = 'Failed to load image'"
                />
              </div>
              <Button
                variant="outline"
                size="sm"
                @click="clearThumbnail"
                class="w-full h-7 text-xs"
              >
                Clear URL
              </Button>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>
