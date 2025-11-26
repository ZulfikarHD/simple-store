<script setup lang="ts">
/**
 * Admin Products Index Page
 * Menampilkan daftar produk dengan fitur pagination, search, dan filter, yaitu:
 * - Tabel data produk dengan kolom nama, kategori, harga, stok, status
 * - Search bar untuk pencarian berdasarkan nama
 * - Filter dropdown untuk kategori dan status
 * - Pagination untuk navigasi halaman
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as productsIndex, create, edit, destroy } from '@/routes/admin/products'
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
} from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'
import { useDebounceFn } from '@vueuse/core'

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
const flashSuccess = computed(() => page.props.flash?.success as string | undefined)
const flashError = computed(() => page.props.flash?.error as string | undefined)

// Local state untuk filter
const search = ref(props.filters.search || '')
const categoryId = ref(props.filters.category_id || '')
const isActive = ref(props.filters.is_active || '')

// Dialog delete
const showDeleteDialog = ref(false)
const productToDelete = ref<Product | null>(null)
const isDeleting = ref(false)

/**
 * Debounced search function untuk menghindari request berlebihan
 */
const debouncedSearch = useDebounceFn(() => {
    applyFilters()
}, 300)

watch(search, () => {
    debouncedSearch()
})

/**
 * Apply filters dengan redirect ke halaman yang sama dengan query params baru
 */
function applyFilters() {
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
    search.value = ''
    categoryId.value = ''
    isActive.value = ''
    router.get(productsIndex().url)
}

/**
 * Buka dialog konfirmasi delete
 */
function confirmDelete(product: Product) {
    productToDelete.value = product
    showDeleteDialog.value = true
}

/**
 * Eksekusi delete produk
 */
function deleteProduct() {
    if (!productToDelete.value) return

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
        router.get(url, {}, { preserveState: true, preserveScroll: true })
    }
}

/**
 * Get image URL atau placeholder
 */
function getImageUrl(image: string | null): string | null {
    if (!image) return null
    return `/storage/${image}`
}
</script>

<template>
    <Head title="Manajemen Produk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Manajemen Produk
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola produk F&B toko Anda
                    </p>
                </div>
                <Link :href="create().url">
                    <Button class="gap-2">
                        <Plus class="h-4 w-4" />
                        Tambah Produk
                    </Button>
                </Link>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="flashSuccess"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
            >
                {{ flashSuccess }}
            </div>
            <div
                v-if="flashError"
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
            >
                {{ flashError }}
            </div>

            <!-- Filters Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Filter class="h-4 w-4" />
                        Filter & Pencarian
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Cari produk..."
                                class="pl-10"
                            />
                        </div>

                        <!-- Category Filter -->
                        <select
                            v-model="categoryId"
                            class="h-10 rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
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
                            class="h-10 rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                            @change="applyFilters"
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>

                        <!-- Reset Button -->
                        <Button
                            variant="outline"
                            @click="resetFilters"
                        >
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Produk
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Kategori
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Harga
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Stok
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="product in products.data"
                                    :key="product.id"
                                    class="transition-colors hover:bg-muted/50"
                                >
                                    <!-- Product Info -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg bg-muted">
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
                                                <span class="font-medium">{{ product.name }}</span>
                                                <Badge
                                                    v-if="product.is_featured"
                                                    variant="secondary"
                                                    class="mt-1 w-fit text-xs"
                                                >
                                                    Featured
                                                </Badge>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="px-4 py-3">
                                        <span v-if="product.category" class="text-sm">
                                            {{ product.category.name }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">
                                            -
                                        </span>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-4 py-3 text-right">
                                        <PriceDisplay :price="product.price" size="sm" />
                                    </td>

                                    <!-- Stock -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge
                                            :variant="product.stock > 10 ? 'default' : product.stock > 0 ? 'secondary' : 'destructive'"
                                        >
                                            {{ product.stock }}
                                        </Badge>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge
                                            :variant="product.is_active ? 'default' : 'outline'"
                                            :class="product.is_active ? 'bg-green-600' : ''"
                                        >
                                            {{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </Badge>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="edit(product.id).url">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0"
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                @click="confirmDelete(product)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="products.data.length === 0">
                                    <td colspan="6" class="px-4 py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <Package class="mb-4 h-12 w-12 text-muted-foreground/50" />
                                            <p class="text-lg font-medium text-muted-foreground">
                                                Belum ada produk
                                            </p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Mulai tambahkan produk pertama Anda
                                            </p>
                                            <Link :href="create().url" class="mt-4">
                                                <Button>
                                                    <Plus class="mr-2 h-4 w-4" />
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
                                :disabled="products.current_page === 1"
                                @click="goToPage(products.links[0]?.url)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <span class="text-sm text-muted-foreground">
                                Halaman {{ products.current_page }} dari {{ products.last_page }}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="products.current_page === products.last_page"
                                @click="goToPage(products.links[products.links.length - 1]?.url)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent>
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
                    @click="showDeleteDialog = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    :disabled="isDeleting"
                    @click="deleteProduct"
                >
                    {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

