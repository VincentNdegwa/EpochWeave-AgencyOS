This is exactly the right architectural instinct. You want a **typed JSON block system** — the same pattern used by Notion, n8n, PandaDoc, and Gutenberg. The database stores one JSON column, the frontend deserializes it into fully typed Vue components, and each block type has a strict contract.

---

## The Core Concept

Every block in the proposal is a **node** in a JSON array. Every node shares a common base shape, then extends it with a `data` object that is unique to that block type. The frontend reads the `type` field and knows exactly which Vue component to mount and which TypeScript interface to enforce.

---

## The Base Block Interface

Every single block, regardless of type, must have these fields. This is the contract all blocks share:

```typescript
// types/proposal-builder.ts

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

export interface BaseBlock {
  id: string           // nanoid() — client-generated, stable across re-renders
  type: BlockType
  sort_order: number
  is_locked: boolean   // true after proposal is sent — prevents edits
  data: BlockData      // discriminated union — typed per block type below
  meta: BlockMeta      // shared display settings across all blocks
}

export interface BlockMeta {
  background_color: string | null   // hex or null (transparent)
  padding_top: 'none' | 'sm' | 'md' | 'lg'
  padding_bottom: 'none' | 'sm' | 'md' | 'lg'
  is_hidden: boolean                // allows toggling blocks without deleting
  notes: string | null              // internal agency note, never shown to client
}
```

---

## The Discriminated Union — Each Block's Data Shape

```typescript
// The master union — TypeScript narrows this based on block.type
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
```

Now each block type's data contract:

