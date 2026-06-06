export type DepositType = 'percentage' | 'fixed';

export interface ProposalMeta {
  currency: string;
  validUntil: string | null;
  proposalNumber: string | null;
  depositEnabled: boolean;
  depositType: DepositType;
  depositValue: number;
}
