export type BlockType =
  | 'cover'
  | 'rich_text'
  | 'image'
  | 'logo'
  | 'divider'
  | 'spacer'
  | 'pricing_table'
  | 'timeline'
  | 'team_member'
  | 'testimonial'
  | 'terms'
  | 'signature'
  | 'cta'
  | 'video_embed'
  | 'file_attachment';

export interface BaseBlock {
  id: string;
  type: BlockType;
  sort_order: number;
  is_locked: boolean;
  data: BlockData;
  meta: BlockMeta;
}

export interface BlockMeta {
  padding_top: 'none' | 'sm' | 'md' | 'lg';
  padding_bottom: 'none' | 'sm' | 'md' | 'lg';
}

export type BlockData =
  | CoverBlockData
  | RichTextBlockData
  | ImageBlockData
  | LogoBlockData
  | DividerBlockData
  | SpacerBlockData
  | PricingTableBlockData
  | TimelineBlockData
  | TeamMemberBlockData
  | TestimonialBlockData
  | TermsBlockData
  | SignatureBlockData
  | CtaBlockData
  | VideoEmbedBlockData
  | FileAttachmentBlockData;

export interface CoverBlockData {
  heading: string;
  subheading: string | null;
  background_type: 'color' | 'image';
  background_value: string;
  text_color: string;
  show_logo: boolean;
  show_date: boolean;
  show_proposal_number: boolean;
}

export interface RichTextBlockData {
  title: string | null;
  content: string;
  show_title: boolean;
  title_color?: string;
  content_color?: string;
  background_color?: string;
  full_width: boolean;
}

export interface ImageBlockData {
  url: string;
  alt_text: string | null;
  caption: string | null;
  alignment: 'left' | 'center' | 'right' | 'full';
  width_percent: number;
  link_url: string | null;
}

export interface LogoBlockData {
  layout: 'workspace_only' | 'client_only' | 'both_side_by_side';
  workspace_logo_url: string | null;
  client_logo_url: string | null;
  height_px: number;
  alignment: 'left' | 'center' | 'right';
}

export interface DividerBlockData {
  style: 'solid' | 'dashed' | 'dotted' | 'none';
  color: string;
  thickness_px: 1 | 2 | 4;
}

export interface SpacerBlockData {
  height_px: 8 | 16 | 24 | 32 | 48 | 64;
}

export interface PricingTableBlockData {
  title: string | null;
  show_quantity_column: boolean;
  show_unit_column: boolean;
  show_subtotal_per_line: boolean;
  items: PricingLineItem[];
  discount: PricingDiscount | null;
  show_subtotal_row: boolean;
  show_tax_row: boolean;
  tax_label: string;
  tax_rate: number;
  show_total_row: boolean;
  currency: string;
  footer_note: string | null;
}

export interface PricingLineItem {
  id: string;
  description: string;
  unit: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
  is_optional: boolean;
  billing_type: 'one_time' | 'recurring';
  product_id: string | null;
}

export interface PricingDiscount {
  type: 'percentage' | 'fixed';
  value: number;
  label: string;
  amount: number;
}

export interface TimelineBlockData {
  title: string | null;
  layout: 'vertical' | 'horizontal';
  milestones: TimelineMilestone[];
}

export interface TimelineMilestone {
  id: string;
  phase_label: string;
  title: string;
  description: string | null;
  duration_label: string;
  deliverables: string[];
  color: string;
}

export interface TeamMemberBlockData {
  title: string | null;
  layout: 'grid' | 'list';
  members: TeamMember[];
}

export interface TeamMember {
  id: string;
  name: string;
  role: string;
  bio: string | null;
  avatar_url: string | null;
  linkedin_url: string | null;
}

export interface TestimonialBlockData {
  title: string | null;
  layout: 'single' | 'grid';
  items: Testimonial[];
}

export interface Testimonial {
  id: string;
  quote: string;
  author_name: string;
  author_role: string | null;
  company_name: string | null;
  avatar_url: string | null;
}

export interface TermsBlockData {
  title: string | null;
  content: string;
}

export interface SignatureBlockData {
  title: string | null;
  description: string | null;
  require_signature: boolean;
  require_date: boolean;
  require_name: boolean;
}

export interface CtaBlockData {
  heading: string;
  description: string | null;
  button_text: string;
  button_link: string;
  alignment: 'left' | 'center' | 'right';
}

export interface VideoEmbedBlockData {
  url: string;
  platform: 'youtube' | 'vimeo' | 'custom';
  title: string | null;
  caption: string | null;
}

export interface FileAttachmentBlockData {
  title: string | null;
  files: FileAttachment[];
}

export interface FileAttachment {
  id: string;
  name: string;
  url: string;
  size: number;
  type: string;
}
