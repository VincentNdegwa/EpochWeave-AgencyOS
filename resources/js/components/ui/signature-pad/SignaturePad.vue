<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const emit = defineEmits<{
    signature: [data: string];
    clear: [];
}>();

const canvasRef = ref<HTMLCanvasElement>();
const typedSignature = ref('');
const selectedColor = ref('#000000');
const lineWidth = ref(2);
const isDrawing = ref(false);
const lastX = ref(0);
const lastY = ref(0);
const hasDrawn = ref(false);

const colors = [
    '#000000',
    '#1e40af', 
    '#dc2626',
    '#16a34a',
    '#9333ea',
    '#ea580c'
];

const hasTypedSignature = computed(() => typedSignature.value.trim().length > 0);
const hasDrawnSignature = computed(() => {
    if (hasDrawn.value) return true;
    
    if (!canvasRef.value) return false;
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return false;
    
    const imageData = ctx.getImageData(0, 0, canvasRef.value.width, canvasRef.value.height);
    const pixels = imageData.data;
    
    for (let i = 3; i < pixels.length; i += 4) {
        if (pixels[i] > 0) {
            return true;
        }
    }
    
    return false;
});

const activeTab = ref<'draw' | 'type'>('draw');

function initCanvas() {
    if (!canvasRef.value) return;
    
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, rect.width, rect.height);
    
    ctx.strokeStyle = selectedColor.value;
    ctx.lineWidth = lineWidth.value;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}

function startDrawing(e: MouseEvent | TouchEvent) {
    if (!canvasRef.value) return;
    
    isDrawing.value = true;
    const rect = canvasRef.value.getBoundingClientRect();
    
    if (e instanceof MouseEvent) {
        lastX.value = e.clientX - rect.left;
        lastY.value = e.clientY - rect.top;
    } else if (e.touches.length > 0) {
        lastX.value = e.touches[0].clientX - rect.left;
        lastY.value = e.touches[0].clientY - rect.top;
    }
}

function draw(e: MouseEvent | TouchEvent) {
    if (!isDrawing.value || !canvasRef.value) return;
    
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return;
    
    const rect = canvasRef.value.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    let currentX: number, currentY: number;
    
    if (e instanceof MouseEvent) {
        currentX = e.clientX - rect.left;
        currentY = e.clientY - rect.top;
    } else if (e.touches.length > 0) {
        currentX = e.touches[0].clientX - rect.left;
        currentY = e.touches[0].clientY - rect.top;
    } else {
        return;
    }
    
    ctx.strokeStyle = selectedColor.value;
    ctx.lineWidth = lineWidth.value;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    ctx.beginPath();
    ctx.moveTo(lastX.value, lastY.value);
    ctx.lineTo(currentX, currentY);
    ctx.stroke();
    
    lastX.value = currentX;
    lastY.value = currentY;
    hasDrawn.value = true;
}

function stopDrawing() {
    isDrawing.value = false;
}

function clearCanvas() {
    if (!canvasRef.value) return;
    
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return;
    
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    hasDrawn.value = false;
    emit('clear');
}

function undoLastStroke() {
    clearCanvas();
}

function generateTypedSignature() {
    if (!hasTypedSignature.value) return null;
    
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    
    canvas.width = 400;
    canvas.height = 150;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    ctx.font = 'italic 32px "Brush Script MT", cursive';
    ctx.fillStyle = selectedColor.value;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    ctx.fillText(typedSignature.value, canvas.width / 2, canvas.height / 2);
    
    return canvas.toDataURL('image/png');
}

function generateDrawnSignature() {
    if (!canvasRef.value || !hasDrawnSignature.value) return null;
    
    return canvasRef.value.toDataURL('image/png');
}

function saveSignature() {
    let signatureData: string | null = null;
    
    if (activeTab.value === 'type' && hasTypedSignature.value) {
        signatureData = generateTypedSignature();
    } else if (activeTab.value === 'draw' && hasDrawnSignature.value) {
        signatureData = generateDrawnSignature();
    }
    
    if (signatureData) {
        emit('signature', signatureData);
    }
}

