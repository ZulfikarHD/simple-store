<script setup lang="ts">
/**
 * Admin Orders Index Page
 * Menampilkan daftar pesanan dengan fitur pagination, search, dan filter, yaitu:
 * - Card view untuk mobile dengan quick actions
 * - Tabel data pesanan untuk desktop dengan kolom order number, customer, total, status, tanggal
 * - Search bar untuk pencarian berdasarkan order number, nama customer, atau nomor telepon
 * - Filter dropdown untuk status pesanan dan date range
 * - Pagination untuk navigasi halaman
 * - iOS-like design dengan spring animations dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import OrderCard from '@/components/admin/OrderCard.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as ordersIndex, show } from '@/routes/admin/orders'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
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
    SlidersHorizontal,
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
    customer_address?: string
    total: number
    status: string
    items: OrderItem[]
    created_at: string
    waiting_minutes?: number
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

// Mobile filter visibility
const showMobileFilter = ref(false)

// Press state untuk iOS-like feedback
const pressedRow = ref<number | null>(null)

/**
 * Toggle mobile filter visibility
 */
function toggleMobileFilter() {
    haptic.selection()
    showMobileFilter.value = !showMobileFilter.value
}

/**
 * Computed untuk cek apakah ada filter aktif
 */
const hasActiveFilters = computed(() => {
    return search.value || status.value || startDate.value || endDate.value
})

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
    haptic.selection()
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
    haptic.medium()
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
        haptic.light()
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
    haptic.medium()
    const cleanPhone = phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
}

/**
 * Handle row press untuk iOS-like feedback
 */
function handleRowPress(orderId: number) {
    pressedRow.value = orderId
    haptic.light()
}

/**
 * Handle row release
 */
function handleRowRelease() {
    pressedRow.value = null
}

/**
 * Navigate to order detail
 */
