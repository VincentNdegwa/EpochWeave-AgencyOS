<script setup lang="ts">
import { PlusIcon, Trash2Icon, UserIcon, Link } from '@lucide/vue';
import { nanoid } from 'nanoid';
import BlockEmptyPlaceholder from '@/pages/proposals/components/blocks/shared/BlockEmptyPlaceholder.vue';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type {
    BaseBlock,
    TeamMemberBlockData,
    TeamMember,
} from '@/types/proposal-builder';

const props = defineProps<{
    data: TeamMemberBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<TeamMemberBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const updateMember = (id: string, changes: Partial<TeamMember>) => {
    updateData({
        members: props.data.members.map((m) =>
            m.id === id ? { ...m, ...changes } : m,
        ),
    });
};

const addMember = () => {
    updateData({
        members: [
            ...props.data.members,
            {
                id: nanoid(8),
                name: 'Team Member',
                role: 'Role',
                bio: null,
                avatar_url: null,
                linkedin_url: null,
            },
        ],
    });
};

const removeMember = (id: string) => {
    updateData({ members: props.data.members.filter((m) => m.id !== id) });
};

const handleAvatarUpload = (id: string, event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = () =>
        updateMember(id, {
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
            placeholder="Our Team"
            :is-locked="isLocked"
            class="mb-6"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <BlockEmptyPlaceholder
            v-if="data.members.length === 0 && !isLocked"
            message="No team members yet"
            action-label="Add member"
            @action="addMember"
        />

        <!-- Grid layout -->
        <div
            v-else-if="data.layout === 'grid'"
            class="grid grid-cols-2 gap-6 sm:grid-cols-3"
        >
            <div
                v-for="member in data.members"
                :key="member.id"
                class="group/member relative"
            >
                <!-- Avatar -->
                <label
                    :class="[
                        'relative mx-auto mb-3 block h-20 w-20 overflow-hidden rounded-full border-2 border-border',
                        !isLocked ? 'cursor-pointer' : '',
                    ]"
                >
                    <img
                        v-if="member.avatar_url"
                        :src="member.avatar_url"
                        :alt="member.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-muted"
                    >
                        <UserIcon class="h-8 w-8 text-muted-foreground/40" />
                    </div>
                    <input
                        v-if="!isLocked"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="(e) => handleAvatarUpload(member.id, e)"
                    />
                </label>

                <!-- Info -->
                <div class="text-center">
                    <input
                        v-if="!isLocked"
                        type="text"
                        :value="member.name"
                        class="w-full border-none bg-transparent text-center text-sm font-semibold text-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                name: ($event.target as HTMLInputElement).value,
                            })
                        "
                    />
                    <p v-else class="text-sm font-semibold text-foreground">
                        {{ member.name }}
                    </p>

                    <input
                        v-if="!isLocked"
                        type="text"
                        :value="member.role"
                        class="w-full border-none bg-transparent text-center text-xs text-muted-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                role: ($event.target as HTMLInputElement).value,
                            })
                        "
                    />
                    <p v-else class="text-xs text-muted-foreground">
                        {{ member.role }}
                    </p>

                    <textarea
                        v-if="!isLocked"
                        :value="member.bio ?? ''"
                        rows="2"
                        placeholder="Short bio…"
                        class="mt-1 w-full resize-none border-none bg-transparent text-center text-xs text-muted-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                bio:
                                    ($event.target as HTMLTextAreaElement)
                                        .value || null,
                            })
                        "
                    />
                    <p
                        v-else-if="member.bio"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        {{ member.bio }}
                    </p>

                    <a
                        v-if="member.linkedin_url && isLocked"
                        :href="member.linkedin_url"
                        target="_blank"
                        rel="noopener"
                        class="mt-1.5 inline-flex items-center gap-1 text-[11px] text-primary"
                    >
                        <Link class="h-3 w-3" /> LinkedIn
                    </a>
                </div>

                <!-- Delete -->
                <button
                    v-if="!isLocked"
                    type="button"
                    class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full border border-border bg-background text-muted-foreground opacity-0 shadow transition group-hover/member:opacity-100 hover:text-destructive"
                    @click="removeMember(member.id)"
                >
                    <Trash2Icon class="h-3 w-3" />
                </button>
            </div>

            <!-- Add slot -->
            <button
                v-if="!isLocked"
                type="button"
                class="flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border py-8 text-xs text-muted-foreground transition hover:border-primary hover:text-foreground"
                @click="addMember"
            >
                <PlusIcon class="h-4 w-4" />
                Add member
            </button>
        </div>

        <!-- List layout -->
        <div v-else class="space-y-4">
            <div
                v-for="member in data.members"
                :key="member.id"
                class="group/member relative flex gap-4 rounded-lg border border-border p-4"
            >
                <!-- Avatar -->
                <label
                    :class="[
                        'relative h-14 w-14 flex-shrink-0 overflow-hidden rounded-full border border-border',
                        !isLocked ? 'cursor-pointer' : '',
                    ]"
                >
                    <img
                        v-if="member.avatar_url"
                        :src="member.avatar_url"
                        :alt="member.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-muted"
                    >
                        <UserIcon class="h-6 w-6 text-muted-foreground/40" />
                    </div>
                    <input
                        v-if="!isLocked"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="(e) => handleAvatarUpload(member.id, e)"
                    />
                </label>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <input
                        v-if="!isLocked"
                        type="text"
                        :value="member.name"
                        class="w-full border-none bg-transparent text-sm font-semibold text-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                name: ($event.target as HTMLInputElement).value,
                            })
                        "
                    />
                    <p v-else class="text-sm font-semibold text-foreground">
                        {{ member.name }}
                    </p>

                    <input
                        v-if="!isLocked"
                        type="text"
                        :value="member.role"
                        class="w-full border-none bg-transparent text-xs text-muted-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                role: ($event.target as HTMLInputElement).value,
                            })
                        "
                    />
                    <p v-else class="text-xs text-muted-foreground">
                        {{ member.role }}
                    </p>

                    <textarea
                        v-if="!isLocked"
                        :value="member.bio ?? ''"
                        rows="2"
                        placeholder="Short bio…"
                        class="mt-1 w-full resize-none border-none bg-transparent text-xs text-muted-foreground outline-none"
                        @input="
                            updateMember(member.id, {
                                bio:
                                    ($event.target as HTMLTextAreaElement)
                                        .value || null,
                            })
                        "
                    />
                    <p
                        v-else-if="member.bio"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        {{ member.bio }}
                    </p>
                </div>

                <!-- Delete -->
                <button
                    v-if="!isLocked"
                    type="button"
                    class="absolute top-3 right-3 flex h-5 w-5 items-center justify-center rounded text-muted-foreground opacity-0 transition group-hover/member:opacity-100 hover:text-destructive"
                    @click="removeMember(member.id)"
                >
                    <Trash2Icon class="h-3 w-3" />
                </button>
            </div>

            <button
                v-if="!isLocked"
                type="button"
                class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border py-3 text-xs text-muted-foreground transition hover:border-primary hover:text-foreground"
                @click="addMember"
            >
                <PlusIcon class="h-3.5 w-3.5" /> Add member
            </button>
        </div>
    </section>
</template>
