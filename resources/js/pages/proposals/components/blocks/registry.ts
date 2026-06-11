import type { Component } from 'vue';
import {
  LayoutPanelTop,
  AlignLeft,
  Image as ImageIcon,
  Building as BuildingIcon,
  Minus as MinusIcon,
  MoveVertical,
  Coins,
  Timer,
  Users,
  Quote,
  FileText,
  PenLine,
  MousePointerClick,
  Video,
  MessageSquare,
  Columns,
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
import PricingTableBlock from '@/pages/proposals/components/blocks/PricingTableBlock/PricingTableBlock.vue';
import PricingTableBlockSettings from '@/pages/proposals/components/blocks/PricingTableBlock/PricingTableBlockSettings.vue';
import TimelineBlock from '@/pages/proposals/components/blocks/TimelineBlock/TimelineBlock.vue';
import TimelineBlockSettings from '@/pages/proposals/components/blocks/TimelineBlock/TimelineBlockSettings.vue';
import TeamMemberBlock from '@/pages/proposals/components/blocks/TeamMemberBlock/TeamMemberBlock.vue';
import TeamMemberBlockSettings from '@/pages/proposals/components/blocks/TeamMemberBlock/TeamMemberBlockSettings.vue';
import TestimonialBlock from '@/pages/proposals/components/blocks/TestimonialBlock/TestimonialBlock.vue';
import TestimonialBlockSettings from '@/pages/proposals/components/blocks/TestimonialBlock/TestimonialBlockSettings.vue';
import TermsBlock from '@/pages/proposals/components/blocks/TermsBlock/TermsBlock.vue';
import TermsBlockSettings from '@/pages/proposals/components/blocks/TermsBlock/TermsBlockSettings.vue';
import SignatureBlock from '@/pages/proposals/components/blocks/SignatureBlock/SignatureBlock.vue';
import SignatureBlockSettings from '@/pages/proposals/components/blocks/SignatureBlock/SignatureBlockSettings.vue';
import CtaBlock from '@/pages/proposals/components/blocks/CtaBlock/CtaBlock.vue';
import CtaBlockSettings from '@/pages/proposals/components/blocks/CtaBlock/CtaBlockSettings.vue';
import VideoEmbedBlock from '@/pages/proposals/components/blocks/VideoEmbedBlock/VideoEmbedBlock.vue';
import VideoEmbedBlockSettings from '@/pages/proposals/components/blocks/VideoEmbedBlock/VideoEmbedBlockSettings.vue';
import CalloutBlock from '@/pages/proposals/components/blocks/CalloutBlock/CalloutBlock.vue';
import CalloutBlockSettings from '@/pages/proposals/components/blocks/CalloutBlock/CalloutBlockSettings.vue';
import ColumnBlock from '@/pages/proposals/components/blocks/ColumnBlock/ColumnBlock.vue';
import ColumnBlockSettings from '@/pages/proposals/components/blocks/ColumnBlock/ColumnBlockSettings.vue';

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
  {
    type: 'pricing_table',
    label: 'Pricing table',
    category: 'content',
    description: 'Line items, discounts, taxes, and totals.',
    icon: Coins,
    component: PricingTableBlock,
    settings: PricingTableBlockSettings,
  },
  {
    type: 'timeline',
    label: 'Timeline',
    category: 'content',
    description: 'Project phases and milestones.',
    icon: Timer,
    component: TimelineBlock,
    settings: TimelineBlockSettings,
  },
  {
    type: 'team_member',
    label: 'Team members',
    category: 'content',
    description: 'Show your team with photos and bios.',
    icon: Users,
    component: TeamMemberBlock,
    settings: TeamMemberBlockSettings,
  },
  {
    type: 'testimonial',
    label: 'Testimonials',
    category: 'content',
    description: 'Client quotes and social proof.',
    icon: Quote,
    component: TestimonialBlock,
    settings: TestimonialBlockSettings,
  },
  {
    type: 'terms',
    label: 'Terms',
    category: 'content',
    description: 'Legal terms and conditions.',
    icon: FileText,
    component: TermsBlock,
    settings: TermsBlockSettings,
  },
  {
    type: 'signature',
    label: 'Signature',
    category: 'content',
    description: 'Client sign-off acceptance.',
    icon: PenLine,
    component: SignatureBlock,
    settings: SignatureBlockSettings,
  },
  {
    type: 'cta',
    label: 'Call to action',
    category: 'content',
    description: 'Button with heading and description.',
    icon: MousePointerClick,
    component: CtaBlock,
    settings: CtaBlockSettings,
  },
  {
    type: 'video_embed',
    label: 'Video',
    category: 'content',
    description: 'Embed YouTube, Vimeo, or custom videos.',
    icon: Video,
    component: VideoEmbedBlock,
    settings: VideoEmbedBlockSettings,
  },
  {
    type: 'callout',
    label: 'Callout',
    category: 'content',
    description: 'Highlighted box with icon — info, tip, warning, quote.',
    icon: MessageSquare,
    component: CalloutBlock,
    settings: CalloutBlockSettings,
  },
  {
    type: 'column',
    label: 'Columns',
    category: 'layout',
    description: 'Place blocks side by side in 2 or 3 columns.',
    icon: Columns,
    component: ColumnBlock,
    settings: ColumnBlockSettings,
  },
];

export function getBlockDefinition(type: BlockType): BlockRegistryEntry | undefined {
  return blockRegistry.find((entry) => entry.type === type);
}
