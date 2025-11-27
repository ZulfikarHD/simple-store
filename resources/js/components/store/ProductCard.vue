<script setup lang="ts">
/**
 * ProductCard Component
 * Menampilkan informasi produk dalam format card dengan iOS-style design
 * mencakup gambar, nama, kategori, harga, dan status ketersediaan
 * dengan spring animations, press feedback, dan haptic response
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Plus, ShoppingCart, Loader2, Check } from 'lucide-vue-next'
import { show } from '@/actions/App/Http/Controllers/ProductController'
import { store } from '@/actions/App/Http/Controllers/CartController'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

/**
 * Interface untuk status stok dari backend
 */
interface StockStatus {
    status: 'in_stock' | 'low_stock' | 'out_of_stock' | 'unavailable'
    label: string
    stock: number
}

/**
 * Props definition untuk ProductCard
 * dengan struktur data produk yang lengkap termasuk stock_status
 */
interface Props {
    product: {
        id: number
        name: string
        slug: string
        description?: string | null
        price: number
        image?: string | null
        stock?: number
        category?: {
            id: number
            name: string
        }
        is_available: boolean
        stock_status?: StockStatus
    }
    /** Mode tampilan: grid atau list */
    mode?: 'grid' | 'list'
    /** Index untuk staggered animation delay */
    index?: number
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'grid',
    index: 0,
})

/**
 * Haptic feedback untuk tactile response
 */
const haptic = useHapticFeedback()

/**
 * Loading dan success state untuk add to cart
 */
const isAdding = ref(false)
const showSuccess = ref(false)

/**
 * Press state untuk iOS-like tap feedback
 */
const isPressed = ref(false)
const isButtonPressed = ref(false)

/**
 * Generate URL detail produk menggunakan Wayfinder
 * dengan slug sebagai parameter untuk SEO-friendly URL
 */
const detailUrl = computed(() => {
    return show.url(props.product.slug)
})

/**
 * Format harga ke format Rupiah Indonesia
 * menggunakan Intl.NumberFormat untuk konsistensi
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(props.product.price)
})

/**
 * Staggered animation delay berdasarkan index
 */
const animationDelay = computed(() => ({
    animationDelay: `${props.index * 50}ms`,
}))

/**
 * Handle press start untuk card
 */
function handlePressStart() {
    isPressed.value = true
    haptic.light()
}

/**
 * Handle press end untuk card
 */
function handlePressEnd() {
    isPressed.value = false
}

/**
 * Handle press start untuk button
 */
function handleButtonPressStart() {
    isButtonPressed.value = true
    haptic.medium()
}

/**
 * Handle press end untuk button
 */
function handleButtonPressEnd() {
    isButtonPressed.value = false
}

/**
 * Handle click pada tombol add to cart
 * dengan validasi ketersediaan produk dan Inertia submission
 */
function handleAddToCart() {
    if (!props.product.is_available || isAdding.value) return

    haptic.medium()
    isAdding.value = true
    router.post(
        store.url(),
        {
            product_id: props.product.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess.value = true
                haptic.success()
                setTimeout(() => {
                    showSuccess.value = false
                }, 2000)
            },
            onFinish: () => {
                isAdding.value = false
            },
        }
    )
}

/**
 * Generate URL gambar produk
 * dengan fallback ke placeholder jika tidak ada
 */
const imageUrl = computed(() => {
    if (props.product.image) {
        return `/storage/${props.product.image}`
    }
    return null
})
</script>

