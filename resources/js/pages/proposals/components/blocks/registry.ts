import type { Component } from 'vue';
import {
  LayoutPanelTop,
  AlignLeft,
  Image as ImageIcon,
} from '@lucide/vue';
import type { BlockType } from '@/types/proposal-builder';
import CoverBlock from '@/pages/proposals/components/blocks/CoverBlock/CoverBlock.vue';
import CoverBlockSettings from '@/pages/proposals/components/blocks/CoverBlock/CoverBlockSettings.vue';
import RichTextBlock from '@/pages/proposals/components/blocks/RichTextBlock/RichTextBlock.vue';
import RichTextBlockSettings from '@/pages/proposals/components/blocks/RichTextBlock/RichTextBlockSettings.vue';
import ImageBlock from '@/pages/proposals/components/blocks/ImageBlock/ImageBlock.vue';
import ImageBlockSettings from '@/pages/proposals/components/blocks/ImageBlock/ImageBlockSettings.vue';

export type BlockCategory = 'layout' | 'content';

export interface BlockRegistryEntry {
  type: BlockType;
  label: string;
  category: BlockCategory;
  description: string;
  icon: Component;
  component: Component;
  settings: Component;
}

export const blockCategories: Record<BlockCategory, string> = {
  layout: 'Layout',
  content: 'Content',
};

export const blockRegistry: BlockRegistryEntry[] = [
  {
    type: 'cover',
    label: 'Cover',
    category: 'layout',
    description: 'Branded hero with headings and metadata.',
    icon: LayoutPanelTop,
    component: CoverBlock,
    settings: CoverBlockSettings,
  },
  {
    type: 'rich_text',
    label: 'Rich text',
    category: 'content',
    description: 'Paragraphs, lists, and basic formatting.',
    icon: AlignLeft,
    component: RichTextBlock,
    settings: RichTextBlockSettings,
  },
  {
    type: 'image',
    label: 'Image',
    category: 'content',
    description: 'Upload screenshots or photos with captions.',
    icon: ImageIcon,
    component: ImageBlock,
    settings: ImageBlockSettings,
  },
];

export function getBlockDefinition(type: BlockType): BlockRegistryEntry | undefined {
  return blockRegistry.find((entry) => entry.type === type);
}
