<script setup lang="ts">
/**
 * ProductDetail Page - Halaman Detail Produk
 * Menampilkan informasi lengkap produk termasuk gambar, deskripsi,
 * harga, dan produk terkait dari kategori yang sama
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { home } from '@/routes'
import { show } from '@/actions/App/Http/Controllers/ProductController'
import ProductCard from '@/components/store/ProductCard.vue'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    ShoppingBag,
    ShoppingCart,
    ChevronRight,
    ArrowLeft,
    Plus,
    Check,
} from 'lucide-vue-next'

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
    category?: {
        id: number
        name: string
        slug: string
    }
    is_available: boolean
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
}

const props = defineProps<Props>()

/**
 * Computed property untuk data produk yang di-unwrap dari resource
 */
const product = computed(() => props.product.data)

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
 * Handler untuk tombol add to cart
 * Akan diimplementasikan di CUST-005
 */
function handleAddToCart() {
    if (!product.value.is_available) return
    console.log('Add to cart:', product.value.id)
}

/**
 * Handler untuk navigasi kembali ke katalog
 */
function handleBack() {
    router.visit(home())
}

/**
 * Handler untuk add to cart dari related products
 */
function handleRelatedAddToCart(productId: number) {
    console.log('Add to cart from related:', productId)
}
</script>

<template>
    <Head :title="`${product.name} - Simple Store`">
        <meta name="description" :content="product.description ?? `Beli ${product.name} dengan harga terbaik`" />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <ShoppingBag class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <span class="text-xl font-bold text-foreground">Simple Store</span>
                </Link>

                <!-- Auth Navigation -->
                <nav class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb Navigation -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
                <Link :href="home()" class="hover:text-foreground transition-colors">
                    Beranda
                </Link>
                <ChevronRight class="h-4 w-4" />
                <template v-if="product.category">
                    <Link
                        :href="`/?category=${product.category.id}`"
                        class="hover:text-foreground transition-colors"
                    >
                        {{ product.category.name }}
                    </Link>
                    <ChevronRight class="h-4 w-4" />
                </template>
                <span class="text-foreground font-medium truncate max-w-[200px]">
                    {{ product.name }}
                </span>
            </nav>

            <!-- Back Button -->
            <Button
                variant="ghost"
                size="sm"
                class="mb-6 flex items-center gap-2"
                @click="handleBack"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Katalog
            </Button>

            <!-- Product Detail Section -->
            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Product Image -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card">
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
                            <ShoppingCart class="h-24 w-24 text-muted-foreground/50" />
                        </div>
                    </div>

                    <!-- Availability Badge -->
                    <Badge
                        v-if="!product.is_available"
                        variant="destructive"
                        class="absolute left-4 top-4 text-sm"
                    >
                        Stok Habis
                    </Badge>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col">
                    <!-- Category -->
                    <p
                        v-if="product.category"
                        class="mb-2 text-sm font-medium uppercase tracking-wider text-muted-foreground"
                    >
                        {{ product.category.name }}
                    </p>

                    <!-- Product Name -->
                    <h1 class="mb-4 text-3xl font-bold tracking-tight text-foreground lg:text-4xl">
                        {{ product.name }}
                    </h1>

                    <!-- Price -->
                    <div class="mb-6">
                        <p class="text-3xl font-bold text-primary">
                            {{ formattedPrice }}
                        </p>
                    </div>

                    <!-- Availability Status -->
                    <div class="mb-6 flex items-center gap-2">
                        <div
                            :class="[
                                'flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium',
                                product.is_available
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                            ]"
                        >
                            <Check v-if="product.is_available" class="h-4 w-4" />
                            <span>{{ product.is_available ? 'Tersedia' : 'Tidak Tersedia' }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-8">
                        <h2 class="mb-3 text-lg font-semibold text-foreground">Deskripsi</h2>
                        <p
                            v-if="product.description"
                            class="leading-relaxed text-muted-foreground whitespace-pre-line"
                        >
                            {{ product.description }}
                        </p>
                        <p v-else class="text-muted-foreground italic">
                            Belum ada deskripsi untuk produk ini.
                        </p>
                    </div>

                    <!-- Add to Cart Button -->
                    <div class="mt-auto">
                        <Button
                            size="lg"
                            :disabled="!product.is_available"
                            class="w-full gap-2 text-base"
                            @click="handleAddToCart"
                        >
                            <Plus class="h-5 w-5" />
                            {{ product.is_available ? 'Tambah ke Keranjang' : 'Stok Habis' }}
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
                            @add-to-cart="handleRelatedAddToCart"
                        />
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-border bg-muted/30">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

