<script setup lang="ts">
import { computed } from 'vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, CtaBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: CtaBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<CtaBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const alignClass = computed(
    () =>
        ({
            left: 'items-start text-left',
            center: 'items-center text-center',
            right: 'items-end text-right',
        })[props.data.alignment] ?? 'items-center text-center',
);
</script>

<template>
    <section class="px-8 py-10">
        <div class="flex flex-col gap-4" :class="alignClass">
            <!-- Heading -->
            <input
                v-if="!isLocked"
                type="text"
                :value="data.heading"
                class="w-full border-none bg-transparent text-2xl font-bold text-foreground outline-none placeholder:text-muted-foreground/50"
                :class="
                    data.alignment === 'center'
                        ? 'text-center'
                        : data.alignment === 'right'
                          ? 'text-right'
                          : 'text-left'
                "
                placeholder="Ready to Get Started?"
                @input="
                    updateData({
                        heading: ($event.target as HTMLInputElement).value,
                    })
                "
            />
            <h2 v-else class="text-2xl font-bold text-foreground">
                {{ data.heading }}
            </h2>

            <!-- Description -->
            <textarea
                v-if="!isLocked"
                :value="data.description ?? ''"
                rows="2"
                placeholder="Add a short description…"
                class="w-full resize-none border-none bg-transparent text-sm text-muted-foreground outline-none placeholder:text-muted-foreground/50"
                :class="
                    data.alignment === 'center'
                        ? 'text-center'
                        : data.alignment === 'right'
                          ? 'text-right'
                          : 'text-left'
                "
                @input="
                    updateData({
                        description:
                            ($event.target as HTMLTextAreaElement).value ||
                            null,
                    })
                "
            />
            <p
                v-else-if="data.description"
                class="text-sm text-muted-foreground"
            >
                {{ data.description }}
            </p>

            <!-- Button -->
            <div>
                <a
                    v-if="isLocked"
                    :href="data.button_link || '#'"
                    class="inline-flex items-center rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                >
                    {{ data.button_text }}
                </a>
                <div
                    v-else
                    class="inline-flex items-center rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground"
                >
                    <input
                        type="text"
                        :value="data.button_text"
                        class="max-w-fit border-none bg-transparent text-center text-sm font-semibold text-primary-foreground outline-none placeholder:text-primary-foreground/70"
                        placeholder="Button text"
                        @input="
                            updateData({
                                button_text: ($event.target as HTMLInputElement)
                                    .value,
                            })
                        "
                    />
                </div>
            </div>
        </div>
    </section>
</template>
