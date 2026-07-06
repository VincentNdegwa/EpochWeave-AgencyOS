<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref, watch } from 'vue';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal, ProposalTemplate } from '@/types/models/proposal';

const props = defineProps<{
    template: ProposalTemplate;
}>();

const workspaceId = usePage().props.workspace?.id ?? 0;
const builderStore = useProposalBuilderStore();
const { proposal, templateSettings } = storeToRefs(builderStore);

const toProposal = (template: ProposalTemplate): Proposal => ({
    id: template.id,
    workspace_id: workspaceId,
    account_id: null,
    created_by: null,
    template_id: null,
    title: template.name,
    proposal_number: null,
    status: 'draft',
    valid_until: null,
    content: template.content || [],
    currency: 'USD',
    subtotal: 0,
    discount_total: 0,
    tax_rate: 0,
    tax_amount: 0,
    grand_total: 0,
    requires_deposit: false,
    deposit_type: null,
    deposit_value: null,
    deposit_amount: null,
    token: null,
    password_hash: null,
    signer_name: null,
    signer_email: null,
    signer_company: null,
    signature_data: null,
    signed_ip: null,
    signed_user_agent: null,
    deposit_invoice_id: null,
    project_id: null,
    sent_at: null,
    viewed_at: null,
    last_viewed_at: null,
    view_count: 0,
    decided_at: null,
    expired_at: null,
    decline_reason: null,
    created_at: template.created_at,
    updated_at: template.updated_at,
});

watch(
    () => props.template,
    (template) => {
        builderStore.hydrateProposal(toProposal(template), {
            mode: 'template',
            template: {
                description: template.description ?? null,
                thumbnailUrl: template.thumbnail_url ?? null,
            },
        });
    },
    { immediate: true, deep: true },
);

const isSaving = ref(false);

const handleSave = async () => {
    if (isSaving.value) {
        return;
    }

    isSaving.value = true;

    try {
        const templateData = {
            name: proposal.value.title,
            description:
                templateSettings.value.description ??
                props.template.description,
            thumbnail_url:
                templateSettings.value.thumbnailUrl ??
                props.template.thumbnail_url,
            content: proposal.value.content,
        };

        await router.put(
            proposalTemplates.update(props.template.id),
            templateData as any,
        );
    } catch (error) {
        console.error('Failed to save template:', error);
    } finally {
        isSaving.value = false;
    }
};

defineExpose({
    handleSave,
    isSaving,
});

setLayoutProps({
    title: 'Edit Template',
    description: 'Modify your reusable proposal template.',
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Templates', href: proposalTemplates.index() },
        {
            title: props.template.name,
            href: proposalTemplates.show(props.template.id),
        },
        { title: 'edit' },
    ],
});
</script>

<template>
    <Head :title="`Edit ${template.name}`" />
    <div class="-mx-4 -mb-4 h-[calc(100vh-64px)]">
        <ProposalBuilder
            mode="edit"
            :on-save="handleSave"
            :is-saving="isSaving"
        />
    </div>
</template>
