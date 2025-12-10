<script setup lang="ts">
/**
 * Admin Orders Index Page
 * Menampilkan daftar pesanan dengan fitur pagination, search, dan filter, yaitu:
 * - Card view untuk mobile dengan quick actions
 * - Tabel data pesanan untuk desktop dengan kolom order number, customer, total, status, tanggal
 * - Search bar untuk pencarian berdasarkan order number, nama customer, atau nomor telepon
 * - Filter dropdown untuk status pesanan dan date range
 * - Pagination untuk navigasi halaman
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
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
    Sparkles,
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

/**
 * OrderStatus type untuk type-safety dengan OrderCard component
 * Status pesanan yang valid dalam sistem
 */
type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address?: string
    total: number
    status: OrderStatus
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
const flashSuccess = computed(() => (page.props as unknown as { flash?: { success?: string } }).flash?.success)
const flashError = computed(() => (page.props as unknown as { flash?: { error?: string } }).flash?.error)

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
 * Count active filters
 */
const activeFilterCount = computed(() => {
    let count = 0
    if (search.value) count++
    if (status.value) count++
    if (startDate.value) count++
    if (endDate.value) count++
    return count
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
 * Get status badge class untuk styling
 */
function getStatusClass(orderStatus: string): string {
    switch (orderStatus) {
        case 'pending':
            return 'admin-badge--pending'
        case 'confirmed':
            return 'admin-badge--confirmed'
        case 'preparing':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
        case 'ready':
            return 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300'
        case 'delivered':
            return 'admin-badge--success'
        case 'cancelled':
            return 'admin-badge--destructive'
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
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-1"
                >
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            Pesanan
                        </h1>
                        <Badge variant="secondary" class="tabular-nums">
                            {{ orders.total }} total
                        </Badge>
                    </div>
                    <p class="text-muted-foreground">
                        Kelola dan pantau semua pesanan customer
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

                <!-- Mobile Search + Filter Toggle -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="flex gap-2 md:hidden"
                >
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari pesanan..."
                            class="admin-input h-12 pl-10"
                        />
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        class="ios-button relative h-12 w-12 shrink-0"
                        :class="{ 'border-primary bg-primary/5': hasActiveFilters }"
                        @click="toggleMobileFilter"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        <span
                            v-if="activeFilterCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white"
                        >
                            {{ activeFilterCount }}
                        </span>
                    </Button>
                </Motion>

                <!-- Mobile Filter Panel -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition-all duration-200 ease-[var(--ios-spring-snappy)]"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 -translate-y-2 scale-95"
                >
                    <div v-if="showMobileFilter" class="admin-form-section md:hidden">
                        <div class="admin-form-section-content">
                            <div class="flex flex-col gap-4">
                                <!-- Status Filter -->
                                <div class="admin-input-group">
                                    <label>Status Pesanan</label>
                                    <select
                                        v-model="status"
                                        class="admin-select"
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
                                    <div class="admin-input-group">
                                        <label>Dari Tanggal</label>
                                        <Input
                                            v-model="startDate"
                                            type="date"
                                            class="admin-input"
                                            @change="applyFilters"
                                        />
                                    </div>
                                    <div class="admin-input-group">
                                        <label>Sampai</label>
                                        <Input
                                            v-model="endDate"
                                            type="date"
                                            class="admin-input"
                                            @change="applyFilters"
                                        />
                                    </div>
                                </div>

                                <!-- Reset Button -->
                                <Button
                                    v-if="hasActiveFilters"
                                    variant="outline"
                                    class="admin-btn-secondary"
                                    @click="resetFilters"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Reset Filter
                                </Button>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Desktop Filters -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="hidden md:block"
                >
                    <div class="admin-form-section">
                        <div class="admin-form-section-header">
                            <h3>
                                <Filter />
                                Filter & Pencarian
                            </h3>
                        </div>
                        <div class="admin-form-section-content">
                            <div class="flex flex-col gap-4">
                                <!-- Row 1: Search and Status -->
                                <div class="flex flex-col gap-4 sm:flex-row">
                                    <div class="relative flex-1">
                                        <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            v-model="search"
                                            type="text"
                                            placeholder="Cari order number, nama, atau telepon..."
                                            class="admin-input pl-10"
                                        />
                                    </div>
                                    <select
                                        v-model="status"
                                        class="admin-select w-full sm:w-48"
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
                                        <span>Rentang Tanggal:</span>
                                    </div>
                                    <Input
                                        v-model="startDate"
                                        type="date"
                                        class="admin-input w-full sm:w-auto"
                                        @change="applyFilters"
                                    />
                                    <span class="text-muted-foreground">sampai</span>
                                    <Input
                                        v-model="endDate"
                                        type="date"
                                        class="admin-input w-full sm:w-auto"
                                        @change="applyFilters"
                                    />
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
                    </div>
                </Motion>

                <!-- Mobile Order Cards -->
                <div class="flex flex-col gap-3 md:hidden">
                    <OrderCard
                        v-for="order in orders.data"
                        :key="order.id"
                        :order="order"
                        :statuses="statuses"
                    />

                    <!-- Mobile Empty State -->
                    <div v-if="!orders.data || orders.data.length === 0" class="admin-form-section">
                        <div class="admin-empty-state">
                            <div class="icon-wrapper">
                                <ShoppingBag />
                            </div>
                            <h3>Belum Ada Pesanan</h3>
                            <p>Pesanan customer akan muncul di sini</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div
                        v-if="orders.data?.length > 0 && orders.last_page > 1"
                        class="flex items-center justify-between rounded-2xl border bg-card p-3"
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

                <!-- Desktop Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="hidden md:block"
                >
                    <div class="ios-grouped-table">
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Customer</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Items</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <Motion
                                        v-for="(order, index) in orders.data"
                                        :key="order.id"
                                        tag="tr"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                        class="cursor-pointer"
                                        :class="{ 'scale-[0.995] bg-muted/60': pressedRow === order.id }"
                                        @mousedown="handleRowPress(order.id)"
                                        @mouseup="handleRowRelease"
                                        @mouseleave="handleRowRelease"
                                        @touchstart.passive="handleRowPress(order.id)"
                                        @touchend="handleRowRelease"
                                        @click="navigateToDetail(order.id)"
                                    >
                                        <td>
                                            <span class="font-mono text-sm font-semibold text-primary">
                                                {{ order.order_number }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex flex-col gap-0.5">
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
                                        <td class="text-right">
                                            <PriceDisplay :price="order.total" size="sm" />
                                        </td>
                                        <td class="text-center">
                                            <Badge variant="outline" class="gap-1 tabular-nums">
                                                <Package class="h-3 w-3" />
                                                {{ order.items.length }}
                                            </Badge>
                                        </td>
                                        <td class="text-center">
                                            <span :class="['admin-badge', getStatusClass(order.status)]">
                                                {{ statuses[order.status] || order.status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-sm text-muted-foreground">
                                                {{ formatDate(order.created_at) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <Link :href="show(order.id).url" @click.stop>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="ios-button h-8 gap-1.5"
                                                >
                                                    <Eye class="h-4 w-4" />
                                                    Detail
                                                </Button>
                                            </Link>
                                        </td>
                                    </Motion>

                                    <!-- Empty State -->
                                    <tr v-if="orders.data.length === 0">
                                        <td colspan="7">
                                            <div class="admin-empty-state">
                                                <div class="icon-wrapper">
                                                    <ShoppingBag />
                                                </div>
                                                <h3>Belum Ada Pesanan</h3>
                                                <p>Pesanan customer akan muncul di sini</p>
                                            </div>
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
                                <span class="text-sm tabular-nums text-muted-foreground">
                                    {{ orders.current_page }} / {{ orders.last_page }}
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
                    </div>
                </Motion>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>
    </AppLayout>
</template>