```typescript
// ─── COVER ───────────────────────────────────────────────────────────────────
export interface CoverBlockData {
  heading: string
  subheading: string | null
  background_type: 'color' | 'image'
  background_value: string        // hex if color, storage URL if image
  text_color: string              // hex
  show_logo: boolean
  show_date: boolean
  show_proposal_number: boolean
}

// ─── RICH TEXT ───────────────────────────────────────────────────────────────
export interface RichTextBlockData {
  title: string | null
  content: string                 // HTML string from Tiptap/ProseMirror editor
}

// ─── IMAGE ───────────────────────────────────────────────────────────────────
export interface ImageBlockData {
  url: string                     // Supabase Storage URL
  alt_text: string | null
  caption: string | null
  alignment: 'left' | 'center' | 'right' | 'full'
  width_percent: number           // 25 | 50 | 75 | 100
  link_url: string | null
}

// ─── LOGO ────────────────────────────────────────────────────────────────────
// Renders workspace logo, client logo, or both side by side
export interface LogoBlockData {
  layout: 'workspace_only' | 'client_only' | 'both_side_by_side'
  workspace_logo_url: string | null   // pulled from workspace settings
  client_logo_url: string | null      // pulled from account record
  height_px: number                   // 40 | 60 | 80
  alignment: 'left' | 'center' | 'right'
}

// ─── DIVIDER ─────────────────────────────────────────────────────────────────
export interface DividerBlockData {
  style: 'solid' | 'dashed' | 'dotted' | 'none'
  color: string                   // hex
  thickness_px: 1 | 2 | 4
}

// ─── SPACER ──────────────────────────────────────────────────────────────────
export interface SpacerBlockData {
  height_px: 8 | 16 | 24 | 32 | 48 | 64
}

// ─── PRICING TABLE ───────────────────────────────────────────────────────────
export interface PricingTableBlockData {
  title: string | null
  show_quantity_column: boolean
  show_unit_column: boolean
  show_subtotal_per_line: boolean
  items: PricingLineItem[]
  discount: PricingDiscount | null
  show_subtotal_row: boolean
  show_tax_row: boolean
  tax_label: string               // e.g. "VAT (16%)"
  tax_rate: number                // e.g. 16
  show_total_row: boolean
  currency: string                // KES | NGN | USD | GHS
  footer_note: string | null      // e.g. "Prices valid for 30 days"
}

export interface PricingLineItem {
  id: string
  description: string
  unit: string                    // hr | day | mo | pkg | item
  quantity: number
  unit_price: number              // in cents
  subtotal: number                // computed: quantity × unit_price
  is_optional: boolean            // client can toggle optional line items
  billing_type: 'one_time' | 'recurring'
  product_id: string | null       // reference to products catalog if picked
}

export interface PricingDiscount {
  type: 'percentage' | 'fixed'
  value: number
  label: string                   // e.g. "Early Payment Discount"
  amount: number                  // computed discount amount in cents
}

// ─── TIMELINE ────────────────────────────────────────────────────────────────
export interface TimelineBlockData {
  title: string | null
  layout: 'vertical' | 'horizontal'
  milestones: TimelineMilestone[]
}

export interface TimelineMilestone {
  id: string
  phase_label: string             // e.g. "Phase 1"
  title: string                   // e.g. "Discovery & Research"
  description: string | null
  duration_label: string          // e.g. "Week 1–2" or "3 days"
  deliverables: string[]          // list of strings
  color: string                   // hex — each phase gets a color
}

// ─── TEAM MEMBER ─────────────────────────────────────────────────────────────
export interface TeamMemberBlockData {
  title: string | null            // e.g. "Your Project Team"
  layout: 'grid' | 'list'
  members: TeamMember[]
}

export interface TeamMember {
  id: string
  name: string
  role: string                    // e.g. "Lead Developer"
  bio: string | null
  avatar_url: string | null
  linkedin_url: string | null
}

// ─── TESTIMONIAL ─────────────────────────────────────────────────────────────
export interface TestimonialBlockData {
  title: string | null
  layout: 'single' | 'grid'
  items: Testimonial[]
}

export interface Testimonial {
  id: string
  quote: string
  author_name: string
  author_role: string | null
  company_name: string | null
  avatar_url: string | null
  rating: 1 | 2 | 3 | 4 | 5 | null
}

// ─── TERMS ───────────────────────────────────────────────────────────────────
export interface TermsBlockData {
  title: string                   // e.g. "Terms & Conditions"
  content: string                 // HTML from rich text editor
  requires_explicit_acceptance: boolean
  // if true, client must tick a checkbox before signature pad activates
  acceptance_label: string        // e.g. "I have read and agree to the terms above"
}

// ─── SIGNATURE ───────────────────────────────────────────────────────────────
export interface SignatureBlockData {
  title: string                   // e.g. "Authorization & Sign-Off"
  instruction_text: string        // e.g. "Type your full name and sign below to accept"
  show_date_field: boolean
  show_company_field: boolean
  // Runtime state — null until client submits
  // These are written back to the proposals table, NOT stored inside the JSON block
  // The block itself just declares the UI config
}

// ─── CTA ─────────────────────────────────────────────────────────────────────
export interface CtaBlockData {
  heading: string                 // e.g. "Ready to get started?"
  subtext: string | null
  button_label: string            // e.g. "Accept This Proposal"
  button_action: 'scroll_to_signature' | 'external_url'
  button_url: string | null       // only if external_url
  alignment: 'left' | 'center' | 'right'
  button_color: string            // hex
  button_text_color: string       // hex
}

// ─── VIDEO EMBED ─────────────────────────────────────────────────────────────
export interface VideoEmbedBlockData {
  provider: 'youtube' | 'vimeo' | 'loom'
  video_id: string                // extracted from URL
  title: string | null
  caption: string | null
  autoplay: boolean
  show_controls: boolean
}

// ─── FILE ATTACHMENT ─────────────────────────────────────────────────────────
export interface FileAttachmentBlockData {
  title: string | null            // e.g. "Supporting Documents"
  files: AttachedFile[]
}

export interface AttachedFile {
  id: string
  file_name: string
  file_size_bytes: number
  mime_type: string
  storage_url: string
  description: string | null
}
```

---

## The Full Typed Proposal Document

The entire proposal canvas is one typed object:

```typescript
export interface ProposalDocument {
  version: '1.0'                  // schema version — critical for migrations
  blocks: BaseBlock[]
}
```

