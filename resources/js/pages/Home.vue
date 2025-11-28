<script setup lang="ts">
/**
 * Home Page - Katalog Produk
 * Menampilkan daftar produk aktif dalam format grid responsive
 * dengan iOS-like animations, pull-to-refresh, staggered entrance,
 * filter kategori, pencarian, dan bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { login, register } from '@/routes'
import { dashboard } from '@/routes/admin'
import ProductCard from '@/components/store/ProductCard.vue'
import CategoryFilter from '@/components/store/CategoryFilter.vue'
import SearchBar from '@/components/store/SearchBar.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { ShoppingBag, X, Menu } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

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
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Local state untuk search input yang di-sync dengan prop searchQuery
 */
const localSearchQuery = ref(props.searchQuery ?? '')

/**
 * Mobile menu state untuk hamburger navigation
 */
const isMobileMenuOpen = ref(false)

/**
 * Menu button press state untuk iOS-like feedback
 */
const isMenuPressed = ref(false)

/**
 * Toggle mobile menu visibility
 */
function toggleMobileMenu() {
    haptic.medium()
    isMobileMenuOpen.value = !isMobileMenuOpen.value
}

/**
 * Close mobile menu
 */
function closeMobileMenu() {
    isMobileMenuOpen.value = false
}

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
    haptic.selection()
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
    haptic.light()
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
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-border/30">
                <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                    <!-- Logo & Brand dengan spring animation -->
                    <div
                        v-motion
                        :initial="{ opacity: 0, x: -20 }"
                        :enter="{
                            opacity: 1,
                            x: 0,
                            transition: {
                                type: 'spring',
                                stiffness: 300,
                                damping: 25,
                            },
                        }"
                        class="flex items-center gap-2 sm:gap-3"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-sm sm:h-10 sm:w-10">
                            <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                        </div>
                        <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                    </div>

                    <!-- Desktop Navigation dengan fade in -->
                    <nav
                        v-motion
                        :initial="{ opacity: 0, x: 20 }"
                        :enter="{
                            opacity: 1,
                            x: 0,
                            transition: {
                                type: 'spring',
                                stiffness: 300,
                                damping: 25,
                                delay: 100,
                            },
                        }"
                        class="hidden items-center gap-3 sm:flex"
                    >
                        <CartCounter :count="cartTotalItems" />

                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="ios-button rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="ios-button rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                            >
                                Masuk
                            </Link>
                            <Link
                                :href="register()"
                                class="ios-button rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                            >
                                Daftar
                            </Link>
                        </template>
                    </nav>

                    <!-- Mobile Navigation -->
                    <div class="flex items-center gap-2 sm:hidden">
                        <CartCounter :count="cartTotalItems" />
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-11 w-11 transition-transform duration-150 ease-[var(--ios-spring-snappy)]"
                            :class="{ 'scale-90': isMenuPressed }"
                            aria-label="Menu navigasi"
                            @click="toggleMobileMenu"
                            @mousedown="isMenuPressed = true"
                            @mouseup="isMenuPressed = false"
                            @mouseleave="isMenuPressed = false"
                            @touchstart.passive="isMenuPressed = true"
                            @touchend="isMenuPressed = false"
                        >
                            <Menu class="h-5 w-5" />
                        </Button>
                    </div>
                </div>

                <!-- Mobile Menu Dropdown dengan slide animation -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-[var(--ios-spring-snappy)]"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div
                        v-if="isMobileMenuOpen"
                        class="border-t border-border/50 bg-background/95 px-4 py-4 backdrop-blur sm:hidden"
                    >
                        <div class="flex flex-col gap-2">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="dashboard()"
                                class="ios-button flex h-12 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm"
                                @click="closeMobileMenu"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="login()"
                                    class="ios-button flex h-12 items-center justify-center rounded-xl border border-border bg-background px-4 text-sm font-medium text-foreground"
                                    @click="closeMobileMenu"
                                >
                                    Masuk
                                </Link>
                                <Link
                                    :href="register()"
                                    class="ios-button flex h-12 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm"
                                    @click="closeMobileMenu"
                                >
                                    Daftar
                                </Link>
                            </template>
                        </div>
                    </div>
                </Transition>
            </header>

            <!-- Spacer untuk fixed header -->
            <div class="h-14 sm:h-16" />

            <!-- Main Content dengan animations -->
            <PullToRefresh>
            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <!-- Search Bar Section dengan slide up animation -->
                <div
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{
                        opacity: 1,
                        y: 0,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 50,
                        },
                    }"
                    class="mb-5 sm:mb-6"
                >
                    <SearchBar
                        v-model="localSearchQuery"
                        placeholder="Cari produk..."
                        :debounce="400"
                        class="w-full sm:max-w-md"
                        @search="handleSearch"
                    />
                </div>

                <!-- Page Title dengan slide up animation -->
                <div
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{
                        opacity: 1,
                        y: 0,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 100,
                        },
                    }"
                    class="mb-5 sm:mb-6"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ pageHeading }}
                            </h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                                {{ pageDescription }}
                            </p>
                        </div>
                        <!-- Clear Search Button dengan spring animation -->
                        <Button
                            v-if="props.searchQuery"
                            v-motion
                            :initial="{ scale: 0, opacity: 0 }"
                            :enter="{
                                scale: 1,
                                opacity: 1,
                                transition: {
                                    type: 'spring',
                                    stiffness: 400,
                                    damping: 20,
                                },
                            }"
                            variant="outline"
                            size="default"
                            class="ios-button flex h-11 w-full items-center justify-center gap-2 rounded-xl sm:h-10 sm:w-auto"
                            @click="handleClearSearch"
                        >
                            <X class="h-4 w-4" />
                            Hapus Pencarian
                        </Button>
                    </div>
                </div>

                <!-- Category Filter dengan iOS horizontal scroll -->
                <div
                    v-if="categories.data.length > 0"
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{
                        opacity: 1,
                        y: 0,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 150,
                        },
                    }"
                    class="mb-6 sm:mb-8"
                >
                    <CategoryFilter
                        :categories="categories.data"
                        :active-category="selectedCategory"
                        @select="handleCategorySelect"
                    />
                </div>

                <!-- Products Grid dengan staggered animations -->
                <div
                    v-if="products.data.length > 0"
                    class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4 xl:gap-6"
                >
                    <ProductCard
                        v-for="(product, index) in products.data"
                        :key="product.id"
                        :product="product"
                        :index="index"
                        mode="grid"
                    />
                </div>

                <!-- Empty State dengan fade animation -->
                <div
                    v-if="products.data.length === 0"
                    v-motion
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :enter="{
                        opacity: 1,
                        scale: 1,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 200,
                        },
                    }"
                >
                    <EmptyState
                        :icon="props.searchQuery ? '🔍' : '🛒'"
                        :title="emptyStateTitle"
                        :description="emptyStateDescription"
                    />
                </div>
            </main>

            <!-- Footer - Hidden on mobile karena ada bottom nav -->
            <footer class="hidden border-t border-border bg-muted/30 md:block">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-muted-foreground">
                        <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                    </div>
                </div>
            </footer>
            </PullToRefresh>

            <!-- Bottom padding untuk mobile nav -->
            <div class="h-20 md:hidden" />
        </div>

        <!-- Mobile Bottom Navigation (Outside main content untuk fixed positioning) -->
        <UserBottomNav />
</template>
