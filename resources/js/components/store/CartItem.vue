<script setup lang="ts">
/**
 * CartItem Component
 * Menampilkan item dalam keranjang belanja dengan iOS-like interactions
 * menggunakan motion-v untuk swipe-to-delete gesture, spring animations,
 * dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { Button } from '@/components/ui/button'
import { Minus, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import { update, destroy } from '@/actions/App/Http/Controllers/CartController'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'

/**
 * Props definition untuk CartItem
 */
interface Props {
    item: {
        id: number
        product: {
            id: number
            name: string
            slug: string
            price: number
            image?: string | null
            is_available: boolean
        }
        quantity: number
        subtotal: number
    }
    /** Index untuk staggered animation */
    index?: number
}

const props = withDefaults(defineProps<Props>(), {
    index: 0,
})

/**
 * Haptic feedback
 */
const haptic = useHapticFeedback()

/**
 * Loading states untuk operasi cart
 */
const isUpdating = ref(false)
const isRemoving = ref(false)

/**
 * Press states untuk iOS-like feedback
 */
const isMinusPressed = ref(false)
const isPlusPressed = ref(false)
const isDeletePressed = ref(false)

/**
 * Swipe state untuk swipe-to-delete
 */
const swipeX = ref(0)
const startX = ref(0)
const isSwiping = ref(false)
const showDeleteAction = ref(false)

/**
 * Threshold untuk trigger delete action (dalam px)
 */
const DELETE_THRESHOLD = 80

/**
 * Format harga ke format Rupiah Indonesia
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.item.product.price)
})

/**
 * Format subtotal ke format Rupiah Indonesia
 */
const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.item.subtotal)
})

/**
 * Generate URL gambar produk
 */
const imageUrl = computed(() => {
    if (props.item.product.image) {
        return `/storage/${props.item.product.image}`
    }
    return null
})

/**
 * Computed untuk swipe transform style
 */
const swipeStyle = computed(() => ({
    transform: `translateX(${swipeX.value}px)`,
    transition: isSwiping.value ? 'none' : 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
}))

/**
 * Animation delay berdasarkan index
 */
const animationDelay = computed(() => props.index * 0.05)

/**
 * Handle touch start untuk swipe
 */
function handleTouchStart(e: TouchEvent) {
    startX.value = e.touches[0].clientX
    isSwiping.value = true
}

/**
 * Handle touch move untuk swipe
 */
function handleTouchMove(e: TouchEvent) {
    if (!isSwiping.value) return

    const deltaX = e.touches[0].clientX - startX.value
    // Hanya allow swipe ke kiri (negative)
    swipeX.value = Math.min(0, Math.max(-DELETE_THRESHOLD - 20, deltaX))

    // Haptic feedback saat mencapai threshold
    if (swipeX.value <= -DELETE_THRESHOLD && !showDeleteAction.value) {
        showDeleteAction.value = true
        haptic.medium()
    } else if (swipeX.value > -DELETE_THRESHOLD && showDeleteAction.value) {
        showDeleteAction.value = false
    }
}

/**
 * Handle touch end untuk swipe
 */
function handleTouchEnd() {
    isSwiping.value = false

    if (swipeX.value <= -DELETE_THRESHOLD) {
        // Trigger delete
        handleRemove()
    } else {
        // Reset position
        swipeX.value = 0
        showDeleteAction.value = false
    }
}

/**
 * Handler untuk increment quantity
 */
