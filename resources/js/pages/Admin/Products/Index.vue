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
import PasswordConfirmDialog from '@/components/admin/PasswordConfirmDialog.vue'
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
const showPasswordDialog = ref(false)
const productToDelete = ref<Product | null>(null)
const isDeleting = ref(false)


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
 * Buka dialog konfirmasi delete - tampilkan preview
 */
function confirmDelete(product: Product) {
    haptic.warning()
    productToDelete.value = product
    showDeleteDialog.value = true
}

/**
 * Lanjut ke verifikasi password
 */
function proceedToPasswordConfirm() {
    showDeleteDialog.value = false
    showPasswordDialog.value = true
}

/**
 * Eksekusi delete produk setelah password terverifikasi
 */
function executeDelete() {
    if (!productToDelete.value) return

    haptic.heavy()
    isDeleting.value = true
    router.delete(destroy(productToDelete.value.id).url, {
        onSuccess: () => {
            haptic.success()
        },
        onError: () => {
            haptic.error()
        },
        onFinish: () => {
            isDeleting.value = false
            showPasswordDialog.value = false
            productToDelete.value = null
        },
    })
}

/**
 * Handle password dialog cancel
 */
function handlePasswordCancel() {
    showPasswordDialog.value = false
    productToDelete.value = null
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

                <!-- Mobile Product Cards -->
                <div class="flex flex-col gap-3 md:hidden">
                    <Motion
                        v-for="(product, index) in products.data"
                        :key="product.id"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: 0.1 + index * 0.03 }"
                    >
                        <div class="overflow-hidden rounded-2xl border bg-card shadow-sm">
                            <!-- Product Header with Image -->
                            <div class="flex gap-4 p-4">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-muted">
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
                                        <ImageOff class="h-6 w-6 text-muted-foreground" />
                                    </div>
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col justify-center">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-semibold leading-tight line-clamp-2">{{ product.name }}</h3>
                                        <span
                                            v-if="product.is_featured"
                                            class="shrink-0 rounded-full bg-amber-100 p-1 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400"
                                        >
                                            <Star class="h-3.5 w-3.5 fill-current" />
                                        </span>
                                    </div>
                                    <p v-if="product.category" class="mt-1 text-sm text-muted-foreground">
                                        {{ product.category.name }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-3">
                                        <PriceDisplay :price="product.price" size="md" class="font-bold text-primary" />
                                        <span
                                            :class="[
                                                'admin-badge tabular-nums',
                                                product.stock > 10
                                                    ? 'admin-badge--success'
                                                    : product.stock > 0
                                                      ? 'admin-badge--pending'
                                                      : 'admin-badge--destructive',
                                            ]"
                                        >
                                            Stok: {{ product.stock }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Footer Actions -->
                            <div class="flex items-center justify-between border-t bg-muted/30 px-4 py-3">
                                <span
                                    :class="[
                                        'admin-badge',
                                        product.is_active ? 'admin-badge--success' : 'bg-muted text-muted-foreground',
                                    ]"
                                >
                                    {{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <Link :href="edit(product.id).url">
                                        <Button variant="outline" size="sm" class="h-10 gap-2">
                                            <Pencil class="h-4 w-4" />
                                            Edit
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-10 gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                        @click="confirmDelete(product)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Hapus
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Mobile Empty State -->
                    <div v-if="products.data.length === 0" class="admin-form-section">
                        <div class="admin-empty-state">
                            <div class="icon-wrapper">
                                <Package />
                            </div>
                            <h3>Belum Ada Produk</h3>
                            <p>Mulai tambahkan produk pertama Anda</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div
                        v-if="products.data?.length > 0 && products.last_page > 1"
                        class="flex items-center justify-between rounded-2xl border bg-card p-3"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-10 w-10 p-0"
                            :disabled="products.current_page === 1"
                            @click="goToPage(products.links[0]?.url)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            {{ products.current_page }} / {{ products.last_page }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-10 w-10 p-0"
                            :disabled="products.current_page === products.last_page"
                            @click="goToPage(products.links[products.links.length - 1]?.url)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Desktop Products Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="hidden md:block"
                >
                    <div class="rounded-2xl border border-border/50 bg-card shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="min-width: 280px;">
                                            Produk
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 150px;">
                                            Kategori
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 120px;">
                                            Harga
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 80px;">
                                            Stok
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 100px;">
                                            Status
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 140px;">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <tr
                                        v-for="product in products.data"
                                        :key="product.id"
                                        class="group transition-colors hover:bg-muted/30"
                                    >
                                        <!-- Product Info -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-4">
                                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-muted">
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
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-foreground">{{ product.name }}</span>
                                                    <div v-if="product.is_featured" class="mt-0.5 flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                                                        <Star class="h-3 w-3 fill-current" />
                                                        Featured
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="px-4 py-3">
                                            <span v-if="product.category" class="text-sm text-foreground">
                                                {{ product.category.name }}
                                            </span>
                                            <span v-else class="text-sm text-muted-foreground">-</span>
                                        </td>

                                        <!-- Price -->
                                        <td class="px-4 py-3 text-right">
                                            <PriceDisplay :price="product.price" size="sm" class="font-semibold text-primary" />
                                        </td>

                                        <!-- Stock -->
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                :class="[
                                                    'admin-badge tabular-nums',
                                                    product.stock > 10
                                                        ? 'admin-badge--success'
                                                        : product.stock > 0
                                                          ? 'admin-badge--pending'
                                                          : 'admin-badge--destructive',
                                                ]"
                                            >
                                                {{ product.stock }}
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                :class="[
                                                    'admin-badge',
                                                    product.is_active ? 'admin-badge--success' : 'bg-muted text-muted-foreground',
                                                ]"
                                            >
                                                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <Link :href="edit(product.id).url">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-8 gap-1.5"
                                                    >
                                                        <Pencil class="h-3.5 w-3.5" />
                                                        Edit
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-8 gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                    @click="confirmDelete(product)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                    Hapus
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="6" class="px-4 py-16">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4 rounded-2xl bg-muted p-4">
                                                    <Package class="h-10 w-10 text-muted-foreground/50" />
                                                </div>
                                                <h3 class="text-lg font-semibold">Belum Ada Produk</h3>
                                                <p class="mt-1 text-sm text-muted-foreground">Mulai tambahkan produk pertama Anda</p>
                                                <Link :href="create().url" class="mt-4" @click="handleAddClick">
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

                        <!-- Desktop Pagination -->
                        <div
                            v-if="products.last_page > 1"
                            class="flex items-center justify-between border-t border-border/50 px-4 py-3"
                        >
                            <p class="text-sm text-muted-foreground">
                                Menampilkan <span class="font-medium text-foreground">{{ products.from }}</span> - <span class="font-medium text-foreground">{{ products.to }}</span> dari <span class="font-medium text-foreground">{{ products.total }}</span> produk
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="products.current_page === 1"
                                    @click="goToPage(products.links[0]?.url)"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Prev
                                </Button>
                                <span class="rounded-lg bg-muted px-3 py-1 text-sm font-medium tabular-nums">
                                    {{ products.current_page }} / {{ products.last_page }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="products.current_page === products.last_page"
                                    @click="goToPage(products.links[products.links.length - 1]?.url)"
                                >
                                    Next
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

    <!-- Delete Confirmation Dialog with Product Preview -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-destructive">
                    <Trash2 class="h-5 w-5" />
                    Hapus Produk
                </DialogTitle>
                <DialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Produk akan dihapus permanen.
                </DialogDescription>
            </DialogHeader>

            <!-- Product Preview -->
            <div v-if="productToDelete" class="my-4 rounded-xl border bg-muted/30 p-4">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-muted">
                        <img
                            v-if="productToDelete.image"
                            :src="getImageUrl(productToDelete.image)"
                            :alt="productToDelete.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <ImageOff class="h-6 w-6 text-muted-foreground" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-semibold">{{ productToDelete.name }}</h4>
                        <p v-if="productToDelete.category" class="text-sm text-muted-foreground">
                            {{ productToDelete.category.name }}
                        </p>
                        <div class="mt-1 flex items-center gap-3">
                            <PriceDisplay :price="productToDelete.price" size="sm" class="font-medium" />
                            <span class="text-sm text-muted-foreground">
                                Stok: {{ productToDelete.stock }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex-col gap-2 sm:flex-row">
                <Button
                    variant="outline"
                    class="w-full sm:w-auto"
                    @click="showDeleteDialog = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    class="w-full gap-2 sm:w-auto"
                    @click="proceedToPasswordConfirm"
                >
                    <Trash2 class="h-4 w-4" />
                    Ya, Hapus Produk
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Password Confirmation Dialog -->
    <PasswordConfirmDialog
        v-model:open="showPasswordDialog"
        title="Konfirmasi Hapus Produk"
        :description="`Masukkan password untuk menghapus produk '${productToDelete?.name}'`"
        confirm-label="Hapus Permanen"
        confirm-variant="destructive"
        :loading="isDeleting"
        @confirm="executeDelete"
        @cancel="handlePasswordCancel"
    />
</template>
