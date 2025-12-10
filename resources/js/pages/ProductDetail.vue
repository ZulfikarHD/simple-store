<script setup lang="ts">
/**
 * ProductDetail Page - Halaman Detail Produk
 * Menampilkan informasi lengkap produk dengan iOS-like spring animations
 * menggunakan motion-v, parallax image effect, haptic feedback pada quantity selector,
 * dan smooth transitions
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { home } from '@/routes'
import { store as storeCart } from '@/actions/App/Http/Controllers/CartController'
import ProductCard from '@/components/store/ProductCard.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ShoppingBag,
    ShoppingCart,
    ChevronRight,
    ArrowLeft,
    Plus,
    Minus,
    Check,
    Loader2,
} from 'lucide-vue-next'

/**
 * Interface untuk status stok dari backend
 */
interface StockStatus {
    status: 'in_stock' | 'low_stock' | 'out_of_stock' | 'unavailable'
    label: string
    stock: number
}

/**
 * Interface untuk data produk dari ProductResource
 */
interface Product {
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
        slug: string
    }
    is_available: boolean
    stock_status?: StockStatus
}

/**
 * Interface untuk response dari Resource::collection
 */
interface ProductCollection {
    data: Product[]
}

/**
 * Props yang diterima dari ProductController::show
 */
interface Props {
    product: { data: Product }
    relatedProducts: ProductCollection
    cart?: {
        total_items: number
    }
}

const props = defineProps<Props>()

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Computed property untuk data produk yang di-unwrap dari resource
 */
const product = computed(() => props.product.data)

/**
 * Local state untuk quantity selector
 */
const quantity = ref(1)
const isAdding = ref(false)
const showSuccess = ref(false)

/**
 * Press states untuk iOS-like button feedback
 */
const isBackPressed = ref(false)
const isMinusPressed = ref(false)
const isPlusPressed = ref(false)
const isAddCartPressed = ref(false)

/**
 * Parallax scroll effect state
 */
const scrollY = ref(0)
const parallaxOffset = computed(() => Math.min(scrollY.value * 0.3, 100))

/**
 * Handle scroll untuk parallax effect
 */
