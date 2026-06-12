<script setup lang="ts">
import { PlusIcon, Trash2Icon, UserIcon, QuoteIcon } from '@lucide/vue';
import { nanoid } from 'nanoid';
import BlockEmptyPlaceholder from '@/pages/proposals/components/blocks/shared/BlockEmptyPlaceholder.vue';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type {
    BaseBlock,
    TestimonialBlockData,
    Testimonial,
} from '@/types/proposal-builder';

const props = defineProps<{
    data: TestimonialBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<TestimonialBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const updateItem = (id: string, changes: Partial<Testimonial>) => {
    updateData({
        items: props.data.items.map((t) =>
            t.id === id ? { ...t, ...changes } : t,
        ),
    });
};

const addItem = () => {
    updateData({
        items: [
            ...props.data.items,
            {
                id: nanoid(8),
                quote: 'Working with this team was an absolute pleasure. They delivered beyond expectations.',
                author_name: 'Client Name',
                author_role: 'CEO',
                company_name: 'Company Name',
                avatar_url: null,
            },
        ],
    });
};

const removeItem = (id: string) => {
    updateData({ items: props.data.items.filter((t) => t.id !== id) });
};

const handleAvatarUpload = (id: string, event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = () =>
        updateItem(id, {
            avatar_url:
                typeof reader.result === 'string' ? reader.result : null,
        });
    reader.readAsDataURL(file);
};
</script>

<template>
    <section class="px-8 py-6">
        <BlockTitle
            v-if="data.title || !isLocked"
            :model-value="data.title"
            placeholder="What Our Clients Say"
            :is-locked="isLocked"
            class="mb-6"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <BlockEmptyPlaceholder
            v-if="data.items.length === 0 && !isLocked"
            message="No testimonials yet"
            action-label="Add testimonial"
            @action="addItem"
        />

        <!-- Single layout -->
        <template v-else-if="data.layout === 'single'">
            <div
                v-for="item in data.items"
                :key="item.id"
                class="group/item relative mx-auto max-w-2xl"
            >
                <QuoteIcon class="mb-4 h-8 w-8 text-muted-foreground/30" />

                <textarea
                    v-if="!isLocked"
                    :value="item.quote"
                    rows="3"
                    class="mb-6 w-full resize-none border-none bg-transparent text-lg leading-relaxed text-foreground italic outline-none"
                    @input="
                        updateItem(item.id, {
                            quote: ($event.target as HTMLTextAreaElement).value,
                        })
                    "
                />
                <p
                    v-else
                    class="mb-6 text-lg leading-relaxed text-foreground italic"
                >
                    "{{ item.quote }}"
                </p>

                <div class="flex items-center gap-3">
                    <label
                        :class="[
                            'h-10 w-10 flex-shrink-0 overflow-hidden rounded-full border border-border',
                            !isLocked ? 'cursor-pointer' : '',
                        ]"
                    >
                        <img
                            v-if="item.avatar_url"
                            :src="item.avatar_url"
                            :alt="item.author_name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-muted"
                        >
                            <UserIcon
                                class="h-4 w-4 text-muted-foreground/40"
                            />
                        </div>
                        <input
                            v-if="!isLocked"
                            type="file"
                            accept="image/*"
                            class="sr-only"
                            @change="(e) => handleAvatarUpload(item.id, e)"
                        />
                    </label>
                    <div>
                        <input
                            v-if="!isLocked"
                            type="text"
                            :value="item.author_name"
                            class="block w-full border-none bg-transparent text-sm font-semibold text-foreground outline-none"
                            @input="
                                updateItem(item.id, {
                                    author_name: (
                                        $event.target as HTMLInputElement
                                    ).value,
                                })
                            "
                        />
                        <p v-else class="text-sm font-semibold text-foreground">
                            {{ item.author_name }}
                        </p>

                        <div v-if="!isLocked" class="flex gap-1">
                            <input
                                type="text"
                                :value="item.author_role ?? ''"
                                placeholder="Role"
                                class="w-20 border-none bg-transparent text-xs text-muted-foreground outline-none"
                                @input="
                                    updateItem(item.id, {
                                        author_role:
                                            ($event.target as HTMLInputElement)
                                                .value || null,
                                    })
                                "
                            />
                            <span class="text-xs text-muted-foreground">·</span>
                            <input
                                type="text"
                                :value="item.company_name ?? ''"
                                placeholder="Company"
                                class="w-24 border-none bg-transparent text-xs text-muted-foreground outline-none"
                                @input="
                                    updateItem(item.id, {
                                        company_name:
                                            ($event.target as HTMLInputElement)
                                                .value || null,
                                    })
                                "
                            />
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            {{
                                [item.author_role, item.company_name]
                                    .filter(Boolean)
                                    .join(' · ')
                            }}
                        </p>
                    </div>
                    <button
                        v-if="!isLocked"
                        type="button"
                        class="ml-auto text-muted-foreground opacity-0 transition group-hover/item:opacity-100 hover:text-destructive"
                        @click="removeItem(item.id)"
                    >
                        <Trash2Icon class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <button
                v-if="!isLocked"
                type="button"
                class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border py-3 text-xs text-muted-foreground transition hover:border-primary hover:text-foreground"
                @click="addItem"
            >
                <PlusIcon class="h-3.5 w-3.5" /> Add testimonial
            </button>
        </template>

        <!-- Grid layout -->
        <template v-else>
            <div class="grid grid-cols-2 gap-4">
                <div
                    v-for="item in data.items"
                    :key="item.id"
                    class="group/item relative rounded-lg border border-border p-5"
                >
                    <QuoteIcon class="mb-3 h-5 w-5 text-muted-foreground/30" />

                    <textarea
                        v-if="!isLocked"
                        :value="item.quote"
                        rows="3"
                        class="mb-4 w-full resize-none border-none bg-transparent text-sm leading-relaxed text-foreground italic outline-none"
                        @input="
                            updateItem(item.id, {
                                quote: ($event.target as HTMLTextAreaElement)
                                    .value,
                            })
                        "
                    />
                    <p
                        v-else
                        class="mb-4 text-sm leading-relaxed text-foreground italic"
                    >
                        "{{ item.quote }}"
                    </p>

                    <div class="flex items-center gap-2.5">
                        <label
                            :class="[
                                'h-8 w-8 flex-shrink-0 overflow-hidden rounded-full border border-border',
                                !isLocked ? 'cursor-pointer' : '',
                            ]"
                        >
                            <img
                                v-if="item.avatar_url"
                                :src="item.avatar_url"
                                :alt="item.author_name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-muted"
                            >
                                <UserIcon
                                    class="h-3.5 w-3.5 text-muted-foreground/40"
                                />
                            </div>
                            <input
                                v-if="!isLocked"
                                type="file"
                                accept="image/*"
                                class="sr-only"
                                @change="(e) => handleAvatarUpload(item.id, e)"
                            />
                        </label>
                        <div class="min-w-0">
                            <input
                                v-if="!isLocked"
                                type="text"
                                :value="item.author_name"
                                class="block w-full border-none bg-transparent text-xs font-semibold text-foreground outline-none"
                                @input="
                                    updateItem(item.id, {
                                        author_name: (
                                            $event.target as HTMLInputElement
                                        ).value,
                                    })
                                "
                            />
                            <p
                                v-else
                                class="truncate text-xs font-semibold text-foreground"
                            >
                                {{ item.author_name }}
                            </p>
                            <p
                                class="truncate text-[11px] text-muted-foreground"
                            >
                                {{
                                    [item.author_role, item.company_name]
                                        .filter(Boolean)
                                        .join(' · ')
                                }}
                            </p>
                        </div>
                    </div>

                    <button
                        v-if="!isLocked"
                        type="button"
                        class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded text-muted-foreground opacity-0 transition group-hover/item:opacity-100 hover:text-destructive"
                        @click="removeItem(item.id)"
                    >
                        <Trash2Icon class="h-3 w-3" />
                    </button>
                </div>

                <!-- Add slot -->
                <button
                    v-if="!isLocked"
                    type="button"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border py-8 text-xs text-muted-foreground transition hover:border-primary hover:text-foreground"
                    @click="addItem"
                >
                    <PlusIcon class="h-4 w-4" />
                    Add testimonial
                </button>
            </div>
        </template>
    </section>
</template>
