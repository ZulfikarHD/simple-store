<script setup lang="ts">
/**
 * ProductDetail Page - Halaman Detail Produk
 * Menampilkan informasi lengkap produk termasuk gambar, deskripsi,
 * harga, dan produk terkait dari kategori yang sama
 * dengan add to cart functionality dan mobile bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import { show } from '@/actions/App/Http/Controllers/ProductController'
import { store as storeCart } from '@/actions/App/Http/Controllers/CartController'
import { show as showCart } from '@/actions/App/Http/Controllers/CartController'
import ProductCard from '@/components/store/ProductCard.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
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
 * Handler untuk increment quantity
 */
function incrementQuantity() {
    if (quantity.value < 99) {
        quantity.value++
    }
}

/**
 * Handler untuk decrement quantity
 */
function decrementQuantity() {
    if (quantity.value > 1) {
        quantity.value--
    }
}

/**
 * Handler untuk tombol add to cart
 * Menggunakan Inertia router untuk submit ke cart
 */
function handleAddToCart() {
    if (!product.value.is_available || isAdding.value) return

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
    router.visit(home())
}
</script>

<template>
    <Head :title="`${product.name} - Simple Store`">
        <meta name="description" :content="product.description ?? `Beli ${product.name} dengan harga terbaik`" />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation - Mobile Optimized -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>

                <!-- Cart Counter & Auth Navigation - Hidden auth on mobile -->
                <nav class="flex items-center gap-2 sm:gap-3">
                    <CartCounter :count="cartTotalItems" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="hidden rounded-lg px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent sm:inline-flex"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content - Mobile Optimized dengan padding untuk sticky footer -->
        <main class="mx-auto max-w-7xl px-4 pb-28 pt-6 sm:px-6 sm:pb-8 sm:pt-8 lg:px-8">
            <!-- Breadcrumb Navigation - Hidden on mobile -->
            <nav class="mb-4 hidden items-center gap-2 text-sm text-muted-foreground sm:mb-6 sm:flex">
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
            </nav>

            <!-- Back Button - Touch-friendly -->
            <Button
                variant="ghost"
                size="default"
                class="mb-4 flex h-11 items-center gap-2 sm:mb-6 sm:h-9"
                @click="handleBack"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Katalog
            </Button>

            <!-- Product Detail Section - Mobile Optimized -->
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                <!-- Product Image -->
                <div class="relative overflow-hidden rounded-xl border border-border bg-card sm:rounded-2xl">
                    <div class="aspect-square">
                        <img
                            v-if="imageUrl"
                            :src="imageUrl"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-muted"
                        >
                            <ShoppingCart class="h-16 w-16 text-muted-foreground/50 sm:h-24 sm:w-24" />
                        </div>
                    </div>

                    <!-- Stock Status Badge on Image -->
                    <Badge
                        v-if="product.stock_status && (product.stock_status.status === 'out_of_stock' || product.stock_status.status === 'unavailable')"
                        variant="destructive"
                        class="absolute left-3 top-3 text-xs sm:left-4 sm:top-4 sm:text-sm"
                    >
                        {{ product.stock_status.label }}
                    </Badge>
                    <Badge
                        v-else-if="product.stock_status?.status === 'low_stock'"
                        class="absolute left-3 top-3 bg-amber-100 text-xs text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 sm:left-4 sm:top-4 sm:text-sm"
                    >
                        Stok Terbatas
                    </Badge>
                    <!-- Fallback -->
                    <Badge
                        v-else-if="!product.is_available"
                        variant="destructive"
                        class="absolute left-3 top-3 text-xs sm:left-4 sm:top-4 sm:text-sm"
                    >
                        Stok Habis
                    </Badge>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col">
                    <!-- Category -->
                    <p
                        v-if="product.category"
                        class="mb-1 text-xs font-medium uppercase tracking-wider text-muted-foreground sm:mb-2 sm:text-sm"
                    >
                        {{ product.category.name }}
                    </p>

                    <!-- Product Name -->
                    <h1 class="mb-3 text-2xl font-bold tracking-tight text-foreground sm:mb-4 sm:text-3xl lg:text-4xl">
                        {{ product.name }}
                    </h1>

                    <!-- Price -->
                    <div class="mb-4 sm:mb-6">
                        <p class="text-2xl font-bold text-primary sm:text-3xl">
                            {{ formattedPrice }}
                        </p>
                    </div>

                    <!-- Stock Status - Enhanced dengan multiple states -->
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:mb-6">
                        <!-- Stock Status Badge -->
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
                        <!-- Fallback untuk backward compatibility -->
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

                        <!-- Stock Count (only for low stock) -->
                        <span
                            v-if="product.stock_status?.status === 'low_stock'"
                            class="text-xs text-amber-600 dark:text-amber-400"
                        >
                            (Sisa {{ product.stock_status.stock }} unit)
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6 sm:mb-8">
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
                    </div>

                    <!-- Quantity Selector - Hidden on mobile (moved to sticky footer) -->
                    <div class="mt-auto hidden space-y-4 sm:block">
                        <!-- Quantity Selector -->
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-foreground">Jumlah:</span>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-10 w-10"
                                    :disabled="quantity <= 1 || !product.is_available"
                                    aria-label="Kurangi jumlah"
                                    @click="decrementQuantity"
                                >
                                    <Minus class="h-4 w-4" />
                                </Button>

                                <span class="w-12 text-center text-lg font-semibold">
                                    {{ quantity }}
                                </span>

                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-10 w-10"
                                    :disabled="quantity >= 99 || !product.is_available"
                                    aria-label="Tambah jumlah"
                                    @click="incrementQuantity"
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <Button
                            size="lg"
                            :disabled="!product.is_available || isAdding"
                            :class="{
                                'bg-green-600 hover:bg-green-600': showSuccess,
                            }"
                            class="h-12 w-full gap-2 text-base"
                            @click="handleAddToCart"
                        >
                            <Loader2 v-if="isAdding" class="h-5 w-5 animate-spin" />
                            <Check v-else-if="showSuccess" class="h-5 w-5" />
                            <Plus v-else class="h-5 w-5" />
                            <span v-if="showSuccess">Ditambahkan ke Keranjang!</span>
                            <span v-else-if="product.is_available">Tambah ke Keranjang</span>
                            <span v-else>Stok Habis</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <section v-if="relatedProducts.data.length > 0" class="mt-16">
                <h2 class="mb-6 text-2xl font-bold tracking-tight text-foreground">
                    Produk Terkait
                </h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="relatedProduct in relatedProducts.data"
                        :key="relatedProduct.id"
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md"
                    >
                        <ProductCard
                            :product="relatedProduct"
                            mode="grid"
                        />
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer - Hidden on mobile -->
        <footer class="mt-12 hidden border-t border-border bg-muted/30 sm:mt-16 md:block">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>

        <!-- Mobile Sticky Add to Cart Footer - positioned above bottom nav -->
        <div
            class="fixed inset-x-0 bottom-16 z-40 border-t border-border bg-background/95 p-4 backdrop-blur supports-[backdrop-filter]:bg-background/80 md:hidden"
        >
            <div class="mx-auto max-w-7xl">
                <!-- Price & Quantity Row -->
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-lg font-bold text-primary">
                        {{ formattedPrice }}
                    </p>
                    <!-- Quantity Selector -->
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-10 w-10"
                            :disabled="quantity <= 1 || !product.is_available"
                            aria-label="Kurangi jumlah"
                            @click="decrementQuantity"
                        >
                            <Minus class="h-4 w-4" />
                        </Button>

                        <span class="w-10 text-center text-lg font-semibold">
                            {{ quantity }}
                        </span>

                        <Button
                            variant="outline"
                            size="icon"
                            class="h-10 w-10"
                            :disabled="quantity >= 99 || !product.is_available"
                            aria-label="Tambah jumlah"
                            @click="incrementQuantity"
                        >
                            <Plus class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <Button
                    size="lg"
                    :disabled="!product.is_available || isAdding"
                    :class="{
                        'bg-green-600 hover:bg-green-600': showSuccess,
                    }"
                    class="h-12 w-full gap-2 text-base"
                    @click="handleAddToCart"
                >
                    <Loader2 v-if="isAdding" class="h-5 w-5 animate-spin" />
                    <Check v-else-if="showSuccess" class="h-5 w-5" />
                    <Plus v-else class="h-5 w-5" />
                    <span v-if="showSuccess">Ditambahkan!</span>
                    <span v-else-if="product.is_available">Tambah ke Keranjang</span>
                    <span v-else>Stok Habis</span>
                </Button>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