This is what gets serialized to JSON and saved in a single `content` column on the `proposals` table:

```sql
-- Add this column to the proposals table
content JSONB NOT NULL DEFAULT '{"version":"1.0","blocks":[]}'
```

Using `JSONB` (not `JSON`) means PostgreSQL indexes into it, and you can query specific block types if you ever need to — for example, finding all proposals that contain a `pricing_table` block.

---

## How the Vue Component Tree Works

The builder has one smart renderer that reads `block.type` and mounts the right component:

```typescript
// composables/useBlockRenderer.ts

import type { BaseBlock } from '@/types/proposal-builder'

// The component map — one entry per block type
const blockComponentMap: Record<BlockType, Component> = {
  cover:           () => import('@/components/blocks/CoverBlock.vue'),
  rich_text:       () => import('@/components/blocks/RichTextBlock.vue'),
  image:           () => import('@/components/blocks/ImageBlock.vue'),
  logo:            () => import('@/components/blocks/LogoBlock.vue'),
  divider:         () => import('@/components/blocks/DividerBlock.vue'),
  spacer:          () => import('@/components/blocks/SpacerBlock.vue'),
  pricing_table:   () => import('@/components/blocks/PricingTableBlock.vue'),
  timeline:        () => import('@/components/blocks/TimelineBlock.vue'),
  team_member:     () => import('@/components/blocks/TeamMemberBlock.vue'),
  testimonial:     () => import('@/components/blocks/TestimonialBlock.vue'),
  terms:           () => import('@/components/blocks/TermsBlock.vue'),
  signature:       () => import('@/components/blocks/SignatureBlock.vue'),
  cta:             () => import('@/components/blocks/CtaBlock.vue'),
  video_embed:     () => import('@/components/blocks/VideoEmbedBlock.vue'),
  file_attachment: () => import('@/components/blocks/FileAttachmentBlock.vue'),
}

export function useBlockRenderer() {
  function resolveComponent(block: BaseBlock): Component {
    return blockComponentMap[block.type]
  }
  return { resolveComponent }
}
```

Each block component receives exactly two props — the typed data and the locked state:

```typescript
// Example — PricingTableBlock.vue props contract
const props = defineProps<{
  data: PricingTableBlockData
  isLocked: boolean
}>()
```

TypeScript will error at compile time if any block component receives the wrong data shape. This is the entire value of the discriminated union — you can never accidentally pass `TimelineBlockData` into a `PricingTableBlock` component.

---

## The State Manager (Pinia Store)

The builder canvas state lives in a Pinia store. All mutations go through it — add block, remove block, reorder blocks, update a single block's data.

```typescript
// stores/proposalBuilder.ts

import { defineStore } from 'pinia'
import { nanoid } from 'nanoid'
import type { BaseBlock, BlockType, ProposalDocument } from '@/types/proposal-builder'

export const useProposalBuilderStore = defineStore('proposalBuilder', {
  state: () => ({
    document: null as ProposalDocument | null,
    isDirty: false,           // true when unsaved changes exist
    isSaving: false,
    selectedBlockId: null as string | null,
  }),

  getters: {
    blocks: (state) => state.document?.blocks ?? [],
    orderedBlocks: (state) =>
      [...(state.document?.blocks ?? [])].sort((a, b) => a.sort_order - b.sort_order),
    selectedBlock: (state) =>
      state.document?.blocks.find(b => b.id === state.selectedBlockId) ?? null,
  },

  actions: {
    loadDocument(doc: ProposalDocument) {
      this.document = doc
      this.isDirty = false
    },

    addBlock(type: BlockType, afterBlockId?: string) {
      if (!this.document) return
      const newBlock = createDefaultBlock(type)  // factory function below
      const insertAfterIndex = afterBlockId
        ? this.document.blocks.findIndex(b => b.id === afterBlockId)
        : this.document.blocks.length - 1

      this.document.blocks.splice(insertAfterIndex + 1, 0, newBlock)
      this.recomputeSortOrder()
      this.selectedBlockId = newBlock.id
      this.isDirty = true
    },

    removeBlock(blockId: string) {
      if (!this.document) return
      this.document.blocks = this.document.blocks.filter(b => b.id !== blockId)
      this.recomputeSortOrder()
      this.isDirty = true
    },

    updateBlockData<T extends BaseBlock>(blockId: string, data: Partial<T['data']>) {
      if (!this.document) return
      const block = this.document.blocks.find(b => b.id === blockId)
      if (!block || block.is_locked) return
      block.data = { ...block.data, ...data }
      this.isDirty = true
    },

    reorderBlocks(orderedIds: string[]) {
      if (!this.document) return
      orderedIds.forEach((id, index) => {
        const block = this.document!.blocks.find(b => b.id === id)
        if (block) block.sort_order = index
      })
      this.isDirty = true
    },

    recomputeSortOrder() {
      this.document?.blocks.forEach((block, index) => {
        block.sort_order = index
      })
    },

    lockAllBlocks() {
      this.document?.blocks.forEach(block => { block.is_locked = true })
    },

    async save(proposalId: string) {
      if (!this.document) return
      this.isSaving = true
      await api.patch(`/proposals/${proposalId}/content`, { content: this.document })
      this.isDirty = false
      this.isSaving = false
    },
  },
})
```

