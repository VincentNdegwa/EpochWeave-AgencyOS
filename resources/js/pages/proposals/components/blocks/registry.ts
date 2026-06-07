import type { Component } from 'vue';
import {
  LayoutPanelTop,
  AlignLeft,
  Image as ImageIcon,
  Building as BuildingIcon,
  Minus as MinusIcon,
  MoveVertical,
} from '@lucide/vue';
import type { BlockType } from '@/types/proposal-builder';
import CoverBlock from '@/pages/proposals/components/blocks/CoverBlock/CoverBlock.vue';
import CoverBlockSettings from '@/pages/proposals/components/blocks/CoverBlock/CoverBlockSettings.vue';
import RichTextBlock from '@/pages/proposals/components/blocks/RichTextBlock/RichTextBlock.vue';
import RichTextBlockSettings from '@/pages/proposals/components/blocks/RichTextBlock/RichTextBlockSettings.vue';
import ImageBlock from '@/pages/proposals/components/blocks/ImageBlock/ImageBlock.vue';
import ImageBlockSettings from '@/pages/proposals/components/blocks/ImageBlock/ImageBlockSettings.vue';
import LogoBlock from '@/pages/proposals/components/blocks/LogoBlock/LogoBlock.vue';
import LogoBlockSettings from '@/pages/proposals/components/blocks/LogoBlock/LogoBlockSettings.vue';
import DividerBlock from '@/pages/proposals/components/blocks/DividerBlock/DividerBlock.vue';
import DividerBlockSettings from '@/pages/proposals/components/blocks/DividerBlock/DividerBlockSettings.vue';
import SpacerBlock from '@/pages/proposals/components/blocks/SpacerBlock/SpacerBlock.vue';
import SpacerBlockSettings from '@/pages/proposals/components/blocks/SpacerBlock/SpacerBlockSettings.vue';

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
  {
    type: 'logo',
    label: 'Logos',
    category: 'layout',
    description: 'Place workspace and client logos side-by-side.',
    icon: BuildingIcon,
    component: LogoBlock,
    settings: LogoBlockSettings,
  },
  {
    type: 'divider',
    label: 'Divider',
    category: 'layout',
    description: 'Horizontal rule to separate sections.',
    icon: MinusIcon,
    component: DividerBlock,
    settings: DividerBlockSettings,
  },
  {
    type: 'spacer',
    label: 'Spacer',
    category: 'layout',
    description: 'Adjustable vertical whitespace.',
    icon: MoveVertical,
    component: SpacerBlock,
    settings: SpacerBlockSettings,
  },
];

export function getBlockDefinition(type: BlockType): BlockRegistryEntry | undefined {
  return blockRegistry.find((entry) => entry.type === type);
}
