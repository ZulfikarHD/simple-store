<script setup lang="ts">
/**
 * Admin Orders Index Page
 * Menampilkan daftar pesanan dengan fitur pagination, search, dan filter, yaitu:
 * - Tabel data pesanan dengan kolom order number, customer, total, status, tanggal
 * - Search bar untuk pencarian berdasarkan order number, nama customer, atau nomor telepon
 * - Filter dropdown untuk status pesanan dan date range
 * - Pagination untuk navigasi halaman
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as ordersIndex, show } from '@/routes/admin/orders'
import {
    ShoppingBag,
    Search,
    Filter,
    ChevronLeft,
    ChevronRight,
    Eye,
    Phone,
    Calendar,
    Package,
} from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'
import { useDebounceFn } from '@vueuse/core'

interface OrderItem {
    id: number
    product_name: string
    quantity: number
    subtotal: number
}

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    total: number
    status: string
    items: OrderItem[]
    created_at: string
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedOrders {
    data: Order[]
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
    status?: string
    start_date?: string
    end_date?: string
}

interface Props {
    orders: PaginatedOrders
    statuses: Record<string, string>
    filters: Filters
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pesanan', href: ordersIndex().url },
]

// Flash messages dari session
const flashSuccess = computed(() => page.props.flash?.success as string | undefined)
const flashError = computed(() => page.props.flash?.error as string | undefined)

// Local state untuk filter
const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const startDate = ref(props.filters.start_date || '')
const endDate = ref(props.filters.end_date || '')

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
        ordersIndex().url,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
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
    status.value = ''
    startDate.value = ''
    endDate.value = ''
    router.get(ordersIndex().url)
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
 * Get status badge variant berdasarkan status order
 */
function getStatusVariant(orderStatus: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (orderStatus) {
        case 'pending':
            return 'secondary'
        case 'confirmed':
            return 'default'
        case 'preparing':
            return 'default'
        case 'ready':
            return 'default'
        case 'delivered':
            return 'default'
        case 'cancelled':
            return 'destructive'
        default:
            return 'outline'
    }
}

/**
 * Get status badge class untuk styling tambahan
 */
function getStatusClass(orderStatus: string): string {
    switch (orderStatus) {
        case 'pending':
            return 'bg-yellow-500 text-white hover:bg-yellow-600'
        case 'confirmed':
            return 'bg-blue-500 text-white hover:bg-blue-600'
        case 'preparing':
            return 'bg-purple-500 text-white hover:bg-purple-600'
        case 'ready':
            return 'bg-cyan-500 text-white hover:bg-cyan-600'
        case 'delivered':
            return 'bg-green-500 text-white hover:bg-green-600'
        case 'cancelled':
            return 'bg-red-500 text-white hover:bg-red-600'
        default:
            return ''
    }
}

/**
 * Format date untuk display
 */
function formatDate(dateString: string): string {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Open WhatsApp dengan nomor telepon customer
 */
function openWhatsApp(phone: string) {
    const cleanPhone = phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
}
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Manajemen Pesanan
                </h1>
                <p class="text-muted-foreground">
                    Kelola dan pantau pesanan customer
                </p>
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
                    <div class="flex flex-col gap-4">
                        <!-- Row 1: Search and Status -->
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <!-- Search Input -->
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari order number, nama, atau telepon..."
                                    class="pl-10"
                                />
                            </div>

                            <!-- Status Filter -->
                            <select
                                v-model="status"
                                class="h-10 rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option
                                    v-for="(label, value) in statuses"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Row 2: Date Range -->
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Calendar class="h-4 w-4" />
                                <span>Tanggal:</span>
                            </div>
                            <Input
                                v-model="startDate"
                                type="date"
                                class="w-full sm:w-auto"
                                @change="applyFilters"
                            />
                            <span class="text-muted-foreground">sampai</span>
                            <Input
                                v-model="endDate"
                                type="date"
                                class="w-full sm:w-auto"
                                @change="applyFilters"
                            />

                            <!-- Reset Button -->
                            <Button
                                variant="outline"
                                @click="resetFilters"
                            >
                                Reset
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Orders Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        No. Pesanan
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Customer
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Total
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Items
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Tanggal
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="transition-colors hover:bg-muted/50"
                                >
                                    <!-- Order Number -->
                                    <td class="px-4 py-3">
                                        <Link
                                            :href="show(order.id).url"
                                            class="font-mono text-sm font-medium text-primary hover:underline"
                                        >
                                            {{ order.order_number }}
                                        </Link>
                                    </td>

                                    <!-- Customer Info -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-medium">{{ order.customer_name }}</span>
                                            <button
                                                class="flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"
                                                @click="openWhatsApp(order.customer_phone)"
                                            >
                                                <Phone class="h-3 w-3" />
                                                {{ order.customer_phone }}
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Total -->
                                    <td class="px-4 py-3 text-right">
                                        <PriceDisplay :price="order.total" size="sm" />
                                    </td>

                                    <!-- Items Count -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge variant="outline" class="gap-1">
                                            <Package class="h-3 w-3" />
                                            {{ order.items.length }}
                                        </Badge>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge
                                            :variant="getStatusVariant(order.status)"
                                            :class="getStatusClass(order.status)"
                                        >
                                            {{ statuses[order.status] || order.status }}
                                        </Badge>
                                    </td>

                                    <!-- Created At -->
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm text-muted-foreground">
                                            {{ formatDate(order.created_at) }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="show(order.id).url">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 gap-1"
                                                >
                                                    <Eye class="h-4 w-4" />
                                                    Detail
                                                </Button>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="7" class="px-4 py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <ShoppingBag class="mb-4 h-12 w-12 text-muted-foreground/50" />
                                            <p class="text-lg font-medium text-muted-foreground">
                                                Belum ada pesanan
                                            </p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Pesanan customer akan muncul di sini
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="orders.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <p class="text-sm text-muted-foreground">
                            Menampilkan {{ orders.from }} - {{ orders.to }} dari {{ orders.total }} pesanan
                        </p>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="orders.current_page === 1"
                                @click="goToPage(orders.links[0]?.url)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <span class="text-sm text-muted-foreground">
                                Halaman {{ orders.current_page }} dari {{ orders.last_page }}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="orders.current_page === orders.last_page"
                                @click="goToPage(orders.links[orders.links.length - 1]?.url)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