---

## The Default Block Factory

When a user drags a block type onto the canvas, this factory generates a valid default shape:

```typescript
// utils/blockFactory.ts

import { nanoid } from 'nanoid'
import type { BaseBlock, BlockType } from '@/types/proposal-builder'

const defaultMeta = (): BlockMeta => ({
  background_color: null,
  padding_top: 'md',
  padding_bottom: 'md',
  is_hidden: false,
  notes: null,
})

export function createDefaultBlock(type: BlockType): BaseBlock {
  const base = {
    id: nanoid(),
    type,
    sort_order: 0,
    is_locked: false,
    meta: defaultMeta(),
  }

  const dataDefaults: Record<BlockType, BlockData> = {
    cover: {
      heading: 'Proposal Title',
      subheading: null,
      background_type: 'color',
      background_value: '#0F172A',
      text_color: '#FFFFFF',
      show_logo: true,
      show_date: true,
      show_proposal_number: true,
    },
    rich_text: {
      title: null,
      content: '<p>Start writing...</p>',
    },
    pricing_table: {
      title: 'Investment',
      show_quantity_column: true,
      show_unit_column: true,
      show_subtotal_per_line: true,
      items: [],
      discount: null,
      show_subtotal_row: true,
      show_tax_row: false,
      tax_label: 'VAT (16%)',
      tax_rate: 16,
      show_total_row: true,
      currency: 'KES',
      footer_note: null,
    },
    timeline: {
      title: 'Project Timeline',
      layout: 'vertical',
      milestones: [],
    },
    signature: {
      title: 'Authorization & Sign-Off',
      instruction_text: 'Type your full name and sign below to accept this proposal.',
      show_date_field: true,
      show_company_field: false,
    },
    // ... remaining defaults for every block type
  }

  return { ...base, data: dataDefaults[type] }
}
```

---

## What Gets Saved to the Database

When the user saves, the entire `ProposalDocument` is serialized to one JSONB column. The `proposal_items` table is still written separately — it is a denormalized copy used for financial reporting and invoice generation. The JSON block is for rendering the canvas. The `proposal_items` rows are the source of truth for money.

The save operation does two things in one transaction:

```
1. UPDATE proposals SET content = $jsonDocument WHERE id = $proposalId
2. DELETE + INSERT proposal_items WHERE proposal_id = $proposalId
   (re-sync from the pricing_table blocks inside the JSON)
```

This dual-write keeps the JSONB block fast for rendering and the relational rows fast for querying revenue data.

---

## Why This Architecture Wins

Every block type has a strict TypeScript contract. The Vue component tree is driven entirely by the `type` field. Adding a new block type in the future means adding one interface, one entry to the component map, one entry to the factory — nothing else changes. The builder scales to 50 block types with zero architectural change. The JSON is the UI state, exactly like n8n.