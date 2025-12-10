<script setup lang="ts">
/**
 * Home Page - Katalog Produk
 * Menampilkan daftar produk aktif dalam format grid responsive
 * dengan iOS-like animations menggunakan motion-v, pull-to-refresh,
 * staggered entrance, filter kategori, pencarian, dan bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { login, register } from '@/routes'
import { dashboard } from '@/routes/admin'
import { index as accountIndex } from '@/routes/account'
import ProductCard from '@/components/store/ProductCard.vue'
import CategoryFilter from '@/components/store/CategoryFilter.vue'
import SearchBar from '@/components/store/SearchBar.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import StoreHeader from '@/components/store/StoreHeader.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { ShoppingBag, X, Menu } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'

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
 * Page instance untuk mengakses auth user
 */
const page = usePage()

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Computed untuk cek apakah user adalah admin
 */
const isAdmin = computed(() => {
    const user = (page.props as { auth?: { user?: { role?: string } } }).auth?.user
    return user?.role === 'admin'
})

/**
 * Interface dan computed untuk store branding
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})

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

/**
 * Handler untuk logout user
 * Melakukan POST request ke endpoint logout dengan method POST
 */
function handleLogout() {
    haptic.medium()
    router.post('/logout')
}

/**
 * Spring transition untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
</script>

<template>
    <Head :title="pageTitle">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
                <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                    <!-- Logo & Brand dengan spring animation -->
                    <StoreHeader />

                    <!-- Desktop Navigation dengan fade in -->
                <Motion
                        :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    tag="nav"
                        class="hidden items-center gap-3 sm:flex"
                    >
                        <CartCounter :count="cartTotalItems" />

                        <template v-if="$page.props.auth.user">
                            <Link
                                v-if="isAdmin"
                                :href="dashboard()"
                                class="ios-button rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                            >
                                Dashboard
                            </Link>
                            <Link
                                v-else
                                :href="accountIndex()"
                                class="ios-button rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
                            >
                                Akun
                            </Link>
                            <button
                                class="ios-button rounded-xl border border-border px-4 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                                @click="handleLogout"
                            >
                                Keluar
                            </button>
                        </template>
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
                </Motion>

                    <!-- Mobile Navigation -->
                    <div class="flex items-center gap-2 sm:hidden">
                        <CartCounter :count="cartTotalItems" />
                    <Motion
                        :animate="{ scale: isMenuPressed ? 0.9 : 1 }"
                        :transition="{ type: 'spring', ...springPresets.snappy }"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-11 w-11"
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
                    </Motion>
                    </div>
                </div>

                <!-- Mobile Menu Dropdown dengan slide animation -->
            <AnimatePresence>
                <Motion
                    v-if="isMobileMenuOpen"
                    :initial="{ opacity: 0, y: -10, height: 0 }"
                    :animate="{ opacity: 1, y: 0, height: 'auto' }"
                    :exit="{ opacity: 0, y: -10, height: 0 }"
                    :transition="springTransition"
                    class="overflow-hidden border-t border-border/50 bg-background/95 backdrop-blur sm:hidden"
                    >
                    <div class="flex flex-col gap-2 px-4 py-4">
                            <template v-if="$page.props.auth.user">
                                <Link
                                    v-if="isAdmin"
                                    :href="dashboard()"
                                    class="ios-button flex h-12 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm"
                                    @click="closeMobileMenu"
                                >
                                    Dashboard
                                </Link>
                                <Link
                                    v-else
                                    :href="accountIndex()"
                                    class="ios-button flex h-12 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm"
                                    @click="closeMobileMenu"
                                >
                                    Akun
                                </Link>
                            </template>
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
                </Motion>
            </AnimatePresence>
            </header>

            <!-- Spacer untuk fixed header -->
            <div class="h-14 sm:h-16" />

            <!-- Main Content dengan animations -->
            <PullToRefresh>
            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <!-- Hero Section dengan gradient background -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springTransition"
                    class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-brand-blue-50 via-white to-brand-gold-50 p-5 sm:mb-8 sm:p-6 dark:from-brand-blue-900/30 dark:via-background dark:to-brand-gold-900/20"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                {{ pageHeading }}
                            </h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                                {{ pageDescription }}
                            </p>
                        </div>
                        <!-- Search Bar Section -->
                        <SearchBar
                            v-model="localSearchQuery"
                            placeholder="Cari produk..."
                            :debounce="400"
                            class="w-full sm:max-w-xs"
                            @search="handleSearch"
                        />
                    </div>
                </Motion>

                <!-- Page Actions dengan slide up animation -->
                <Motion
                    v-if="props.searchQuery"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    class="mb-5 sm:mb-6"
                >
                    <div class="flex items-center justify-end">
                        <Button
                            variant="outline"
                            size="default"
                            class="ios-button flex h-11 items-center justify-center gap-2 rounded-xl border-brand-blue-200 text-brand-blue hover:bg-brand-blue-50 dark:border-brand-blue-700 dark:hover:bg-brand-blue-900/30 sm:h-10"
                            @click="handleClearSearch"
                        >
                            <X class="h-4 w-4" />
                            Hapus Pencarian
                        </Button>
                    </div>
                </Motion>

                <!-- Category Filter dengan iOS horizontal scroll -->
                <Motion
                    v-if="categories.data.length > 0"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.15 }"
                    class="mb-6 sm:mb-8"
                >
                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm font-medium text-muted-foreground sm:block">Kategori:</span>
                        <CategoryFilter
                            :categories="categories.data"
                            :active-category="selectedCategory"
                            @select="handleCategorySelect"
                        />
                    </div>
                </Motion>

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
                <AnimatePresence>
                    <Motion
                    v-if="products.data.length === 0"
                    :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.95 }"
                        :transition="{ ...springTransition, delay: 0.2 }"
                >
                    <EmptyState
                        :icon="props.searchQuery ? '🔍' : '🛒'"
                        :title="emptyStateTitle"
                        :description="emptyStateDescription"
                    />
                    </Motion>
                </AnimatePresence>
            </main>
            </PullToRefresh>

            <!-- Footer - Hidden on mobile karena ada bottom nav, premium styling -->
            <footer class="hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background md:block">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="brand-logo h-8 w-8">
                                <img
                                    v-if="store.logo"
                                    :src="`/storage/${store.logo}`"
                                    :alt="store.name"
                                    class="h-full w-full rounded-xl object-contain"
                                />
                                <ShoppingBag v-else class="h-4 w-4 text-white" />
                            </div>
                            <span class="font-bold text-foreground">{{ store.name }}</span>
                        </div>
                        <p class="text-center text-sm text-muted-foreground">
                            &copy; {{ new Date().getFullYear() }} {{ store.name }}. Created By Zulfikar Hidayatullah.
                        </p>
                        <p class="text-xs text-brand-gold">{{ store.tagline }}</p>
                    </div>
                </div>
            </footer>

            <!-- Bottom padding untuk mobile nav -->
            <div class="h-20 md:hidden" />
        </div>

        <!-- Mobile Bottom Navigation (Outside main content untuk fixed positioning) -->
        <UserBottomNav />
</template>
