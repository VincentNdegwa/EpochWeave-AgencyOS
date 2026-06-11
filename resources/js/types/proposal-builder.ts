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
  | 'file_attachment'
  | 'callout'
  | 'column';


export type PaddingSize = 'none' | 'sm' | 'md' | 'lg' | 'xl'
 
export interface BlockMeta {
  padding_top:    PaddingSize
  padding_bottom: PaddingSize
 
  background_color: string | null   // hex or null (transparent)
  border_top:    boolean            // thin divider line above the block
  border_bottom: boolean            // thin divider line below the block
 
  is_hidden: boolean                // hides block on canvas (greyed out in editor, gone in preview)
 
  notes: string | null              // agency-only note, never shown to client
}
 
export const defaultBlockMeta: BlockMeta = {
  padding_top:      'md',
  padding_bottom:   'md',
  background_color: '#FFFFFF',
  border_top:       false,
  border_bottom:    false,
  is_hidden:        false,
  notes:            null,
}

export interface BaseBlock {
  id: string;
  type: BlockType;
  sort_order: number;
  is_locked: boolean;
  data: BlockData;
  meta: BlockMeta;
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
  | FileAttachmentBlockData
  | CalloutBlockData
  | ColumnBlockData;

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
  full_width: boolean;
}

export interface ImageItem {
  id: string
  url: string
  alt_text: string | null
  caption: string | null
  link_url: string | null
}
 
export interface ImageBlockData {
  images: ImageItem[]
  columns: 1 | 2 | 3
  alignment: 'left' | 'center' | 'right' | 'full'   // applies only when columns = 1
  width_percent: number                               // applies only when columns = 1 and alignment !== 'full'
  aspect_ratio: 'auto' | 'square' | 'video' | 'portrait'
  rounded: boolean
  show_captions: boolean
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
  description: string;              // the name shown on the table row
  item_description: string | null;  // sub-description shown below the name
  unit: string;
  quantity: number;
  unit_price: number;
  subtotal: number;                 // computed: (qty × price - discount + tax)
 
  billing_type: 'one_time' | 'recurring';
  billing_frequency: 'none' | 'daily' | 'weekly' | 'monthly' | 'yearly';
  product_id: number | null;        // null = custom item
 
  is_optional: boolean;
 
  item_discount_type:  'none' | 'percentage' | 'fixed';
  item_discount_value: number;      // e.g. 10 for 10% or fixed amount
 
  item_tax_type:  'none' | 'percentage' | 'fixed';
  item_tax_value: number;
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

// ── CALLOUT BLOCK ────────────────────────────────────────────
// A styled container: icon + rich text + background + accent border
// Replaces the need for "tip box", "warning box", "quote block" etc.

export interface CalloutBlockData {
  content:          string           // HTML from Quill
  icon:             string | null    // Emoji or null
  background_color: string | null    // hex or null (transparent)
  accent_color:     string           // used for left border — default '#6366f1'
  border_left:      boolean          // show left accent bar — default true
}

// ── COLUMN BLOCK ─────────────────────────────────────────────
// Layout container. Holds other blocks in a configurable grid.
// This is the composability layer — no need for a Figma canvas.

export interface ColumnBlockData {
  columns:        1 | 2 | 3
  gap:            'sm' | 'md' | 'lg'
  vertical_align: 'start' | 'center' | 'end'
  column_widths:  number[] | null
  // null = equal widths
  // [60, 40] = custom ratio for 2-col (must sum to 100)
  // Only used when columns = 2
  children:       BaseBlock[][]
  // children[0] = blocks in column 1
  // children[1] = blocks in column 2
  // children[2] = blocks in column 3
  // Array length always === columns
}