function navigateToDetail(orderId: number) {
    haptic.selection()
    router.visit(show(orderId).url)
}
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header dengan spring animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-2"
                >
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Manajemen Pesanan
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola dan pantau pesanan customer
                    </p>
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
                        class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
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
                        class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {{ flashError }}
                    </div>
                </Transition>

                <!-- Mobile Search Bar + Filter Toggle -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="flex gap-2 md:hidden"
                >
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari pesanan..."
                            class="ios-input h-11 pl-10"
                        />
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        class="ios-button h-11 w-11 shrink-0"
                        :class="{ 'bg-primary text-primary-foreground': hasActiveFilters }"
                        @click="toggleMobileFilter"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                    </Button>
                </Motion>

                <!-- Mobile Filter Panel (Collapsible) -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-[var(--ios-spring-snappy)]"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <Card v-if="showMobileFilter" class="ios-card md:hidden">
                        <CardContent class="p-4">
                            <div class="flex flex-col gap-3">
                                <!-- Status Filter -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium">Status</label>
                                    <select
                                        v-model="status"
                                        class="h-11 rounded-xl border border-input bg-background px-3 text-base"
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

                                <!-- Date Range -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-medium">Dari Tanggal</label>
                                        <Input
                                            v-model="startDate"
                                            type="date"
                                            class="ios-input h-11"
                                            @change="applyFilters"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-medium">Sampai Tanggal</label>
                                        <Input
                                            v-model="endDate"
                                            type="date"
                                            class="ios-input h-11"
                                            @change="applyFilters"
                                        />
                                    </div>
                                </div>

                                <!-- Reset Button -->
                                <Button
                                    variant="outline"
                                    class="ios-button h-11"
                                    @click="resetFilters"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Reset Filter
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </Transition>

                <!-- Desktop Filters Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="hidden md:block"
                >
                    <Card class="ios-card">
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
                                        class="ios-input pl-10"
                                    />
                                </div>

                                <!-- Status Filter -->
                                <select
                                    v-model="status"
                                    class="h-10 rounded-xl border border-input bg-background px-3 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring"
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
                                    class="ios-input w-full sm:w-auto"
                                    @change="applyFilters"
                                />
                                <span class="text-muted-foreground">sampai</span>
                                <Input
                                    v-model="endDate"
                                    type="date"
                                    class="ios-input w-full sm:w-auto"
                                    @change="applyFilters"
                                />

                                <!-- Reset Button -->
                                <Button
                                    variant="outline"
                                    class="ios-button"
                                    @click="resetFilters"
                                >
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                </Motion>

                <!-- Mobile Order Cards View - Visible Only on Mobile (<768px) -->
                <div class="flex flex-col gap-3 md:hidden">
                    <!-- Debug Banner - Remove after testing -->
                    <div class="rounded-lg bg-yellow-100 p-3 text-sm text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        📱 Mobile View - {{ orders.data?.length ?? 0 }} pesanan
                    </div>

                    <!-- Order Cards -->
                    <OrderCard
                        v-for="order in orders.data"
                        :key="order.id"
                        :order="order"
                        :statuses="statuses"
                    />

                    <!-- Mobile Empty State (when no orders) -->
                    <Card v-if="!orders.data || orders.data.length === 0" class="ios-card">
                        <CardContent class="py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <ShoppingBag class="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <p class="text-lg font-medium text-muted-foreground">
                                    Belum ada pesanan
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Pesanan customer akan muncul di sini
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Mobile Pagination -->
                    <div
                        v-if="orders.data?.length > 0 && orders.last_page > 1"
                        class="flex items-center justify-between rounded-xl border bg-card p-3"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            class="ios-button h-10 w-10 p-0"
                            :disabled="orders.current_page === 1"
                            @click="goToPage(orders.links[0]?.url)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            {{ orders.current_page }} / {{ orders.last_page }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="ios-button h-10 w-10 p-0"
                            :disabled="orders.current_page === orders.last_page"
                            @click="goToPage(orders.links[orders.links.length - 1]?.url)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Desktop Orders Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="hidden md:block"
                >
                    <Card class="ios-card">
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
                                    <Motion
                                        v-for="(order, index) in orders.data"
                                        :key="order.id"
                                        tag="tr"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                        class="cursor-pointer transition-all duration-150 hover:bg-muted/50"
                                        :class="{ 'scale-[0.99] bg-muted/30': pressedRow === order.id }"
                                        @mousedown="handleRowPress(order.id)"
                                        @mouseup="handleRowRelease"
                                        @mouseleave="handleRowRelease"
                                        @touchstart.passive="handleRowPress(order.id)"
                                        @touchend="handleRowRelease"
                                        @click="navigateToDetail(order.id)"
                                    >
                                        <!-- Order Number -->
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-sm font-medium text-primary">
                                                {{ order.order_number }}
                                            </span>
                                        </td>

                                        <!-- Customer Info -->
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-1">
                                                <span class="font-medium">{{ order.customer_name }}</span>
                                                <button
                                                    class="flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"
                                                    @click.stop="openWhatsApp(order.customer_phone)"
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
                                                <Link :href="show(order.id).url" @click.stop>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="ios-button h-8 gap-1"
                                                    >
                                                        <Eye class="h-4 w-4" />
                                                        Detail
                                                    </Button>
                                                </Link>
                                            </div>
                                        </td>
                                    </Motion>

                                    <!-- Empty State -->
                                    <tr v-if="orders.data.length === 0">
                                        <td colspan="7" class="px-4 py-12">
                                            <Motion
                                                :initial="{ opacity: 0, scale: 0.95 }"
                                                :animate="{ opacity: 1, scale: 1 }"
                                                :transition="springPresets.ios"
                                                class="flex flex-col items-center justify-center text-center"
                                            >
                                                <ShoppingBag class="mb-4 h-12 w-12 text-muted-foreground/50" />
                                                <p class="text-lg font-medium text-muted-foreground">
                                                    Belum ada pesanan
                                                </p>
                                                <p class="mt-1 text-sm text-muted-foreground">
                                                    Pesanan customer akan muncul di sini
                                                </p>
                                            </Motion>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Desktop Pagination -->
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
                                    class="ios-button"
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
                                    class="ios-button"
                                    :disabled="orders.current_page === orders.last_page"
                                    @click="goToPage(orders.links[orders.links.length - 1]?.url)"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                </Motion>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>

    </AppLayout>
</template>