function handleScroll() {
    scrollY.value = window.scrollY
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

/**
 * Generate URL gambar produk dengan fallback
 */
const imageUrl = computed(() => {
    if (product.value.image) {
        return `/storage/${product.value.image}`
    }
    return null
})

/**
 * Format harga ke format Rupiah Indonesia
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(product.value.price)
})

/**
 * Computed untuk cart total items
 */
const cartTotalItems = computed(() => props.cart?.total_items ?? 0)

/**
 * Handler untuk increment quantity dengan haptic
 */
function incrementQuantity() {
    if (quantity.value < 99) {
        haptic.light()
        quantity.value++
    }
}

/**
 * Handler untuk decrement quantity dengan haptic
 */
function decrementQuantity() {
    if (quantity.value > 1) {
        haptic.light()
        quantity.value--
    }
}

/**
 * Handler untuk tombol add to cart dengan haptic feedback
 */
function handleAddToCart() {
    if (!product.value.is_available || isAdding.value) return

    haptic.medium()
    isAdding.value = true
    router.post(
        storeCart.url(),
        {
            product_id: product.value.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess.value = true
                haptic.success()
                quantity.value = 1
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
 * Handler untuk navigasi kembali ke katalog
 */
function handleBack() {
    haptic.light()
    router.visit(home())
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`${product.name} - Simple Store`">
        <meta name="description" :content="product.description ?? `Beli ${product.name} dengan harga terbaik`" />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Motion
                    tag="a"
                    :href="home()"
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                    class="flex items-center gap-2 sm:gap-3"
                    @click.prevent="() => router.visit(home())"
                >
                    <div class="brand-logo h-9 w-9 sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                        <span class="hidden text-[10px] font-medium text-brand-gold sm:block">Premium Quality Products</span>
                    </div>
                </Motion>

                <!-- Cart Counter & Auth Navigation -->
                <nav class="flex items-center gap-2 sm:gap-3">
                    <CartCounter :count="cartTotalItems" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="ios-button hidden rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="ios-button hidden rounded-xl px-4 py-2.5 text-sm font-medium text-foreground sm:inline-flex"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="ios-button hidden rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm sm:inline-flex"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 pb-36 pt-6 sm:px-6 sm:pb-8 sm:pt-8 lg:px-8">
            <!-- Breadcrumb Navigation dengan fade in -->
            <Motion
                tag="nav"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="springTransition"
                class="mb-4 hidden items-center gap-2 text-sm text-muted-foreground sm:mb-6 sm:flex"
            >
                <Link :href="home()" class="transition-colors hover:text-foreground">
                    Beranda
                </Link>
                <ChevronRight class="h-4 w-4" />
                <template v-if="product.category">
                    <Link
                        :href="`/?category=${product.category.id}`"
                        class="transition-colors hover:text-foreground"
                    >
                        {{ product.category.name }}
                    </Link>
                    <ChevronRight class="h-4 w-4" />
                </template>
                <span class="max-w-[200px] truncate font-medium text-foreground">
                    {{ product.name }}
                </span>
            </Motion>

            <!-- Back Button dengan iOS press feedback -->
            <Motion
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ ...springTransition, delay: 0.05 }"
            >
                <Motion
                    :animate="{ scale: isBackPressed ? 0.95 : 1, opacity: isBackPressed ? 0.7 : 1 }"
                    :transition="snappyTransition"
                >
                    <Button
                variant="ghost"
                size="default"
                class="ios-button mb-4 flex h-11 items-center gap-2 rounded-xl sm:mb-6 sm:h-10"
                @click="handleBack"
                @mousedown="isBackPressed = true"
                @mouseup="isBackPressed = false"
                @mouseleave="isBackPressed = false"
                @touchstart.passive="isBackPressed = true"
                @touchend="isBackPressed = false"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Katalog
            </Button>
                </Motion>
            </Motion>

            <!-- Product Detail Section -->
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                <!-- Product Image dengan parallax effect -->
                <Motion
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    class="relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-lg sm:rounded-3xl"
                >
                    <div
                        class="aspect-square overflow-hidden"
                        :style="{ transform: `translateY(${parallaxOffset}px)` }"
                    >
                        <img
                            v-if="imageUrl"
                            :src="imageUrl"
                            :alt="product.name"
                            class="h-full w-full scale-110 object-cover transition-transform duration-700 ease-[var(--ios-spring-smooth)]"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-muted/50"
                        >
                            <ShoppingCart class="h-16 w-16 text-muted-foreground/30 sm:h-24 sm:w-24" />
                        </div>
                    </div>

                    <!-- Stock Status Badge dengan bounce animation -->
                    <AnimatePresence>
                        <Motion
                        v-if="product.stock_status && (product.stock_status.status === 'out_of_stock' || product.stock_status.status === 'unavailable')"
                        :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :exit="{ scale: 0 }"
                            :transition="{ ...bouncyTransition, delay: 0.3 }"
                            class="absolute left-3 top-3 sm:left-4 sm:top-4"
                        >
                            <Badge
                        variant="destructive"
                                class="text-xs font-medium shadow-sm sm:text-sm"
                    >
                        {{ product.stock_status.label }}
                    </Badge>
                        </Motion>
                        <Motion
                        v-else-if="product.stock_status?.status === 'low_stock'"
                        :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :exit="{ scale: 0 }"
                            :transition="{ ...bouncyTransition, delay: 0.3 }"
                            class="absolute left-3 top-3 sm:left-4 sm:top-4"
                        >
                            <Badge
                                class="bg-amber-100 text-xs font-medium text-amber-700 shadow-sm hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 sm:text-sm"
                    >
                        Stok Terbatas
                    </Badge>
                        </Motion>
                        <Motion
                        v-else-if="!product.is_available"
                        :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :exit="{ scale: 0 }"
                            :transition="{ ...bouncyTransition, delay: 0.3 }"
                            class="absolute left-3 top-3 sm:left-4 sm:top-4"
                        >
                            <Badge
                        variant="destructive"
                                class="text-xs font-medium shadow-sm sm:text-sm"
                    >
                        Stok Habis
                    </Badge>
                        </Motion>
                    </AnimatePresence>
                </Motion>

                <!-- Product Info dengan staggered animations -->
                <div class="flex flex-col">
                    <!-- Category -->
                    <Motion
                        v-if="product.category"
                        tag="p"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.15 }"
                        class="mb-1 text-xs font-medium uppercase tracking-wider text-muted-foreground/80 sm:mb-2 sm:text-sm"
                    >
                        {{ product.category.name }}
                    </Motion>

                    <!-- Product Name -->
                    <Motion
                        tag="h1"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.2 }"
                        class="mb-3 text-2xl font-bold tracking-tight text-foreground sm:mb-4 sm:text-3xl lg:text-4xl"
                    >
                        {{ product.name }}
                    </Motion>

                    <!-- Price dengan slide animation -->
                    <Motion
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ ...springTransition, delay: 0.25 }"
                        class="mb-4 sm:mb-6"
                    >
                        <div class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 px-4 py-2 dark:from-brand-blue-900/30 dark:to-brand-gold-900/20">
                            <p class="price-tag text-2xl font-bold sm:text-3xl">
                                {{ formattedPrice }}
                            </p>
                            <span v-if="product.is_available" class="text-xs font-medium text-brand-gold">
                                Harga Terbaik
                            </span>
                        </div>
                    </Motion>

                    <!-- Stock Status -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.3 }"
                        class="mb-4 flex flex-wrap items-center gap-2 sm:mb-6"
                    >
                        <div
                            v-if="product.stock_status"
                            :class="[
                                'flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium',
                                product.stock_status.status === 'in_stock' && 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                product.stock_status.status === 'low_stock' && 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                (product.stock_status.status === 'out_of_stock' || product.stock_status.status === 'unavailable') && 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            ]"
                        >
                            <Check v-if="product.stock_status.status === 'in_stock'" class="h-4 w-4" />
                            <span>{{ product.stock_status.label }}</span>
                        </div>
                        <div
                            v-else
                            :class="[
                                'flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium',
                                product.is_available
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                            ]"
                        >
                            <Check v-if="product.is_available" class="h-4 w-4" />
                            <span>{{ product.is_available ? 'Tersedia' : 'Tidak Tersedia' }}</span>
                        </div>

                        <span
                            v-if="product.stock_status?.status === 'low_stock'"
                            class="text-xs text-amber-600 dark:text-amber-400"
                        >
                            (Sisa {{ product.stock_status.stock }} unit)
                        </span>
                    </Motion>

                    <!-- Description -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.35 }"
                        class="mb-6 sm:mb-8"
                    >
                        <h2 class="mb-2 text-base font-semibold text-foreground sm:mb-3 sm:text-lg">Deskripsi</h2>
                        <p
                            v-if="product.description"
                            class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground sm:text-base"
                        >
                            {{ product.description }}
                        </p>
                        <p v-else class="text-sm italic text-muted-foreground sm:text-base">
                            Belum ada deskripsi untuk produk ini.
                        </p>
                    </Motion>

                    <!-- Desktop Quantity Selector dengan iOS interactions -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.4 }"
                        class="mt-auto hidden space-y-4 sm:block"
                    >
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-foreground">Jumlah:</span>
                            <div class="flex items-center gap-2">
                                <Motion
                                    :animate="{ scale: isMinusPressed ? 0.9 : 1 }"
                                    :transition="snappyTransition"
                                >
                                <Button
                                    variant="outline"
                                    size="icon"
                                        class="h-11 w-11 rounded-xl"
                                    :disabled="quantity <= 1 || !product.is_available"
                                    aria-label="Kurangi jumlah"
                                    @click="decrementQuantity"
                                    @mousedown="isMinusPressed = true"
                                    @mouseup="isMinusPressed = false"
                                    @mouseleave="isMinusPressed = false"
                                    @touchstart.passive="isMinusPressed = true"
                                    @touchend="isMinusPressed = false"
                                >
                                    <Minus class="h-4 w-4" />
                                </Button>
                                </Motion>

                                <Motion
                                    :key="quantity"
                                    :initial="{ scale: 1.2, opacity: 0 }"
                                    :animate="{ scale: 1, opacity: 1 }"
                                    :transition="bouncyTransition"
                                    class="w-14 text-center text-xl font-semibold"
                                >
                                    {{ quantity }}
                                </Motion>

                                <Motion
                                    :animate="{ scale: isPlusPressed ? 0.9 : 1 }"
                                    :transition="snappyTransition"
                                >
                                <Button
                                    variant="outline"
                                    size="icon"
                                        class="h-11 w-11 rounded-xl"
                                    :disabled="quantity >= 99 || !product.is_available"
                                    aria-label="Tambah jumlah"
                                    @click="incrementQuantity"
                                    @mousedown="isPlusPressed = true"
                                    @mouseup="isPlusPressed = false"
                                    @mouseleave="isPlusPressed = false"
                                    @touchstart.passive="isPlusPressed = true"
                                    @touchend="isPlusPressed = false"
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                                </Motion>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <Motion
                            :animate="{ scale: isAddCartPressed ? 0.95 : 1 }"
                            :transition="snappyTransition"
                        >
                        <Button
                            size="lg"
                            :disabled="!product.is_available || isAdding"
                                class="ios-button h-14 w-full gap-2 rounded-2xl text-base shadow-lg"
                            :class="[
                                showSuccess
                                    ? 'bg-green-600 hover:bg-green-600'
                                    : 'bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 hover:from-brand-blue-600 hover:to-brand-blue-700 shadow-brand-blue-500/25',
                            ]"
                            @click="handleAddToCart"
                            @mousedown="isAddCartPressed = true"
                            @mouseup="isAddCartPressed = false"
                            @mouseleave="isAddCartPressed = false"
                            @touchstart.passive="isAddCartPressed = true"
                            @touchend="isAddCartPressed = false"
                        >
                            <Loader2 v-if="isAdding" class="h-5 w-5 animate-spin" />
                                <AnimatePresence mode="wait">
                                    <Motion
                                        v-if="showSuccess"
                                        :key="'success'"
                                :initial="{ scale: 0 }"
                                        :animate="{ scale: 1 }"
                                        :exit="{ scale: 0 }"
                                        :transition="bouncyTransition"
                                    >
                                        <Check class="h-5 w-5" />
                                    </Motion>
                                    <Plus v-else-if="!isAdding" class="h-5 w-5" />
                                </AnimatePresence>
                            <span v-if="showSuccess">Ditambahkan ke Keranjang!</span>
                            <span v-else-if="product.is_available">Tambah ke Keranjang</span>
                            <span v-else>Stok Habis</span>
                        </Button>
                        </Motion>
                    </Motion>
                </div>
            </div>

            <!-- Related Products Section dengan staggered cards -->
            <Motion
                v-if="relatedProducts.data.length > 0"
                tag="section"
                :initial="{ opacity: 0, y: 30 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springPresets.smooth, delay: 0.5 }"
                class="mt-12 sm:mt-16"
            >
                <h2 class="mb-6 text-2xl font-bold tracking-tight text-foreground">
                    Produk Terkait
                </h2>
                <!-- Horizontal scroll carousel untuk mobile -->
                <div class="ios-snap-scroll -mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                    <ProductCard
                        v-for="(relatedProduct, index) in relatedProducts.data"
                        :key="relatedProduct.id"
                        :product="relatedProduct"
                        :index="index"
                        mode="grid"
                        class="ios-snap-item w-44 shrink-0 sm:w-auto"
                    />
                </div>
            </Motion>
        </main>

        <!-- Footer -->
        <footer class="mt-12 hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background sm:mt-16 md:block">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div class="flex flex-col items-center gap-2">
                    <p class="text-center text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} Simple Store. Dibuat dengan ❤️ oleh Zulfikar Hidayatullah.
                    </p>
                    <p class="text-xs text-brand-gold">Premium Quality Products</p>
                </div>
            </div>
        </footer>

        <!-- Mobile Sticky Add to Cart Footer dengan iOS Glass Effect -->
        <div
            class="ios-glass fixed inset-x-0 bottom-16 z-40 border-t border-brand-blue-200/30 p-4 dark:border-brand-blue-800/30 md:hidden"
        >
            <div class="mx-auto max-w-7xl">
                <!-- Price & Quantity Row -->
                <div class="mb-3 flex items-center justify-between">
                    <p class="price-tag text-lg font-bold">
                        {{ formattedPrice }}
                    </p>
                    <!-- Quantity Selector dengan iOS press feedback -->
                    <div class="flex items-center gap-2">
                        <Motion
                            :animate="{ scale: isMinusPressed ? 0.9 : 1 }"
                            :transition="snappyTransition"
                        >
                        <Button
                            variant="outline"
                            size="icon"
                                class="h-11 w-11 rounded-xl"
                            :disabled="quantity <= 1 || !product.is_available"
                            aria-label="Kurangi jumlah"
                            @click="decrementQuantity"
                            @mousedown="isMinusPressed = true"
                            @mouseup="isMinusPressed = false"
                            @mouseleave="isMinusPressed = false"
                            @touchstart.passive="isMinusPressed = true"
                            @touchend="isMinusPressed = false"
                        >
                            <Minus class="h-4 w-4" />
                        </Button>
                        </Motion>

                        <Motion
                            :key="quantity"
                            :initial="{ scale: 1.2, opacity: 0 }"
                            :animate="{ scale: 1, opacity: 1 }"
                            :transition="bouncyTransition"
                            class="w-10 text-center text-lg font-semibold"
                        >
                            {{ quantity }}
                        </Motion>

                        <Motion
                            :animate="{ scale: isPlusPressed ? 0.9 : 1 }"
                            :transition="snappyTransition"
                        >
                        <Button
                            variant="outline"
                            size="icon"
                                class="h-11 w-11 rounded-xl"
                            :disabled="quantity >= 99 || !product.is_available"
                            aria-label="Tambah jumlah"
                            @click="incrementQuantity"
                            @mousedown="isPlusPressed = true"
                            @mouseup="isPlusPressed = false"
                            @mouseleave="isPlusPressed = false"
                            @touchstart.passive="isPlusPressed = true"
                            @touchend="isPlusPressed = false"
                        >
                            <Plus class="h-4 w-4" />
                        </Button>
                        </Motion>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <Motion
                    :animate="{ scale: isAddCartPressed ? 0.95 : 1 }"
                    :transition="snappyTransition"
                >
                <Button
                    size="lg"
                    :disabled="!product.is_available || isAdding"
                        class="ios-button h-13 w-full gap-2 rounded-2xl text-base shadow-lg"
                    :class="[
                        showSuccess
                            ? 'bg-green-600 hover:bg-green-600'
                            : 'bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 hover:from-brand-blue-600 hover:to-brand-blue-700 shadow-brand-blue-500/25',
                    ]"
                    @click="handleAddToCart"
                    @mousedown="isAddCartPressed = true"
                    @mouseup="isAddCartPressed = false"
                    @mouseleave="isAddCartPressed = false"
                    @touchstart.passive="isAddCartPressed = true"
                    @touchend="isAddCartPressed = false"
                >
                    <Loader2 v-if="isAdding" class="h-5 w-5 animate-spin" />
                        <AnimatePresence mode="wait">
                            <Motion
                                v-if="showSuccess"
                                :key="'success-mobile'"
                        :initial="{ scale: 0 }"
                                :animate="{ scale: 1 }"
                                :exit="{ scale: 0 }"
                                :transition="bouncyTransition"
                            >
                                <Check class="h-5 w-5" />
                            </Motion>
                            <Plus v-else-if="!isAdding" class="h-5 w-5" />
                        </AnimatePresence>
                    <span v-if="showSuccess">Ditambahkan!</span>
                    <span v-else-if="product.is_available">Tambah ke Keranjang</span>
                    <span v-else>Stok Habis</span>
                </Button>
                </Motion>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
