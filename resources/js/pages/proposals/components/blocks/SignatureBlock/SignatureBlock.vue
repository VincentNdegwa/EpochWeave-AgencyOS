<script setup lang="ts">
import { ref, computed } from 'vue';
import { PenLineIcon, CheckCircleIcon } from '@lucide/vue';
import type { BaseBlock, SignatureBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: SignatureBlockData;
  block: BaseBlock;
  isLocked: boolean;
  // Portal mode props — passed by the portal renderer
  portalMode?: boolean;
  alreadySigned?: boolean;
  signerName?: string | null;
  signerDate?: string | null;
  signatureDataUrl?: string | null;
}>();

const emit = defineEmits<{
  sign: [payload: { name: string; date: string; dataUrl: string }];
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<SignatureBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

// ── Signature pad (canvas) ────────────────────────────────────
const canvasRef    = ref<HTMLCanvasElement | null>(null);
const isDrawing    = ref(false);
const hasStrokes   = ref(false);
const typedName    = ref('');
const typedDate    = ref(new Date().toISOString().split('T')[0]);

const startDraw = (e: MouseEvent | TouchEvent) => {
  if (!canvasRef.value) return;
  isDrawing.value = true;
  const ctx = canvasRef.value.getContext('2d');
  if (!ctx) return;
  const pos = getPos(e, canvasRef.value);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e: MouseEvent | TouchEvent) => {
  if (!isDrawing.value || !canvasRef.value) return;
  e.preventDefault();
  hasStrokes.value = true;
  const ctx = canvasRef.value.getContext('2d');
  if (!ctx) return;
  const pos = getPos(e, canvasRef.value);
  ctx.lineTo(pos.x, pos.y);
  ctx.strokeStyle = '#111827';
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
  ctx.stroke();
};

const endDraw = () => { isDrawing.value = false; };

const clearCanvas = () => {
  if (!canvasRef.value) return;
  const ctx = canvasRef.value.getContext('2d');
  if (ctx) ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  hasStrokes.value = false;
};

const getPos = (e: MouseEvent | TouchEvent, canvas: HTMLCanvasElement) => {
  const rect = canvas.getBoundingClientRect();
  if ('touches' in e) {
    return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
  }
  return { x: e.clientX - rect.left, y: e.clientY - rect.top };
};

const submitSignature = () => {
  if (!canvasRef.value || !typedName.value.trim()) return;
  const dataUrl = canvasRef.value.toDataURL('image/png');
  emit('sign', { name: typedName.value, date: typedDate.value, dataUrl });
};

const canSubmit = computed(() => typedName.value.trim().length > 0 && hasStrokes.value);
</script>

<template>
  <section class="px-8 py-6">

    <!-- ── Builder preview mode ───────────────────────────────── -->
    <div v-if="!portalMode" class="rounded-xl border-2 border-dashed border-border p-8 text-center">
      <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full
                  border border-border bg-muted">
        <PenLineIcon class="h-5 w-5 text-muted-foreground" />
      </div>
      <h3 class="text-sm font-semibold text-foreground">
        {{ data.title || 'Signature' }}
      </h3>
      <p class="mt-1 text-xs text-muted-foreground">
        {{ data.description || 'Client signs here to accept the proposal' }}
      </p>
      <div class="mt-4 flex flex-wrap justify-center gap-2">
        <span v-if="data.require_name" class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
          Name required
        </span>
        <span v-if="data.require_date" class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
          Date required
        </span>
        <span v-if="data.require_signature" class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
          Signature required
        </span>
      </div>
    </div>

    <!-- ── Already signed (frozen) ───────────────────────────── -->
    <div v-else-if="alreadySigned" class="rounded-xl border border-border bg-muted/30 p-6">
      <div class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground">
        <CheckCircleIcon class="h-4 w-4 text-green-600" />
        Proposal accepted
      </div>
      <img
        v-if="signatureDataUrl"
        :src="signatureDataUrl"
        alt="Signature"
        class="mb-3 max-h-20 rounded border border-border bg-background p-2"
      />
      <div class="grid grid-cols-2 gap-4 text-xs text-muted-foreground">
        <div v-if="signerName">
          <p class="font-medium text-foreground">Signed by</p>
          <p>{{ signerName }}</p>
        </div>
        <div v-if="signerDate">
          <p class="font-medium text-foreground">Date</p>
          <p>{{ signerDate }}</p>
        </div>
      </div>
    </div>

    <!-- ── Portal sign mode ───────────────────────────────────── -->
    <div v-else class="space-y-4">
      <div>
        <h3 class="text-sm font-semibold text-foreground">{{ data.title || 'Sign to accept' }}</h3>
        <p v-if="data.description" class="mt-0.5 text-xs text-muted-foreground">{{ data.description }}</p>
      </div>

      <!-- Name field -->
      <div v-if="data.require_name" class="grid gap-1.5">
        <label class="text-xs font-medium text-muted-foreground">Full name</label>
        <input
          v-model="typedName"
          type="text"
          placeholder="Type your full name"
          class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm
                 outline-none focus:ring-2 focus:ring-ring"
        />
      </div>

      <!-- Date field -->
      <div v-if="data.require_date" class="grid gap-1.5">
        <label class="text-xs font-medium text-muted-foreground">Date</label>
        <input
          v-model="typedDate"
          type="date"
          class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm
                 outline-none focus:ring-2 focus:ring-ring"
        />
      </div>

      <!-- Signature canvas -->
      <div v-if="data.require_signature" class="grid gap-1.5">
        <div class="flex items-center justify-between">
          <label class="text-xs font-medium text-muted-foreground">Signature</label>
          <button type="button" class="text-[11px] text-muted-foreground hover:text-foreground" @click="clearCanvas">
            Clear
          </button>
        </div>
        <canvas
          ref="canvasRef"
          width="560"
          height="120"
          class="w-full rounded-lg border border-border bg-background touch-none cursor-crosshair"
          @mousedown="startDraw"
          @mousemove="draw"
          @mouseup="endDraw"
          @mouseleave="endDraw"
          @touchstart.prevent="startDraw"
          @touchmove.prevent="draw"
          @touchend="endDraw"
        />
        <p class="text-[11px] text-muted-foreground">Draw your signature above</p>
      </div>

      <!-- Submit -->
      <button
        type="button"
        class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold
               text-primary-foreground transition hover:bg-primary/90 disabled:opacity-50"
        :disabled="!canSubmit"
        @click="submitSignature"
      >
        Accept & sign proposal
      </button>
    </div>

  </section>
</template>