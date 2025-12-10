<script setup lang="ts">
/**
 * Admin Products Index Page
 * Menampilkan daftar produk dengan iOS-style premium design, yaitu:
 * - Premium filter section dengan search dan dropdowns
 * - iOS grouped table dengan product thumbnails
 * - Animated rows dengan press feedback
 * - Premium pagination
 * - Mobile FAB untuk quick add
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as productsIndex, create, edit, destroy } from '@/routes/admin/products'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    Package,
    Plus,
    Search,
    Pencil,
    Trash2,
    Filter,
    ChevronLeft,
    ChevronRight,
    ImageOff,
    Star,
    X,
} from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

interface Category {
    id: number
    name: string
    slug: string
}

interface Product {
    id: number
    name: string
    slug: string
    description: string | null
    price: number
    stock: number
    image: string | null
    is_active: boolean
    is_featured: boolean
    category: Category | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedProducts {
    data: Product[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links: PaginationLink[]
}

interface Filters {
    search?: string
    category_id?: string
    is_active?: string
}

interface Props {
    products: PaginatedProducts
    categories: Category[]
    filters: Filters
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Produk', href: productsIndex().url },
]

// Flash messages dari session
const flashSuccess = computed(() => (page.props as unknown as { flash?: { success?: string } }).flash?.success)
const flashError = computed(() => (page.props as unknown as { flash?: { error?: string } }).flash?.error)

// Local state untuk filter
const search = ref(props.filters.search || '')
const categoryId = ref(props.filters.category_id || '')
const isActive = ref(props.filters.is_active || '')

// Dialog delete
const showDeleteDialog = ref(false)
const productToDelete = ref<Product | null>(null)
const isDeleting = ref(false)

// Press state untuk iOS-like feedback
const pressedRow = ref<number | null>(null)

/**
 * Has active filters
 */
const hasActiveFilters = computed(() => {
    return search.value || categoryId.value || isActive.value
})

/**
 * Debounced search function
 */
const debouncedSearch = useDebounceFn(() => {
    applyFilters()
}, 300)

watch(search, () => {
    debouncedSearch()
})

/**
 * Apply filters
 */
