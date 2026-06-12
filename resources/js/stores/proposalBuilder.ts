import { nanoid } from 'nanoid';
import { defineStore } from 'pinia';
import { computed, nextTick, ref, watch } from 'vue';
import { createDefaultBlock } from '@/composables/blockFactory';
import type { Proposal, ProposalItem } from '@/types/models/proposal';
import type {
    BaseBlock,
    BlockType,
    PricingLineItem,
    PricingTableBlockData,
} from '@/types/proposal-builder';

type BuilderMode = 'proposal' | 'template';
type SidebarTab = 'block' | 'proposal';

interface TemplateSettings {
    description: string | null;
    thumbnailUrl: string | null;
}

const cloneProposal = (payload: Proposal): Proposal =>
    JSON.parse(JSON.stringify(payload)) as Proposal;

const createDefaultProposal = (workspaceId = 0): Proposal => {
    const timestamp = new Date().toISOString();

    return {
        id: 0,
        workspace_id: workspaceId,
        account_id: null,
        created_by: null,
        template_id: null,
        title: 'Untitled proposal',
        proposal_number: null,
        status: 'draft',
        proposal_status_id: null,
        valid_until: null,
        content: [],
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
        created_at: timestamp,
        updated_at: timestamp,
    };
};

export const useProposalBuilderStore = defineStore('proposalBuilder', () => {
    // State
    const isDirty = ref(false);
    const isSaving = ref(false);
    const selectedBlockId = ref<string | null>(null);
    const sidebarTab = ref<SidebarTab>('proposal');
    const lastSavedAt = ref<Date | null>(null);
    const builderMode = ref<BuilderMode>('proposal');
    const hydrating = ref(false);
    const proposal = ref<Proposal>(createDefaultProposal());
    const templateSettings = ref<TemplateSettings>({
        description: null,
        thumbnailUrl: null,
    });
    const lineItemsCatalog = ref<PricingLineItem[]>([]);

    // Helper function to find block recursively including nested blocks
    function findBlockRecursive(
        blocks: BaseBlock[],
        blockId: string,
    ): BaseBlock | null {
        for (const block of blocks) {
            if (block.id === blockId) {
                return block;
            }

            if (
                block.type === 'column' &&
                'children' in block.data &&
                block.data.children
            ) {
                for (const column of block.data.children) {
                    const found = findBlockRecursive(column, blockId);

                    if (found) {
                        return found;
                    }
                }
            }
        }

        return null;
    }

    // Computed
    const orderedBlocks = computed(() =>
        [...proposal.value.content].sort((a, b) => a.sort_order - b.sort_order),
    );
    const selectedBlock = computed(() =>
        findBlockRecursive(proposal.value.content, selectedBlockId.value ?? ''),
    );
    const blocks = computed(() => proposal.value.content);

    // Watchers
    watch(
        proposal,
        () => {
            if (!hydrating.value) {
                isDirty.value = true;
            }
        },
        { deep: true },
    );

    function loadBlocks(newBlocks: BaseBlock[]) {
        proposal.value.content = [...newBlocks];
        isDirty.value = false;
    }

    function addBlock(type: BlockType, afterBlockId?: string) {
        const newBlock = createDefaultBlock(type);
        const insertAfterIndex = afterBlockId
            ? proposal.value.content.findIndex((b) => b.id === afterBlockId)
            : proposal.value.content.length - 1;

        proposal.value.content.splice(insertAfterIndex + 1, 0, newBlock);
        reindexBlocks();
        selectedBlockId.value = newBlock.id;
        sidebarTab.value = 'block';
        isDirty.value = true;
    }

    function updateBlock(blockId: string, updates: Partial<BaseBlock>) {
        const index = proposal.value.content.findIndex((b) => b.id === blockId);

        if (index !== -1) {
            proposal.value.content[index] = {
                ...proposal.value.content[index],
                ...updates,
            };
            isDirty.value = true;
        }
    }

    function updateBlockData(blockId: string, data: BaseBlock['data']) {
        updateBlock(blockId, { data });
    }

    function updateNestedBlockData(
        blocks: BaseBlock[],
        blockId: string,
        data: BaseBlock['data'],
    ): boolean {
        for (let i = 0; i < blocks.length; i++) {
            const block = blocks[i];

            if (block.id === blockId) {
                blocks[i] = { ...block, data };

                return true;
            }

            if (
                block.type === 'column' &&
                'children' in block.data &&
                block.data.children
            ) {
                for (const column of block.data.children) {
                    if (updateNestedBlockData(column, blockId, data)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function updateBlockDataRecursive(
        blockId: string,
        data: BaseBlock['data'],
    ) {
        if (updateNestedBlockData(proposal.value.content, blockId, data)) {
            isDirty.value = true;
        }
    }

    function updateBlockMeta(blockId: string, meta: BaseBlock['meta']) {
        updateBlock(blockId, { meta });
    }

    function updateNestedBlockMeta(
        blocks: BaseBlock[],
        blockId: string,
        meta: BaseBlock['meta'],
    ): boolean {
        for (let i = 0; i < blocks.length; i++) {
            const block = blocks[i];

            if (block.id === blockId) {
                blocks[i] = { ...block, meta };

                return true;
            }

            if (
                block.type === 'column' &&
                'children' in block.data &&
                block.data.children
            ) {
                for (const column of block.data.children) {
                    if (updateNestedBlockMeta(column, blockId, meta)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function updateBlockMetaRecursive(
        blockId: string,
        meta: BaseBlock['meta'],
    ) {
        if (updateNestedBlockMeta(proposal.value.content, blockId, meta)) {
            isDirty.value = true;
        }
    }

    function deleteBlock(blockId: string) {
        proposal.value.content = proposal.value.content.filter(
            (b) => b.id !== blockId,
        );
        reindexBlocks();
        isDirty.value = true;
    }

    function reorderBlocks(blockIds: string[]) {
        const blockMap = new Map(proposal.value.content.map((b) => [b.id, b]));
        const reorderedBlocks: BaseBlock[] = [];

        for (const [index, blockId] of blockIds.entries()) {
            const block = blockMap.get(blockId);

            if (block) {
                reorderedBlocks.push({ ...block, sort_order: index });
            }
        }

        proposal.value.content = reorderedBlocks;
        isDirty.value = true;
    }

    function duplicateBlock(blockId: string) {
        const block = proposal.value.content.find((b) => b.id === blockId);

        if (block) {
            const newBlock = {
                ...JSON.parse(JSON.stringify(block)),
                id: nanoid(),
                sort_order: block.sort_order + 1,
            };
            proposal.value.content.splice(block.sort_order + 1, 0, newBlock);
            reindexBlocks();
            isDirty.value = true;
        }
    }

    function setSidebarTab(tab: 'block' | 'proposal') {
        sidebarTab.value = tab;
    }

    function selectBlock(blockId: string | null) {
        selectedBlockId.value = blockId;
    }

    function markAsClean() {
        isDirty.value = false;
        lastSavedAt.value = new Date();
    }

    function resetProposal(workspaceId = 0, overrides: Partial<Proposal> = {}) {
        hydrating.value = true;
        proposal.value = cloneProposal({
            ...createDefaultProposal(workspaceId),
            ...overrides,
        });
        nextTick(() => {
            hydrating.value = false;
            markAsClean();
        });
    }

    function hydrateProposal(
        payload: Proposal,
        options?: { mode?: BuilderMode; template?: TemplateSettings },
    ) {
        hydrating.value = true;
        proposal.value = cloneProposal(payload);

        // Populate line items catalog from backend proposal items
        if (payload.items && Array.isArray(payload.items)) {
            const catalogItems = payload.items.map((item: ProposalItem) => ({
                id: item.id.toString(),
                description: item.item_name || '',
                item_description: item.description || null,
                unit: item.unit_label || 'Pcs',
                quantity: parseFloat(item.quantity) || 1,
                unit_price: parseFloat(item.unit_price) || 0,
                subtotal: parseFloat(item.subtotal) || 0,
                billing_type: item.billing_type || 'one_time',
                billing_frequency: item.billing_frequency || 'none',
                is_optional: Boolean(item.is_optional),
                product_id: item.product_id || null,
                item_discount_type:
                    (item.discount_type as 'percentage' | 'fixed' | 'none') ||
                    'none',
                item_discount_value: parseFloat(item.discount_value) || 0,
                item_tax_type: 'none' as const, // Backend doesn't seem to have item tax
                item_tax_value: 0,
            }));
            setLineItemsCatalog(catalogItems);

            // Update block item IDs to match catalog item IDs
            updateBlockItemIdsToMatchCatalog(catalogItems);
        }

        if (options?.mode) {
            builderMode.value = options.mode;
        }

        if (options?.template) {
            templateSettings.value = { ...options.template };
        }

        nextTick(() => {
            hydrating.value = false;
            markAsClean();
        });
    }

    function clear() {
        resetProposal();
        selectedBlockId.value = null;
        sidebarTab.value = 'proposal';
        builderMode.value = 'proposal';
        templateSettings.value = { description: null, thumbnailUrl: null };
    }

    function reindexBlocks() {
        proposal.value.content = proposal.value.content.map((block, index) => ({
            ...block,
            sort_order: index,
        }));
    }

    function moveBlock(blockId: string, direction: 'up' | 'down') {
        const currentIndex = proposal.value.content.findIndex(
            (block) => block.id === blockId,
        );

        if (currentIndex === -1) {
            return;
        }

        const nextIndex =
            direction === 'up' ? currentIndex - 1 : currentIndex + 1;

        if (nextIndex < 0 || nextIndex >= proposal.value.content.length) {
            return;
        }

        const updated = [...proposal.value.content];
        const [removed] = updated.splice(currentIndex, 1);
        updated.splice(nextIndex, 0, removed);
        proposal.value.content = updated.map((block, index) => ({
            ...block,
            sort_order: index,
        }));
        isDirty.value = true;
    }

    // ── Line Items Catalog Management ──────────────────────────────────

    function addLineItem(item: PricingLineItem): void {
        const existingIndex = lineItemsCatalog.value.findIndex(
            (i) => i.id === item.id,
        );

        if (existingIndex >= 0) {
            lineItemsCatalog.value[existingIndex] = item;
        } else {
            lineItemsCatalog.value.push(item);
        }

        isDirty.value = true;
    }

    function updateLineItem(
        itemId: string,
        updates: Partial<PricingLineItem>,
    ): void {
        const index = lineItemsCatalog.value.findIndex(
            (item) => item.id === itemId,
        );

        if (index >= 0) {
            lineItemsCatalog.value[index] = {
                ...lineItemsCatalog.value[index],
                ...updates,
            };
            isDirty.value = true;
        }
    }

    function removeLineItem(itemId: string): void {
        const index = lineItemsCatalog.value.findIndex(
            (item) => item.id === itemId,
        );

        if (index >= 0) {
            lineItemsCatalog.value.splice(index, 1);
            isDirty.value = true;
        }
    }

    function getLineItem(itemId: string): PricingLineItem | null {
        return (
            lineItemsCatalog.value.find((item) => item.id === itemId) ?? null
        );
    }

    function getAllLineItems(): PricingLineItem[] {
        return lineItemsCatalog.value;
    }

    function setLineItemsCatalog(items: PricingLineItem[]): void {
        lineItemsCatalog.value = items;
        isDirty.value = true;
    }

    function getPricingTableBlockItemIds(): string[] {
        const itemIds: string[] = [];

        function extractFromBlocks(blocks: BaseBlock[]) {
            for (const block of blocks) {
                if (block.type === 'pricing_table' && 'items' in block.data) {
                    const pricingItems = (block.data as PricingTableBlockData)
                        .items;
                    itemIds.push(...pricingItems.map((item) => item.id));
                }

                if (
                    block.type === 'column' &&
                    'children' in block.data &&
                    block.data.children
                ) {
                    for (const column of block.data.children) {
                        extractFromBlocks(column);
                    }
                }
            }
        }

        extractFromBlocks(proposal.value.content);

        return itemIds;
    }

    function updateBlockItemIdsToMatchCatalog(catalogItems: PricingLineItem[]) {
        function updateBlocks(blocks: BaseBlock[]) {
            for (const block of blocks) {
                if (block.type === 'pricing_table' && 'items' in block.data) {
                    const blockData = block.data as PricingTableBlockData;
                    const pricingItems = blockData.items;

                    if (!pricingItems || pricingItems.length === 0) {
                        block.data.items = catalogItems.map((item) => ({
                            id: item.id,
                            description: item.description,
                            item_description: item.item_description,
                            unit: item.unit,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            subtotal: item.subtotal,
                            billing_type: item.billing_type,
                            billing_frequency: item.billing_frequency,
                            is_optional: item.is_optional,
                            product_id: item.product_id,
                            item_discount_type: item.item_discount_type,
                            item_discount_value: item.item_discount_value,
                            item_tax_type: item.item_tax_type,
                            item_tax_value: item.item_tax_value,
                        }));
                        continue;
                    }

                    const catalogMap = new Map<string, PricingLineItem>();
                    catalogItems.forEach((item) =>
                        catalogMap.set(item.description, item),
                    );

                    const updatedItems = pricingItems.map((blockItem) => {
                        const matchingCatalogItem = catalogMap.get(
                            blockItem.description,
                        );

                        if (matchingCatalogItem) {
                            return { ...blockItem, id: matchingCatalogItem.id };
                        }

                        const existingMatch = catalogItems.find(
                            (catalogItem) => catalogItem.id === blockItem.id,
                        );

                        if (existingMatch) {
                            return { ...blockItem, id: existingMatch.id };
                        }

                        const availableCatalogItem = catalogItems.find(
                            (catalogItem) =>
                                !pricingItems.some(
                                    (pItem) => pItem.id === catalogItem.id,
                                ),
                        );

                        if (availableCatalogItem) {
                            return {
                                ...blockItem,
                                id: availableCatalogItem.id,
                            };
                        }

                        return blockItem;
                    });

                    const uniqueItems = updatedItems.filter(
                        (item, index, self) =>
                            index === self.findIndex((t) => t.id === item.id),
                    );

                    if (uniqueItems.length < catalogItems.length) {
                        const missingCatalogItems = catalogItems.filter(
                            (catalogItem) =>
                                !uniqueItems.some(
                                    (item) => item.id === catalogItem.id,
                                ),
                        );

                        uniqueItems.push(...missingCatalogItems);
                    }

                    block.data.items = uniqueItems;
                }

                if (
                    block.type === 'column' &&
                    'children' in block.data &&
                    block.data.children
                ) {
                    for (const column of block.data.children) {
                        updateBlocks(column);
                    }
                }
            }
        }

        updateBlocks(proposal.value.content);
    }

    function getPricingTableItems(blockId: string): PricingLineItem[] {
        const block = findBlockRecursive(proposal.value.content, blockId);

        if (
            !block ||
            block.type !== 'pricing_table' ||
            !('items' in block.data)
        ) {
            return [];
        }

        const blockData = block.data as PricingTableBlockData;
        const itemIds = blockData.items.map((item) => item.id);

        return lineItemsCatalog.value.filter((item) =>
            itemIds.includes(item.id),
        );
    }

    return {
        proposal,
        blocks,
        isDirty,
        isSaving,
        selectedBlockId,
        sidebarTab,
        lastSavedAt,
        builderMode,
        templateSettings,
        orderedBlocks,
        selectedBlock,
        loadBlocks,
        addBlock,
        updateBlock,
        updateBlockData,
        updateBlockDataRecursive,
        updateBlockMeta,
        updateBlockMetaRecursive,
        deleteBlock,
        reorderBlocks,
        duplicateBlock,
        selectBlock,
        setSidebarTab,
        markAsClean,
        resetProposal,
        hydrateProposal,
        clear,
        reindexBlocks,
        moveBlock,
        setLastSavedAt: (date: Date | null) => {
            lastSavedAt.value = date;
        },
        // Line items catalog methods
        lineItemsCatalog,
        addLineItem,
        updateLineItem,
        removeLineItem,
        getLineItem,
        getAllLineItems,
        setLineItemsCatalog,
        getPricingTableBlockItemIds,
        getPricingTableItems,
    };
});