<template>
    <article
        v-motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :enter="{
            opacity: 1,
            y: 0,
            scale: 1,
            transition: {
                type: 'spring',
                stiffness: 300,
                damping: 25,
                delay: index * 50,
            },
        }"
        class="product-card group cursor-pointer"
        :class="[
            mode === 'grid' ? 'flex flex-col' : 'flex flex-row gap-4',
            isPressed ? 'scale-[0.97]' : 'scale-100',
        ]"
        :style="animationDelay"
        @mousedown="handlePressStart"
        @mouseup="handlePressEnd"
        @mouseleave="handlePressEnd"
        @touchstart.passive="handlePressStart"
        @touchend="handlePressEnd"
        @touchcancel="handlePressEnd"
    >
        <!-- Product Image Container dengan Link ke Detail -->
        <Link
            :href="detailUrl"
            class="relative overflow-hidden"
            :class="{
                'aspect-square rounded-t-2xl': mode === 'grid',
                'w-24 h-24 shrink-0 rounded-xl': mode === 'list',
            }"
        >
            <!-- Image dengan hover zoom effect dan spring transition -->
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 ease-[var(--ios-spring-smooth)] group-hover:scale-[1.05]"
                loading="lazy"
            />
            <!-- Placeholder ketika tidak ada gambar -->
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-muted/50"
            >
                <ShoppingCart class="h-8 w-8 text-muted-foreground/50" />
            </div>

            <!-- Stock Status Badge - Enhanced dengan animation -->
            <Badge
                v-if="product.stock_status"
                v-motion
                :initial="{ scale: 0, opacity: 0 }"
                :enter="{
                    scale: 1,
                    opacity: 1,
                    transition: {
                        type: 'spring',
                        stiffness: 400,
                        damping: 20,
                        delay: index * 50 + 200,
                    },
                }"
                :variant="
                    product.stock_status.status === 'out_of_stock' || product.stock_status.status === 'unavailable'
                        ? 'destructive'
                        : product.stock_status.status === 'low_stock'
                          ? 'secondary'
                          : 'default'
                "
                :class="[
                    'absolute left-2 top-2 text-[10px] font-medium shadow-sm sm:left-3 sm:top-3 sm:text-xs',
                    product.stock_status.status === 'in_stock' && 'bg-green-100 text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                    product.stock_status.status === 'low_stock' && 'bg-amber-100 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
                ]"
            >
                {{ product.stock_status.label }}
            </Badge>
            <!-- Fallback untuk backward compatibility -->
            <Badge
                v-else-if="!product.is_available"
                v-motion
                :initial="{ scale: 0, opacity: 0 }"
                :enter="{
                    scale: 1,
                    opacity: 1,
                    transition: {
                        type: 'spring',
                        stiffness: 400,
                        damping: 20,
                        delay: index * 50 + 200,
                    },
                }"
                variant="destructive"
                class="absolute left-2 top-2 text-[10px] font-medium shadow-sm sm:left-3 sm:top-3 sm:text-xs"
            >
                Habis
            </Badge>
        </Link>

        <!-- Product Info - Optimized untuk mobile -->
        <div
            class="flex flex-1 flex-col"
            :class="{
                'p-3 sm:p-4': mode === 'grid',
                'py-1': mode === 'list',
            }"
        >
            <!-- Category Label dengan fade in -->
            <p
                v-if="product.category"
                class="mb-0.5 text-[10px] font-medium uppercase tracking-wider text-muted-foreground/80 sm:mb-1 sm:text-xs"
            >
                {{ product.category.name }}
            </p>

            <!-- Product Name -->
            <h3
                class="font-semibold text-foreground transition-colors duration-200"
                :class="{
                    'mb-1.5 line-clamp-2 text-sm sm:mb-2 sm:text-base': mode === 'grid',
                    'mb-1 line-clamp-1 text-sm': mode === 'list',
                }"
            >
                {{ product.name }}
            </h3>

            <!-- Description (grid mode only, hidden pada mobile kecil) -->
            <p
                v-if="product.description && mode === 'grid'"
                class="mb-2 line-clamp-2 hidden text-xs text-muted-foreground sm:mb-3 sm:block sm:text-sm"
            >
                {{ product.description }}
            </p>

            <!-- Price & Action - Touch-friendly dengan iOS press feedback -->
            <div class="mt-auto flex items-center justify-between gap-2">
                <p class="text-base font-bold text-primary sm:text-lg">
                    {{ formattedPrice }}
                </p>

                <!-- Add to Cart Button dengan iOS-like spring animation -->
                <Button
                    v-if="mode === 'grid'"
                    v-motion
                    :initial="{ scale: 0, opacity: 0 }"
                    :enter="{
                        scale: 1,
                        opacity: 1,
                        transition: {
                            type: 'spring',
                            stiffness: 500,
                            damping: 25,
                            delay: index * 50 + 300,
                        },
                    }"
                    size="icon"
                    variant="secondary"
                    :disabled="!product.is_available || isAdding"
                    class="h-11 w-11 shrink-0 rounded-full transition-all duration-150 ease-[var(--ios-spring-snappy)]"
                    :class="[
                        showSuccess
                            ? 'bg-green-100 text-green-600 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400'
                            : 'bg-primary/10 text-primary hover:bg-primary/20',
                        isButtonPressed ? 'scale-90' : 'scale-100',
                        'disabled:opacity-50',
                    ]"
                    aria-label="Tambah ke keranjang"
                    @click.prevent="handleAddToCart"
                    @mousedown.stop="handleButtonPressStart"
                    @mouseup.stop="handleButtonPressEnd"
                    @mouseleave="handleButtonPressEnd"
                    @touchstart.passive.stop="handleButtonPressStart"
                    @touchend.stop="handleButtonPressEnd"
                >
                    <!-- Loading spinner dengan iOS-style animation -->
                    <Loader2
                        v-if="isAdding"
                        class="h-5 w-5 animate-spin"
                    />
                    <!-- Success check dengan bounce animation -->
                    <Check
                        v-else-if="showSuccess"
                        v-motion
                        :initial="{ scale: 0 }"
                        :enter="{
                            scale: 1,
                            transition: {
                                type: 'spring',
                                stiffness: 600,
                                damping: 15,
                            },
                        }"
                        class="h-5 w-5"
                    />
                    <!-- Plus icon default -->
                    <Plus v-else class="h-5 w-5" />
                </Button>
            </div>
        </div>
    </article>
</template>