function applyFilters() {
    haptic.selection()
    router.get(
        productsIndex().url,
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
            is_active: isActive.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

/**
 * Reset semua filter
 */
function resetFilters() {
    haptic.medium()
    search.value = ''
    categoryId.value = ''
    isActive.value = ''
    router.get(productsIndex().url)
}

/**
 * Buka dialog konfirmasi delete
 */
function confirmDelete(product: Product) {
    haptic.warning()
    productToDelete.value = product
    showDeleteDialog.value = true
}

/**
 * Eksekusi delete produk
 */
function deleteProduct() {
    if (!productToDelete.value) return

    haptic.heavy()
    isDeleting.value = true
    router.delete(destroy(productToDelete.value.id).url, {
        onFinish: () => {
            isDeleting.value = false
            showDeleteDialog.value = false
            productToDelete.value = null
        },
    })
}

/**
 * Navigate ke halaman pagination
 */
function goToPage(url: string | null) {
    if (url) {
        haptic.light()
        router.get(url, {}, { preserveState: true, preserveScroll: true })
    }
}

/**
 * Get image URL
 * Mengembalikan undefined jika tidak ada image untuk kompatibilitas dengan Vue template
 */
function getImageUrl(image: string | null): string | undefined {
    if (!image) return undefined
    return `/storage/${image}`
}

/**
 * Handle row press
 */
function handleRowPress(productId: number) {
    pressedRow.value = productId
    haptic.light()
}

/**
 * Handle row release
 */
function handleRowRelease() {
    pressedRow.value = null
}

/**
 * Handle add product click
 */
function handleAddClick() {
    haptic.medium()
}
</script>

<template>
    <Head title="Manajemen Produk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                                Produk
                            </h1>
                            <Badge variant="secondary" class="tabular-nums">
                                {{ products.total }} total
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">
                            Kelola produk F&B toko Anda
                        </p>
                    </div>
                    <Link :href="create().url" class="hidden md:block" @click="handleAddClick">
                        <Button class="admin-btn-primary gap-2">
                            <Plus class="h-4 w-4" />
                            Tambah Produk
                        </Button>
                    </Link>
                </Motion>

                <!-- Flash Messages -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashSuccess"
                        class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        {{ flashSuccess }}
                    </div>
                </Transition>
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashError"
                        class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {{ flashError }}
                    </div>
                </Transition>

                <!-- Filters -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                >
                    <div class="admin-form-section">
                        <div class="admin-form-section-header">
                            <h3>
                                <Filter />
                                Filter & Pencarian
                            </h3>
                        </div>
                        <div class="admin-form-section-content">
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <!-- Search Input -->
                                <div class="relative flex-1">
                                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        v-model="search"
                                        type="text"
                                        placeholder="Cari produk..."
                                        class="admin-input pl-10"
                                    />
                                </div>

                                <!-- Category Filter -->
                                <select
                                    v-model="categoryId"
                                    class="admin-select w-full sm:w-48"
                                    @change="applyFilters"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>

                                <!-- Status Filter -->
                                <select
                                    v-model="isActive"
                                    class="admin-select w-full sm:w-40"
                                    @change="applyFilters"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>

                                <!-- Reset Button -->
                                <Button
                                    v-if="hasActiveFilters"
                                    variant="outline"
                                    class="ios-button"
                                    @click="resetFilters"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Products Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                >
                    <div class="ios-grouped-table">
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <Motion
                                        v-for="(product, index) in products.data"
                                        :key="product.id"
                                        tag="tr"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                        :class="{ 'scale-[0.995] bg-muted/60': pressedRow === product.id }"
                                        @mousedown="handleRowPress(product.id)"
                                        @mouseup="handleRowRelease"
                                        @mouseleave="handleRowRelease"
                                        @touchstart.passive="handleRowPress(product.id)"
                                        @touchend="handleRowRelease"
                                    >
                                        <!-- Product Info -->
                                        <td>
                                            <div class="flex items-center gap-4">
                                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-muted">
                                                    <img
                                                        v-if="product.image"
                                                        :src="getImageUrl(product.image)"
                                                        :alt="product.name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="flex h-full w-full items-center justify-center"
                                                    >
                                                        <ImageOff class="h-5 w-5 text-muted-foreground" />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <span class="font-semibold">{{ product.name }}</span>
                                                    <div v-if="product.is_featured" class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                                                        <Star class="h-3 w-3 fill-current" />
                                                        Featured
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td>
                                            <span v-if="product.category" class="text-sm">
                                                {{ product.category.name }}
                                            </span>
                                            <span v-else class="text-sm text-muted-foreground">-</span>
                                        </td>

                                        <!-- Price -->
                                        <td class="text-right">
                                            <PriceDisplay :price="product.price" size="sm" class="font-semibold" />
                                        </td>

                                        <!-- Stock -->
                                        <td class="text-center">
                                            <Badge
                                                :class="[
                                                    'tabular-nums',
                                                    product.stock > 10
                                                        ? 'admin-badge--success'
                                                        : product.stock > 0
                                                          ? 'admin-badge--pending'
                                                          : 'admin-badge--destructive',
                                                ]"
                                            >
                                                {{ product.stock }}
                                            </Badge>
                                        </td>

                                        <!-- Status -->
                                        <td class="text-center">
                                            <span
                                                :class="[
                                                    'admin-badge',
                                                    product.is_active ? 'admin-badge--success' : 'bg-muted text-muted-foreground',
                                                ]"
                                            >
                                                {{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Link :href="edit(product.id).url">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="ios-button h-9 w-9 p-0"
                                                    >
                                                        <Pencil class="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="ios-button h-9 w-9 p-0 text-destructive hover:text-destructive"
                                                    @click="confirmDelete(product)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </Motion>

                                    <!-- Empty State -->
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="6">
                                            <div class="admin-empty-state">
                                                <div class="icon-wrapper">
                                                    <Package />
                                                </div>
                                                <h3>Belum Ada Produk</h3>
                                                <p>Mulai tambahkan produk pertama Anda</p>
                                                <Link :href="create().url" @click="handleAddClick">
                                                    <Button class="admin-btn-primary gap-2">
                                                        <Plus class="h-4 w-4" />
                                                        Tambah Produk
                                                    </Button>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="products.last_page > 1"
                            class="flex items-center justify-between border-t px-4 py-3"
                        >
                            <p class="text-sm text-muted-foreground">
                                Menampilkan {{ products.from }} - {{ products.to }} dari {{ products.total }} produk
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="ios-button"
                                    :disabled="products.current_page === 1"
                                    @click="goToPage(products.links[0]?.url)"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <span class="text-sm tabular-nums text-muted-foreground">
                                    {{ products.current_page }} / {{ products.last_page }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="ios-button"
                                    :disabled="products.current_page === products.last_page"
                                    @click="goToPage(products.links[products.links.length - 1]?.url)"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-24 md:hidden" />
            </div>
        </PullToRefresh>

        <!-- Mobile FAB -->
        <Link :href="create().url" class="admin-fab md:hidden" @click="handleAddClick">
            <Plus />
        </Link>
    </AppLayout>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="rounded-2xl">
            <DialogHeader>
                <DialogTitle>Hapus Produk</DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin menghapus produk "{{ productToDelete?.name }}"?
                    Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    class="ios-button"
                    @click="showDeleteDialog = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    class="ios-button"
                    :disabled="isDeleting"
                    @click="deleteProduct"
                >
                    {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
