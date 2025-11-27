<script setup lang="ts">
/**
 * BottomSheet Component
 * iOS-style bottom sheet modal dengan drag-to-dismiss gesture,
 * spring animations, dan backdrop blur effect
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

/**
 * Props untuk BottomSheet
 */
interface Props {
    /** Control visibility dari parent */
    modelValue: boolean
    /** Judul sheet (optional) */
    title?: string
    /** Tampilkan handle untuk drag */
    showHandle?: boolean
    /** Tinggi snap points dalam persen (0-100) */
    snapPoints?: number[]
    /** Apakah bisa ditutup dengan drag ke bawah */
    dismissible?: boolean
    /** Background overlay color */
    overlayClass?: string
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
    showHandle: true,
    snapPoints: () => [0, 50, 90],
    dismissible: true,
    overlayClass: '',
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    close: []
}>()

/**
 * Haptic feedback untuk tactile response
 */
const haptic = useHapticFeedback()

/**
 * Refs untuk DOM elements dan state
 */
const sheetRef = ref<HTMLElement | null>(null)
const contentRef = ref<HTMLElement | null>(null)

/**
 * Drag state
 */
const isDragging = ref(false)
const startY = ref(0)
const currentY = ref(0)
const sheetHeight = ref(0)
const currentSnapIndex = ref(1) // Default ke snap point tengah

/**
 * Computed untuk transform berdasarkan drag
 */
const translateY = computed(() => {
    if (isDragging.value) {
        return Math.max(0, currentY.value)
    }
    // Saat tidak drag, gunakan snap point
    if (!props.modelValue) {
        return sheetHeight.value
    }
    const snapPercent = props.snapPoints[currentSnapIndex.value] ?? 50
    return sheetHeight.value * (1 - snapPercent / 100)
})

/**
 * Handle touch/mouse start
 */
function handleDragStart(e: TouchEvent | MouseEvent) {
    if (!props.dismissible) return

    isDragging.value = true
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY
    startY.value = clientY
    currentY.value = translateY.value

    haptic.light()
}

/**
 * Handle touch/mouse move
 */
function handleDragMove(e: TouchEvent | MouseEvent) {
    if (!isDragging.value) return

    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY
    const deltaY = clientY - startY.value
    currentY.value = Math.max(0, translateY.value + deltaY)

    // Prevent scroll ketika dragging
    e.preventDefault()
}

/**
 * Handle touch/mouse end
 */
function handleDragEnd() {
    if (!isDragging.value) return

    isDragging.value = false

    // Tentukan snap point terdekat berdasarkan posisi
    const currentPercent = (1 - currentY.value / sheetHeight.value) * 100
    let nearestSnapIndex = 0
    let minDistance = Math.abs(currentPercent - props.snapPoints[0])

    props.snapPoints.forEach((snap, index) => {
        const distance = Math.abs(currentPercent - snap)
        if (distance < minDistance) {
            minDistance = distance
            nearestSnapIndex = index
        }
    })

    // Jika snap ke 0, tutup sheet
    if (nearestSnapIndex === 0 || currentPercent < 15) {
        haptic.medium()
        close()
    } else {
        currentSnapIndex.value = nearestSnapIndex
        haptic.selection()
    }
}

/**
 * Close sheet
 */
function close() {
    emit('update:modelValue', false)
    emit('close')
}

/**
 * Handle backdrop click
 */
function handleBackdropClick() {
    if (props.dismissible) {
        haptic.light()
        close()
    }
}

/**
 * Handle escape key
 */
function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.dismissible) {
        close()
    }
}

/**
 * Update sheet height on mount dan resize
 */
function updateSheetHeight() {
    if (sheetRef.value) {
        sheetHeight.value = sheetRef.value.offsetHeight
    }
}

/**
 * Watch untuk open/close
 */
watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            updateSheetHeight()
            currentSnapIndex.value = 1 // Reset ke snap tengah
        })
        document.addEventListener('keydown', handleKeydown)
        // Prevent body scroll
        document.body.style.overflow = 'hidden'
    } else {
        document.removeEventListener('keydown', handleKeydown)
        document.body.style.overflow = ''
    }
})

onMounted(() => {
    window.addEventListener('resize', updateSheetHeight)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateSheetHeight)
    document.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-[var(--ios-spring-smooth)]"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-[var(--ios-spring-snappy)]"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
                :class="overlayClass"
                @click="handleBackdropClick"
            />
        </Transition>

        <!-- Bottom Sheet -->
        <Transition
            enter-active-class="transition-transform duration-500 ease-[var(--ios-spring-bounce)]"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-300 ease-[var(--ios-spring-snappy)]"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="modelValue"
                ref="sheetRef"
                class="ios-bottom-sheet fixed inset-x-0 bottom-0 z-50 flex max-h-[90vh] flex-col rounded-t-3xl bg-background"
                :style="{
                    transform: `translateY(${translateY}px)`,
                    transition: isDragging ? 'none' : 'transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
                }"
                @touchstart.passive="handleDragStart"
                @touchmove="handleDragMove"
                @touchend="handleDragEnd"
                @mousedown="handleDragStart"
                @mousemove="handleDragMove"
                @mouseup="handleDragEnd"
                @mouseleave="handleDragEnd"
            >
                <!-- Handle bar untuk drag gesture -->
                <div
                    v-if="showHandle"
                    class="flex cursor-grab items-center justify-center pb-2 pt-3 active:cursor-grabbing"
                >
                    <div class="ios-handle h-1.5 w-12 rounded-full bg-muted-foreground/30" />
                </div>

                <!-- Header dengan title -->
                <div
                    v-if="title || $slots.header"
                    class="flex items-center justify-between border-b border-border px-4 pb-3"
                >
                    <slot name="header">
                        <h2 class="text-lg font-semibold text-foreground">{{ title }}</h2>
                    </slot>
                </div>

                <!-- Content area dengan scroll -->
                <div
                    ref="contentRef"
                    class="ios-scroll flex-1 overflow-y-auto overscroll-contain px-4 py-4"
                >
                    <slot />
                </div>

                <!-- Footer actions -->
                <div
                    v-if="$slots.footer"
                    class="border-t border-border px-4 py-4 pb-[calc(1rem+env(safe-area-inset-bottom))]"
                >
                    <slot name="footer" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

