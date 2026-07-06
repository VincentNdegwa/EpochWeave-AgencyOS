<?php

namespace Database\Seeders;

use App\Models\ProposalTemplate;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessionalFreelanceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::find(1);

        if (! $workspace) {
            $this->command->error('Workspace with ID 1 not found');

            return;
        }

        ProposalTemplate::where('workspace_id', $workspace->id)
            ->where('name', 'Professional Freelance Services')
            ->delete();

        $template = ProposalTemplate::create([
            'workspace_id' => $workspace->id,
            'name' => 'Professional Freelance Services',
            'description' => 'A comprehensive template for freelance professionals including project overview, services, timeline, team, pricing, and terms.',
            'content' => $this->generateProfessionalTemplate(),
            'is_default' => false,
        ]);

        $this->command->info('Professional Freelance Template created successfully!');
    }

    private function generateProfessionalTemplate(): array
    {
        $blocks = [
            // 1. Cover Block - Professional introduction
            [
                'id' => (string) Str::uuid(),
                'type' => 'cover',
                'sort_order' => 0,
                'is_locked' => false,
                'data' => [
                    'heading' => 'Professional Web Development Services',
                    'subheading' => 'Custom solutions tailored to elevate your digital presence and drive business growth',
                    'background_type' => 'color',
                    'background_value' => '#1e293b',
                    'text_color' => '#ffffff',
                    'show_logo' => true,
                    'show_date' => true,
                    'show_proposal_number' => true,
                ],
                'meta' => [
                    'padding_top' => 'md',
                    'padding_bottom' => 'lg',
                    'background_color' => '#1e293b',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 2. Logo Block - Professional branding
            [
                'id' => (string) Str::uuid(),
                'type' => 'logo',
                'sort_order' => 1,
                'is_locked' => false,
                'data' => [
                    'layout' => 'both_side_by_side',
                    'workspace_logo_url' => null,
                    'client_logo_url' => null,
                    'height_px' => 60,
                    'alignment' => 'center',
                ],
                'meta' => [
                    'padding_top' => 'md',
                    'padding_bottom' => 'md',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => true,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 3. Rich Text Block - Executive Summary
            [
                'id' => (string) Str::uuid(),
                'type' => 'rich_text',
                'sort_order' => 2,
                'is_locked' => false,
                'data' => [
                    'title' => 'Executive Summary',
                    'content' => '<p>We are pleased to present this comprehensive proposal for your web development project. Our team of experienced developers and designers is committed to delivering a high-quality, scalable solution that meets your specific business requirements and exceeds your expectations.</p><p>This proposal outlines our approach, timeline, deliverables, and pricing structure. We have carefully analyzed your needs and designed a solution that will provide maximum value and return on investment.</p>',
                    'show_title' => true,
                    'title_color' => '#1e293b',
                    'content_color' => '#475569',
                    'full_width' => false,
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 4. Callout Block - Key Benefits
            [
                'id' => (string) Str::uuid(),
                'type' => 'callout',
                'sort_order' => 3,
                'is_locked' => false,
                'data' => [
                    'title' => 'Why Choose Us?',
                    'content' => '<p>5+ years of industry experience</p><p>100+ successful projects delivered</p><p>24/7 dedicated support</p><p>Agile development methodology</p><p>Post-launch maintenance included</p>',
                    'background_color' => '#f0f9ff',
                    'accent_color' => '#0ea5e9',
                    'border_left' => true,
                ],
                'meta' => [
                    'padding_top' => 'md',
                    'padding_bottom' => 'md',
                    'background_color' => '#f0f9ff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 5. Column Block - Services Overview (2 columns)
            [
                'id' => (string) Str::uuid(),
                'type' => 'column',
                'sort_order' => 4,
                'is_locked' => false,
                'data' => [
                    'columns' => 2,
                    'gap' => 'md',
                    'vertical_align' => 'start',
                    'column_widths' => [50, 50],
                    'children' => [
                        // Column 1: Web Development Services
                        [
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'rich_text',
                                'sort_order' => 0,
                                'is_locked' => false,
                                'data' => [
                                    'title' => 'Web Development Services',
                                    'content' => '<h4>Custom Web Applications</h4><p>Full-stack development using modern frameworks and technologies.</p><h4>E-commerce Solutions</h4><p>Scalable online stores with secure payment processing.</p><h4>API Development</h4><p>RESTful APIs and microservices architecture.</p>',
                                    'show_title' => true,
                                    'title_color' => '#1e293b',
                                    'content_color' => '#475569',
                                    'full_width' => false,
                                ],
                                'meta' => [
                                    'padding_top' => 'md',
                                    'padding_bottom' => 'md',
                                    'background_color' => '#ffffff',
                                    'border_top' => false,
                                    'border_bottom' => false,
                                    'is_hidden' => false,
                                    'notes' => null,
                                ],
                            ],
                        ],
                        // Column 2: Design & UX Services
                        [
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'rich_text',
                                'sort_order' => 0,
                                'is_locked' => false,
                                'data' => [
                                    'title' => 'Design & UX Services',
                                    'content' => '<h4>UI/UX Design</h4><p>User-centered design with modern aesthetics and usability.</p><h4>Responsive Design</h4><p>Mobile-first approach for all device compatibility.</p><h4>Brand Integration</h4><p>Cohesive visual identity aligned with your brand.</p>',
                                    'show_title' => true,
                                    'title_color' => '#1e293b',
                                    'content_color' => '#475569',
                                    'full_width' => false,
                                ],
                                'meta' => [
                                    'padding_top' => 'md',
                                    'padding_bottom' => 'md',
                                    'background_color' => '#ffffff',
                                    'border_top' => false,
                                    'border_bottom' => false,
                                    'is_hidden' => false,
                                    'notes' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 6. Timeline Block - Project Timeline
            [
                'id' => (string) Str::uuid(),
                'type' => 'timeline',
                'sort_order' => 5,
                'is_locked' => false,
                'data' => [
                    'title' => 'Project Timeline',
                    'layout' => 'vertical',
                    'milestones' => [
                        [
                            'id' => (string) Str::uuid(),
                            'phase_label' => 'Phase 1',
                            'title' => 'Discovery & Planning',
                            'description' => 'Requirements gathering, user research, and technical architecture planning.',
                            'duration_label' => '2 weeks',
                            'deliverables' => ['Project roadmap', 'Technical specifications', 'Design mockups'],
                            'color' => '#3b82f6',
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'phase_label' => 'Phase 2',
                            'title' => 'Design & Development',
                            'description' => 'UI/UX design implementation and core functionality development.',
                            'duration_label' => '6 weeks',
                            'deliverables' => ['Functional prototype', 'Core features', 'Database structure'],
                            'color' => '#10b981',
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'phase_label' => 'Phase 3',
                            'title' => 'Testing & Deployment',
                            'description' => 'Quality assurance, user testing, and production deployment.',
                            'duration_label' => '2 weeks',
                            'deliverables' => ['Tested application', 'Deployment documentation', 'User training'],
                            'color' => '#f59e0b',
                        ],
                    ],
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#f8fafc',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 7. Team Member Block - Our Team
            [
                'id' => (string) Str::uuid(),
                'type' => 'team_member',
                'sort_order' => 6,
                'is_locked' => false,
                'data' => [
                    'title' => 'Our Expert Team',
                    'layout' => 'grid',
                    'members' => [
                        [
                            'id' => (string) Str::uuid(),
                            'name' => 'Alex Johnson',
                            'role' => 'Lead Developer',
                            'bio' => '8+ years of experience in full-stack development with expertise in modern web technologies.',
                            'avatar_url' => null,
                            'linkedin_url' => null,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'name' => 'Sarah Chen',
                            'role' => 'UI/UX Designer',
                            'bio' => '6+ years creating intuitive and beautiful user experiences for web and mobile applications.',
                            'avatar_url' => null,
                            'linkedin_url' => null,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'name' => 'Michael Davis',
                            'role' => 'Project Manager',
                            'bio' => '10+ years managing complex projects with agile methodologies and client communication.',
                            'avatar_url' => null,
                            'linkedin_url' => null,
                        ],
                    ],
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 8. Testimonial Block - Client Testimonials
            [
                'id' => (string) Str::uuid(),
                'type' => 'testimonial',
                'sort_order' => 7,
                'is_locked' => false,
                'data' => [
                    'title' => 'What Our Clients Say',
                    'layout' => 'grid',
                    'items' => [
                        [
                            'id' => (string) Str::uuid(),
                            'quote' => 'Exceptional work! They delivered our project on time and exceeded our expectations. The team was professional, responsive, and truly understood our vision.',
                            'author_name' => 'Jennifer Martinez',
                            'author_role' => 'CEO',
                            'company_name' => 'TechStart Inc.',
                            'avatar_url' => null,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'quote' => 'The best development team we\'ve worked with. Their attention to detail and commitment to quality is unmatched. Highly recommended!',
                            'author_name' => 'Robert Thompson',
                            'author_role' => 'CTO',
                            'company_name' => 'Innovation Labs',
                            'avatar_url' => null,
                        ],
                    ],
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#f8fafc',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 9. Pricing Table Block - Investment
            [
                'id' => (string) Str::uuid(),
                'type' => 'pricing_table',
                'sort_order' => 8,
                'is_locked' => false,
                'data' => [
                    'title' => 'Investment Breakdown',
                    'show_quantity_column' => true,
                    'show_unit_column' => true,
                    'show_subtotal_per_line' => true,
                    'items' => [
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Discovery & Planning Phase',
                            'item_description' => 'Requirements analysis, user research, technical architecture',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 5000,
                            'subtotal' => 5000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'UI/UX Design',
                            'item_description' => 'Wireframes, mockups, responsive design',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 8000,
                            'subtotal' => 8000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Frontend Development',
                            'item_description' => 'React/Vue implementation, responsive design',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 12000,
                            'subtotal' => 12000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Backend Development',
                            'item_description' => 'API development, database design, authentication',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 10000,
                            'subtotal' => 10000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Testing & QA',
                            'item_description' => 'Unit testing, integration testing, user acceptance testing',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 4000,
                            'subtotal' => 4000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Deployment & Launch',
                            'item_description' => 'Production setup, monitoring, documentation',
                            'unit' => 'Phase',
                            'quantity' => 1,
                            'unit_price' => 3000,
                            'subtotal' => 3000,
                            'billing_type' => 'one_time',
                            'billing_frequency' => 'none',
                            'is_optional' => false,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'description' => 'Monthly Maintenance (Optional)',
                            'item_description' => 'Bug fixes, security updates, performance monitoring',
                            'unit' => 'Month',
                            'quantity' => 12,
                            'unit_price' => 500,
                            'subtotal' => 6000,
                            'billing_type' => 'recurring',
                            'billing_frequency' => 'monthly',
                            'is_optional' => true,
                            'product_id' => null,
                            'item_discount_type' => 'none',
                            'item_discount_value' => 0,
                            'item_tax_type' => 'none',
                            'item_tax_value' => 0,
                        ],
                    ],
                    'discount' => null,
                    'show_subtotal_row' => true,
                    'show_tax_row' => true,
                    'tax_label' => 'Tax',
                    'tax_rate' => 0,
                    'show_total_row' => true,
                    'currency' => 'USD',
                    'footer_note' => 'Payment schedule: 30% upfront, 40% mid-project, 30% on completion',
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 10. Spacer Block
            [
                'id' => (string) Str::uuid(),
                'type' => 'spacer',
                'sort_order' => 9,
                'is_locked' => false,
                'data' => [
                    'height_px' => 32,
                ],
                'meta' => [
                    'padding_top' => 'none',
                    'padding_bottom' => 'none',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 11. Terms Block - Terms & Conditions
            [
                'id' => (string) Str::uuid(),
                'type' => 'terms',
                'sort_order' => 10,
                'is_locked' => false,
                'data' => [
                    'title' => 'Terms & Conditions',
                    'content' => '<h3>Payment Terms</h3><p>30% deposit required to begin work. 40% payment due at project midpoint. Final 30% due upon completion and client approval.</p><h3>Project Timeline</h3><p>Estimated project duration: 10 weeks. Timeline may be adjusted based on client feedback and change requests.</p><h3>Scope & Changes</h3><p>This proposal includes all items listed in the pricing section. Additional features will be quoted separately.</p><h3>Intellectual Property</h3><p>Upon final payment, all intellectual property rights to the delivered work transfer to the client.</p><h3>Support & Maintenance</h3><p>30 days of post-launch support included. Extended support packages available upon request.</p>',
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#f8fafc',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 12. Signature Block
            [
                'id' => (string) Str::uuid(),
                'type' => 'signature',
                'sort_order' => 11,
                'is_locked' => false,
                'data' => [
                    'title' => 'Accept Proposal',
                    'description' => 'Please sign below to accept this proposal and authorize the project to begin.',
                    'require_signature' => true,
                    'require_date' => true,
                    'require_name' => true,
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],

            // 13. CTA Block - Next Steps
            [
                'id' => (string) Str::uuid(),
                'type' => 'cta',
                'sort_order' => 12,
                'is_locked' => false,
                'data' => [
                    'heading' => 'Ready to Get Started?',
                    'description' => 'Have questions or want to discuss customizations? We\'re here to help you succeed.',
                    'button_text' => 'Schedule a Call',
                    'button_link' => 'https://calendly.com/your-team/project-discussion',
                    'alignment' => 'center',
                ],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#1e293b',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],
        ];

        return $blocks;
    }
}
