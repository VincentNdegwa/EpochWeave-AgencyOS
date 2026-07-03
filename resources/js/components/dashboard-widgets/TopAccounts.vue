<script setup lang="ts">
import { computed } from 'vue';
import { useCurrency } from '@/composables/useCurrency';
import type { TopAccount } from '@/types';

const props = defineProps<{ accounts: TopAccount[] }>();

const { format: formatCurrency } = useCurrency();

const maxAccountRevenue = computed(() =>
    props.accounts.length > 0
        ? Math.max(...props.accounts.map((a) => a.revenue))
        : 1,
);
</script>

<template>
    <div class="rounded-sm border border-border bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">Top Accounts (Revenue)</h3>
        <div class="space-y-3">
            <div
                v-for="(account, idx) in props.accounts"
                :key="account.id"
                class="flex items-center gap-3"
            >
                <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center bg-muted text-[10px] font-bold text-muted-foreground"
                >
                    {{ idx + 1 }}
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="truncate font-medium">{{
                            account.name
                        }}</span>
                        <span class="shrink-0 text-muted-foreground">
                            {{ formatCurrency(account.revenue) }}
                        </span>
                    </div>
                    <div class="mt-1 h-1.5 w-full bg-muted">
                        <div
                            class="h-full bg-primary transition-all"
                            :style="{
                                width: `${(account.revenue / maxAccountRevenue) * 100}%`,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