function handleIncrement() {
    if (isUpdating.value) return

    haptic.light()
    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity + 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk decrement quantity
 */
function handleDecrement() {
    if (isUpdating.value || props.item.quantity <= 1) return

    haptic.light()
    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity - 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk remove item dari cart
 */
function handleRemove() {
    if (isRemoving.value) return

    haptic.heavy()
    isRemoving.value = true
    router.delete(destroy.url(props.item.id), {
        preserveScroll: true,
        onFinish: () => {
            isRemoving.value = false
            swipeX.value = 0
            showDeleteAction.value = false
        },
    })
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Motion
        :initial="{ opacity: 0, x: -20 }"
        :animate="{ opacity: 1, x: 0 }"
        :transition="{ ...springTransition, delay: animationDelay }"
        class="relative overflow-hidden rounded-2xl"
    >
        <!-- Delete action background (revealed on swipe) -->
        <div
            class="absolute inset-y-0 right-0 flex w-24 items-center justify-center bg-destructive transition-opacity duration-200"
            :class="showDeleteAction ? 'opacity-100' : 'opacity-0'"
        >
            <Trash2 class="h-6 w-6 text-destructive-foreground" />
        </div>

        <!-- Main content dengan swipe gesture -->
        <div
            class="relative flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-sm transition-shadow sm:flex-row sm:items-center sm:gap-4 sm:p-4"
            :class="{ 'opacity-50': isRemoving }"
            :style="swipeStyle"
            @touchstart.passive="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
        >
            <!-- Top Row: Image, Info & Remove (Mobile) -->
            <div class="flex items-start gap-3 sm:contents">
                <!-- Product Image dengan rounded corners -->
                <div class="h-18 w-18 shrink-0 overflow-hidden rounded-xl bg-muted sm:h-20 sm:w-20">
                    <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        :alt="item.product.name"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-muted/50"
                    >
                        <span class="text-xl sm:text-2xl">🛒</span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-1 flex-col gap-0.5 sm:gap-1">
                    <h4 class="line-clamp-2 text-sm font-semibold text-foreground sm:line-clamp-1 sm:text-base">
                        {{ item.product.name }}
                    </h4>
                    <p class="text-sm text-muted-foreground">
                        {{ formattedPrice }}
                    </p>
                    <!-- Availability Warning -->
                    <p
                        v-if="!item.product.is_available"
                        class="text-xs text-destructive"
                    >
                        Produk tidak tersedia
                    </p>
                </div>

                <!-- Remove Button - Mobile Only -->
                <Motion
                    :animate="{ scale: isDeletePressed ? 0.9 : 1 }"
                    :transition="snappyTransition"
                    class="sm:hidden"
                >
                <Button
                    variant="ghost"
                    size="icon"
                        class="h-10 w-10 shrink-0 rounded-xl text-destructive hover:bg-destructive/10 hover:text-destructive"
                    :disabled="isRemoving"
                    aria-label="Hapus item"
                    @click="handleRemove"
                    @mousedown="isDeletePressed = true"
                    @mouseup="isDeletePressed = false"
                    @mouseleave="isDeletePressed = false"
                    @touchstart.passive="isDeletePressed = true"
                    @touchend="isDeletePressed = false"
                >
                    <Loader2 v-if="isRemoving" class="h-4 w-4 animate-spin" />
                    <Trash2 v-else class="h-4 w-4" />
                </Button>
                </Motion>
            </div>

            <!-- Bottom Row: Quantity & Subtotal (Mobile) -->
            <div class="flex items-center justify-between gap-3 sm:contents">
                <!-- Quantity Controls dengan iOS press feedback -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <Motion
                        :animate="{ scale: isMinusPressed ? 0.9 : 1 }"
                        :transition="snappyTransition"
                    >
                    <Button
                        variant="outline"
                        size="icon"
                            class="h-11 w-11 rounded-xl sm:h-9 sm:w-9"
                        :disabled="item.quantity <= 1 || isUpdating"
                        aria-label="Kurangi jumlah"
                        @click="handleDecrement"
                        @mousedown="isMinusPressed = true"
                        @mouseup="isMinusPressed = false"
                        @mouseleave="isMinusPressed = false"
                        @touchstart.passive="isMinusPressed = true"
                        @touchend="isMinusPressed = false"
                    >
                        <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                        <Minus v-else class="h-4 w-4" />
                    </Button>
                    </Motion>

                    <!-- Quantity dengan number change animation -->
                    <Motion
                        :key="item.quantity"
                        :initial="{ scale: 1.2, opacity: 0 }"
                        :animate="{ scale: 1, opacity: 1 }"
                        :transition="bouncyTransition"
                        class="w-10 text-center text-lg font-semibold"
                    >
                        {{ item.quantity }}
                    </Motion>

                    <Motion
                        :animate="{ scale: isPlusPressed ? 0.9 : 1 }"
                        :transition="snappyTransition"
                    >
                    <Button
                        variant="outline"
                        size="icon"
                            class="h-11 w-11 rounded-xl sm:h-9 sm:w-9"
                        :disabled="isUpdating"
                        aria-label="Tambah jumlah"
                        @click="handleIncrement"
                        @mousedown="isPlusPressed = true"
                        @mouseup="isPlusPressed = false"
                        @mouseleave="isPlusPressed = false"
                        @touchstart.passive="isPlusPressed = true"
                        @touchend="isPlusPressed = false"
                    >
                        <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                        <Plus v-else class="h-4 w-4" />
                    </Button>
                    </Motion>
                </div>

                <!-- Subtotal & Remove (Desktop) -->
                <div class="flex flex-col items-end gap-2">
                    <!-- Subtotal dengan number change animation -->
                    <Motion
                        :key="item.subtotal"
                        :initial="{ scale: 1.1, opacity: 0 }"
                        :animate="{ scale: 1, opacity: 1 }"
                        :transition="bouncyTransition"
                        class="price-tag text-base font-bold sm:text-lg"
                    >
                        {{ formattedSubtotal }}
                    </Motion>
                    <Motion
                        :animate="{ scale: isDeletePressed ? 0.9 : 1 }"
                        :transition="snappyTransition"
                        class="hidden sm:block"
                    >
                    <Button
                        variant="ghost"
                        size="icon"
                            class="h-9 w-9 rounded-xl text-destructive hover:bg-destructive/10 hover:text-destructive"
                        :disabled="isRemoving"
                        aria-label="Hapus item"
                        @click="handleRemove"
                        @mousedown="isDeletePressed = true"
                        @mouseup="isDeletePressed = false"
                        @mouseleave="isDeletePressed = false"
                    >
                        <Loader2 v-if="isRemoving" class="h-4 w-4 animate-spin" />
                        <Trash2 v-else class="h-4 w-4" />
                    </Button>
                    </Motion>
                </div>
            </div>
        </div>
    </Motion>
</template>
