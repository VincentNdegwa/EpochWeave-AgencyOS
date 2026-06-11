import { nanoid } from 'nanoid';
import type { BaseBlock, BlockType } from '@/types/proposal-builder';
import { defaultBlockMeta } from '@/types/proposal-builder';

export function createDefaultBlock(type: BlockType): BaseBlock {
    const defaults: Record<BlockType, any> = {
        cover: {
            heading: 'Proposal Title',
            subheading: 'Subtitle here',
            background_type: 'color',
            background_value: '#ffffff',
            text_color: '#000000',
            show_logo: true,
            show_date: true,
            show_proposal_number: true,
        },
        rich_text: {
            title: 'Project overview',
            content: `
                <p>Thanks for considering us for this engagement. Below is a quick summary of what to expect.</p>
                <h3>What you can expect</h3>
                <ol>
                    <li>Clear milestones with transparent communication.</li>
                    <li>Dedicated point of contact for day-to-day updates.</li>
                    <li>Actionable insights delivered at the end of each phase.</li>
                </ol>
                <p>Review the details in the following sections and let us know if any adjustments are needed.</p>
            `.trim(),
            show_title: true,
            title_color: '#000000',
            content_color: '#000000',
            full_width: true,
        },
        image: {
            images: [],
            columns: 1,
            alignment: 'center',
            width_percent: 100,
            aspect_ratio: 'auto',
            rounded: true,
            show_captions: true,
        },
        logo: {
            layout: 'workspace_only',
            workspace_logo_url: null,
            client_logo_url: null,
            height_px: 60,
            alignment: 'center',
        },
        divider: {
            style: 'solid',
            color: '#e5e7eb',
            thickness_px: 1,
        },
        spacer: {
            height_px: 32,
        },
        pricing_table: {
            title: null,
            show_quantity_column: true,
            show_unit_column: true,
            show_subtotal_per_line: false,
            items: [],
            discount: null,
            show_subtotal_row: true,
            show_tax_row: false,
            tax_label: 'Tax',
            tax_rate: 0,
            show_total_row: true,
            currency: 'USD',
            footer_note: null,
        },
        timeline: {
            title: null,
            layout: 'vertical',
            milestones: [],
        },
        team_member: {
            title: 'Our Team',
            layout: 'grid',
            members: [],
        },
        testimonial: {
            title: 'What Our Clients Say',
            layout: 'grid',
            items: [],
        },
        terms: {
            title: 'Terms & Conditions',
            content: '<p>Add your terms here...</p>',
        },
        signature: {
            title: 'Signature',
            description: 'Please sign below to accept this proposal',
            require_signature: true,
            require_date: true,
            require_name: true,
        },
        cta: {
            heading: 'Ready to Get Started?',
            description: 'Contact us to begin your project',
            button_text: 'Contact Us',
            button_link: '#',
            alignment: 'center',
        },
        video_embed: {
            url: '',
            platform: 'youtube',
            title: null,
            caption: null,
        },
        file_attachment: {
            title: 'Attachments',
            files: [],
        },
        callout: {
            content: '<p>Add your callout content here.</p>',
            icon: 'ℹ️',
            background_color: '#EFF6FF',
            accent_color: '#3B82F6',
            border_left: true,
        },
        column: {
            columns: 2,
            gap: 'md',
            vertical_align: 'start',
            column_widths: null,
            children: [[], []],
        },
    };

    return {
        id: nanoid(),
        type,
        sort_order: 0,
        is_locked: false,
        data: defaults[type],
        meta: {
            ...defaultBlockMeta,
        },
    };
}
