<?php

namespace App\Enums;

enum BlockType: string
{
    case COVER = 'cover';
    case RICH_TEXT = 'rich_text';
    case IMAGE = 'image';
    case LOGO = 'logo';
    case DIVIDER = 'divider';
    case SPACER = 'spacer';
    case PRICING_TABLE = 'pricing_table';
    case TIMELINE = 'timeline';
    case TEAM_MEMBER = 'team_member';
    case TESTIMONIAL = 'testimonial';
    case TERMS = 'terms';
    case SIGNATURE = 'signature';
    case CTA = 'cta';
    case VIDEO_EMBED = 'video_embed';
    case FILE_ATTACHMENT = 'file_attachment';
    case CALLOUT = 'callout';
    case COLUMN = 'column';

    /**
     * Get all valid block type values for validation.
     */
    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    /**
     * Get a human-readable label for the block type.
     */
    public function label(): string
    {
        return match ($this) {
            self::COVER => 'Cover',
            self::RICH_TEXT => 'Rich Text',
            self::IMAGE => 'Image',
            self::LOGO => 'Logo',
            self::DIVIDER => 'Divider',
            self::SPACER => 'Spacer',
            self::PRICING_TABLE => 'Pricing Table',
            self::TIMELINE => 'Timeline',
            self::TEAM_MEMBER => 'Team Member',
            self::TESTIMONIAL => 'Testimonial',
            self::TERMS => 'Terms',
            self::SIGNATURE => 'Signature',
            self::CTA => 'Call to Action',
            self::VIDEO_EMBED => 'Video Embed',
            self::FILE_ATTACHMENT => 'File Attachment',
            self::CALLOUT => 'Callout',
            self::COLUMN => 'Column',
        };
    }
}
