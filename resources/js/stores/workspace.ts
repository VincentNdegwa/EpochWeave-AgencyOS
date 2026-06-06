import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useWorkspaceStore = defineStore('workspace', () => {
  const name = ref<string | null>(null);
  const logoUrl = ref<string | null>(null);

  const setWorkspace = (workspace: { name?: string | null; logo_url?: string | null } | null) => {
    name.value = workspace?.name ?? null;
    logoUrl.value = workspace?.logo_url ?? null;
  };

  const clear = () => {
    name.value = null;
    logoUrl.value = null;
  };

  return {
    name,
    logoUrl,
    setWorkspace,
    clear,
  };
});