function switchToTab(tab: 'draw' | 'type') {
    activeTab.value = tab;
    nextTick(() => {
        if (tab === 'draw') {
            initCanvas();
        }
    });
}

onMounted(() => {
    nextTick(() => {
        initCanvas();
    });
});
</script>

<template>
    <div class="w-full space-y-4">
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid w-full grid-cols-2">
                <TabsTrigger value="draw" @click="switchToTab('draw')">
                    Draw Signature
                </TabsTrigger>
                <TabsTrigger value="type" @click="switchToTab('type')">
                    Type Signature
                </TabsTrigger>
            </TabsList>
            
            <TabsContent value="draw" class="space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label>Signature Color</Label>
                        <div class="flex gap-2">
                            <button
                                v-for="color in colors"
                                :key="color"
                                @click="selectedColor = color"
                                class="w-6 h-6 rounded-full border-2 transition-all"
                                :class="selectedColor === color ? 'border-gray-800 scale-110' : 'border-gray-300'"
                                :style="{ backgroundColor: color }"
                            />
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <Label>Line Width</Label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="lineWidth"
                                type="range"
                                min="1"
                                max="8"
                                class="w-24"
                            />
                            <span class="text-sm text-muted-foreground w-4">{{ lineWidth }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative border-2 border-dashed border-gray-300 rounded-lg">
                    <canvas
                        ref="canvasRef"
                        class="w-full h-32 cursor-crosshair touch-none"
                        @mousedown="startDrawing"
                        @mousemove="draw"
                        @mouseup="stopDrawing"
                        @mouseleave="stopDrawing"
                        @touchstart="startDrawing"
                        @touchmove="draw"
                        @touchend="stopDrawing"
                    />
                    
                    <div class="absolute top-2 right-2 flex gap-1">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="undoLastStroke"
                            class="bg-white shadow-sm"
                        >
                            Undo
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="clearCanvas"
                            class="bg-white shadow-sm"
                        >
                            Clear
                        </Button>
                    </div>
                </div>
            </TabsContent>
            
            <TabsContent value="type" class="space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label>Signature Color</Label>
                        <div class="flex gap-2">
                            <button
                                v-for="color in colors"
                                :key="color"
                                @click="selectedColor = color"
                                class="w-6 h-6 rounded-full border-2 transition-all"
                                :class="selectedColor === color ? 'border-gray-800 scale-110' : 'border-gray-300'"
                                :style="{ backgroundColor: color }"
                            />
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <Label for="typed-signature">Type Your Signature</Label>
                        <Input
                            id="typed-signature"
                            v-model="typedSignature"
                            placeholder="Type your full name"
                            class="text-lg font-medium"
                            :style="{ color: selectedColor }"
                        />
                    </div>
                    
                    <div class="text-sm text-muted-foreground">
                        Preview: 
                        <span 
                            v-if="typedSignature"
                            class="text-lg italic ml-2"
                            :style="{ color: selectedColor }"
                        >
                            {{ typedSignature }}
                        </span>
                        <span v-else class="italic ml-2">Start typing to see preview</span>
                    </div>
                </div>
            </TabsContent>
        </Tabs>
        
        <Separator />
        
        <div class="flex justify-between items-center">
            <div class="text-sm text-muted-foreground">
                <span v-if="activeTab === 'draw' && hasDrawnSignature">
                    ✓ Signature drawn
                </span>
                <span v-else-if="activeTab === 'type' && hasTypedSignature">
                    ✓ Signature typed
                </span>
                <span v-else>
                    Please provide a signature
                </span>
            </div>
            
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="clearCanvas"
                    :disabled="!hasDrawnSignature && !hasTypedSignature"
                >
                    Clear
                </Button>
                <Button
                    @click="saveSignature"
                    :disabled="!hasDrawnSignature && !hasTypedSignature"
                >
                    Save Signature
                </Button>
            </div>
        </div>
    </div>
</template>
