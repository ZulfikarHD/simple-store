<script setup lang="ts">
/**
 * Home Page - Katalog Produk
 * Menampilkan daftar produk aktif dalam format grid responsive
 * dengan filter kategori, empty state, dan navigasi ke halaman login/register
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { dashboard, login, register } from '@/routes'
import ProductCard from '@/components/store/ProductCard.vue'
import CategoryFilter from '@/components/store/CategoryFilter.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import { ShoppingBag } from 'lucide-vue-next'

/**
 * Interface untuk data produk dari ProductResource
 * dengan category sebagai optional untuk kompatibilitas dengan ProductCard
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
    }
    is_available: boolean
}

/**
 * Interface untuk data kategori dari CategoryResource
 */
interface Category {
    id: number
    name: string
    slug: string
    description: string | null
    products_count: number
}

/**
 * Interface untuk response dari Resource::collection
 * yang membungkus data dalam property 'data'
 */
interface ProductCollection {
    data: Product[]
}

interface CategoryCollection {
    data: Category[]
}

/**
 * Props yang diterima dari ProductController
 */
interface Props {
    products: ProductCollection
    categories: CategoryCollection
    selectedCategory: number | null
}

const props = defineProps<Props>()

/**
 * Computed property untuk mendapatkan nama kategori yang dipilih
 * untuk ditampilkan pada page title dan description
 */
const selectedCategoryName = computed(() => {
    if (!props.selectedCategory) return null
    const category = props.categories.data.find(c => c.id === props.selectedCategory)
    return category?.name ?? null
})

/**
 * Computed property untuk page title dinamis berdasarkan kategori
 */
const pageTitle = computed(() => {
    return selectedCategoryName.value
        ? `${selectedCategoryName.value} - Katalog Produk`
        : 'Katalog Produk'
})

/**
 * Computed property untuk page description dinamis berdasarkan kategori
 */
const pageDescription = computed(() => {
    return selectedCategoryName.value
        ? `Temukan berbagai produk ${selectedCategoryName.value.toLowerCase()} berkualitas`
        : 'Temukan berbagai produk berkualitas untuk kebutuhan Anda'
})

/**
 * Handler untuk event select dari CategoryFilter
 * Navigasi ke halaman dengan query parameter category
 */
function handleCategorySelect(categoryId: number | null) {
    router.visit('/', {
        data: categoryId ? { category: categoryId } : {},
        preserveState: true,
        preserveScroll: true,
    })
}

/**
 * Handler untuk event add to cart dari ProductCard
 * Saat ini hanya log ke console, akan diimplementasikan di CUST-005
 */
function handleAddToCart(productId: number) {
    console.log('Add to cart:', productId)
}
</script>

<template>
    <Head :title="pageTitle">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <ShoppingBag class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <span class="text-xl font-bold text-foreground">Simple Store</span>
                </div>

                <!-- Auth Navigation -->
                <nav class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="register()"
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
            <!-- Page Title -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ selectedCategoryName ?? 'Katalog Produk' }}
                </h1>
                <p class="mt-2 text-muted-foreground">
                    {{ pageDescription }}
                </p>
            </div>

            <!-- Category Filter -->
            <div v-if="categories.data.length > 0" class="mb-8">
                <CategoryFilter
                    :categories="categories.data"
                    :active-category="selectedCategory"
                    @select="handleCategorySelect"
                />
            </div>

            <!-- Products Grid -->
            <div
                v-if="products.data.length > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md"
                >
                    <ProductCard
                        :product="product"
                        mode="grid"
                        @add-to-cart="handleAddToCart"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-if="products.data.length === 0"
                icon="🛒"
                :title="selectedCategoryName ? `Tidak Ada Produk ${selectedCategoryName}` : 'Belum Ada Produk'"
                :description="selectedCategoryName ? `Belum ada produk dalam kategori ${selectedCategoryName.toLowerCase()}. Silakan pilih kategori lain.` : 'Produk sedang dalam persiapan. Silakan kembali lagi nanti.'"
            />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-muted/30">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

