<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import type { ProposalMeta } from '@/types/proposal-meta';

const title = defineModel<string>('title', { default: 'Untitled proposal' });
const proposalMetaModel = defineModel<ProposalMeta>('proposalMeta');

const ensureMeta = (): ProposalMeta => {
  if (!proposalMetaModel.value) {
    proposalMetaModel.value = {
      currency: 'USD',
      validUntil: null,
      proposalNumber: 'DRAFT',
      depositEnabled: false,
      depositType: 'percentage',
      depositValue: 0,
    };
  }
  return proposalMetaModel.value;
};

const updateMeta = (changes: Partial<ProposalMeta>) => {
  const current = ensureMeta();
  proposalMetaModel.value = {
    ...current,
    ...changes,
  };
};

const meta = computed<ProposalMeta>(() => ensureMeta());
const formattedNumber = computed(() => meta.value.proposalNumber ?? 'Draft');
</script>

<template>
  <div class="flex h-full flex-col gap-4 p-4 text-sm">
    <div class="grid gap-2">
      <Label for="proposal-title">Proposal title</Label>
      <Input id="proposal-title" :value="title" @input="(event: Event) => (title = (event.target as HTMLInputElement).value)" />
    </div>

    <div class="grid gap-2">
      <Label>Currency</Label>
      <Select :value="meta.currency" @update:value="(value: string) => updateMeta({ currency: value })">
        <SelectTrigger>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="USD">USD</SelectItem>
          <SelectItem value="KES">KES</SelectItem>
          <SelectItem value="NGN">NGN</SelectItem>
          <SelectItem value="GHS">GHS</SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div class="grid gap-2">
      <Label for="valid-until">Valid until</Label>
      <Input
        id="valid-until"
        type="date"
        :value="meta.validUntil ?? ''"
        @input="updateMeta({ validUntil: ($event.target as HTMLInputElement).value || null })"
      />
    </div>

    <div class="grid gap-1">
      <Label>Proposal number</Label>
      <p class="text-muted-foreground">{{ formattedNumber }}</p>
    </div>

    <div class="rounded-lg border border-border p-3">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium">Require deposit</p>
          <p class="text-xs text-muted-foreground">Collect commitment before kicking off work.</p>
        </div>
        <Switch :checked="meta.depositEnabled" @update:checked="(checked: boolean) => updateMeta({ depositEnabled: checked })" />
      </div>

      <div v-if="meta.depositEnabled" class="mt-3 space-y-3">
        <div class="grid gap-2">
          <Label>Deposit type</Label>
          <Select :value="meta.depositType" @update:value="(value: string) => updateMeta({ depositType: value as ProposalMeta['depositType'] })">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="percentage">Percentage</SelectItem>
              <SelectItem value="fixed">Fixed amount</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="grid gap-2">
          <Label>Deposit value</Label>
          <Input
            type="number"
            min="0"
            :value="meta.depositValue"
            @input="updateMeta({ depositValue: Number(($event.target as HTMLInputElement).value) })"
          />
        </div>
      </div>
    </div>
  </div>
</template>
