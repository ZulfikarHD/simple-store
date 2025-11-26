<script setup lang="ts">
/**
 * Home Page - Katalog Produk
 * Menampilkan daftar produk aktif dalam format grid responsive
 * dengan filter kategori, pencarian, empty state, cart counter,
 * dan navigasi ke halaman login/register
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { login, register } from '@/routes'
import { dashboard } from '@/routes/admin'
import ProductCard from '@/components/store/ProductCard.vue'
import CategoryFilter from '@/components/store/CategoryFilter.vue'
import SearchBar from '@/components/store/SearchBar.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import { ShoppingBag, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

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
    searchQuery: string | null
    cart?: {
        total_items: number
    }
}

const props = defineProps<Props>()

/**
 * Local state untuk search input yang di-sync dengan prop searchQuery
 */
const localSearchQuery = ref(props.searchQuery ?? '')

/**
 * Computed untuk cart total items
 */
const cartTotalItems = computed(() => props.cart?.total_items ?? 0)

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
 * Computed property untuk page title dinamis berdasarkan kategori dan pencarian
 */
const pageTitle = computed(() => {
    if (props.searchQuery) {
        return `Hasil Pencarian "${props.searchQuery}" - Katalog Produk`
    }
    return selectedCategoryName.value
        ? `${selectedCategoryName.value} - Katalog Produk`
        : 'Katalog Produk'
})

/**
 * Computed property untuk page description dinamis berdasarkan kategori dan pencarian
 */
const pageDescription = computed(() => {
    if (props.searchQuery) {
        return `Menampilkan ${props.products.data.length} hasil pencarian untuk "${props.searchQuery}"`
    }
    return selectedCategoryName.value
        ? `Temukan berbagai produk ${selectedCategoryName.value.toLowerCase()} berkualitas`
        : 'Temukan berbagai produk berkualitas untuk kebutuhan Anda'
})

/**
 * Computed property untuk heading dinamis berdasarkan pencarian atau kategori
 */
const pageHeading = computed(() => {
    if (props.searchQuery) {
        return `Hasil Pencarian "${props.searchQuery}"`
    }
    return selectedCategoryName.value ?? 'Katalog Produk'
})

/**
 * Computed property untuk empty state title
 */
const emptyStateTitle = computed(() => {
    if (props.searchQuery) {
        return `Tidak Ditemukan Hasil untuk "${props.searchQuery}"`
    }
    if (selectedCategoryName.value) {
        return `Tidak Ada Produk ${selectedCategoryName.value}`
    }
    return 'Belum Ada Produk'
})

/**
 * Computed property untuk empty state description
 */
const emptyStateDescription = computed(() => {
    if (props.searchQuery) {
        return 'Coba kata kunci lain atau hapus filter pencarian.'
    }
    if (selectedCategoryName.value) {
        return `Belum ada produk dalam kategori ${selectedCategoryName.value.toLowerCase()}. Silakan pilih kategori lain.`
    }
    return 'Produk sedang dalam persiapan. Silakan kembali lagi nanti.'
})

/**
 * Handler untuk event select dari CategoryFilter
 * Navigasi ke halaman dengan query parameter category, mempertahankan search jika ada
 */
function handleCategorySelect(categoryId: number | null) {
    const data: Record<string, string | number> = {}
    if (categoryId) {
        data.category = categoryId
    }
    if (props.searchQuery) {
        data.search = props.searchQuery
    }
    router.visit('/', {
        data,
        preserveState: true,
        preserveScroll: true,
    })
}

/**
 * Handler untuk event search dari SearchBar
 * Navigasi ke halaman dengan query parameter search, mempertahankan category jika ada
 */
function handleSearch(searchTerm: string) {
    const data: Record<string, string | number> = {}
    if (searchTerm) {
        data.search = searchTerm
    }
    if (props.selectedCategory) {
        data.category = props.selectedCategory
    }
    router.visit('/', {
        data,
        preserveState: true,
        preserveScroll: true,
    })
}

/**
 * Handler untuk clear search
 * Menghapus query parameter search dan kembali ke state sebelumnya
 */
function handleClearSearch() {
    localSearchQuery.value = ''
    const data: Record<string, number> = {}
    if (props.selectedCategory) {
        data.category = props.selectedCategory
    }
    router.visit('/', {
        data,
        preserveState: true,
        preserveScroll: true,
    })
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

                <!-- Cart Counter & Auth Navigation -->
                <nav class="flex items-center gap-3">
                    <CartCounter :count="cartTotalItems" />

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
            <!-- Search Bar Section -->
            <div class="mb-6">
                <SearchBar
                    v-model="localSearchQuery"
                    placeholder="Cari produk..."
                    :debounce="400"
                    class="max-w-md"
                    @search="handleSearch"
                />
            </div>

            <!-- Page Title -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-foreground">
                            {{ pageHeading }}
                        </h1>
                        <p class="mt-2 text-muted-foreground">
                            {{ pageDescription }}
                        </p>
                    </div>
                    <!-- Clear Search Button -->
                    <Button
                        v-if="props.searchQuery"
                        variant="outline"
                        size="sm"
                        class="flex items-center gap-2"
                        @click="handleClearSearch"
                    >
                        <X class="h-4 w-4" />
                        Hapus Pencarian
                    </Button>
                </div>
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
                    />
                </div>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-if="products.data.length === 0"
                :icon="props.searchQuery ? '🔍' : '🛒'"
                :title="emptyStateTitle"
                :description="emptyStateDescription"
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
