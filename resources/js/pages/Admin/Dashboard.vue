<script setup lang="ts">
/**
 * Admin Dashboard Component
 * Menampilkan overview statistik toko dengan metrics utama dalam iOS-style design, yaitu:
 * - Total sales dengan premium gradient card
 * - Orders hari ini dengan animated counter
 * - Pending orders dengan urgent indicator
 * - Active products count
 * - Recent orders list dengan interactive cards
 * - Browser notifications untuk pesanan baru
 * - iOS-like design dengan spring animations dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { OrderStatusBadge } from '@/components/store'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as productsIndex } from '@/routes/admin/products'
import { index as categoriesIndex } from '@/routes/admin/categories'
import { index as ordersIndex } from '@/routes/admin/orders'
import { show as orderShow } from '@/routes/admin/orders'
import { useOrderNotifications } from '@/composables/useOrderNotifications'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import {
    ShoppingBag,
    Clock,
    TrendingUp,
    Package,
    Users,
    Calendar,
    FolderTree,
    Bell,
    BellOff,
    ChevronRight,
    Sparkles,
    ArrowUpRight,
    Wallet,
} from 'lucide-vue-next'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Setup browser notifications untuk pesanan baru
 */
const {
    isSupported: notificationSupported,
    notificationPermission,
    requestPermission,
    watchPendingOrders,
} = useOrderNotifications()

/**
 * Press state untuk iOS-like button feedback
 */
const pressedCard = ref<string | null>(null)

onMounted(() => {
    watchPendingOrders()
})

interface OrderItem {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    total: number
    status: string
    items_count: number
    created_at: string
    created_at_human: string
}

interface DashboardStats {
    today_orders: number
    pending_orders: number
    total_sales: number
    active_products: number
    recent_orders: OrderItem[]
    order_status_breakdown: Record<string, number>
}

interface Props {
    stats: DashboardStats
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

/**
 * Format angka ke format Indonesia dengan separator titik
 */
function formatNumber(value: number): string {
    return new Intl.NumberFormat('id-ID').format(value)
}

/**
 * Handle card press untuk iOS-like feedback
 */
function handleCardPress(cardId: string) {
    pressedCard.value = cardId
    haptic.light()
}

/**
 * Handle card release
 */
function handleCardRelease() {
    pressedCard.value = null
}

/**
 * Handle quick action click dengan haptic
 */
function handleQuickAction() {
    haptic.medium()
}

/**
 * Navigate to order detail
 */
function navigateToOrder(orderId: number) {
    haptic.selection()
    router.visit(orderShow(orderId).url)
}

/**
 * Navigate to orders list
 */
function navigateToOrders() {
    haptic.selection()
    router.visit(ordersIndex().url)
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header dengan spring animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-1"
                >
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            Dashboard
                        </h1>
                        <Badge variant="outline" class="hidden gap-1.5 md:flex">
                            <Sparkles class="h-3 w-3 text-primary" />
                            Admin
                        </Badge>
                    </div>
                    <p class="text-muted-foreground">
                        Selamat datang! Berikut ringkasan performa toko Anda hari ini.
                    </p>
                </Motion>

                <!-- Premium Stats Cards Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Sales Card - Hero Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ ...springPresets.bouncy, delay: staggerDelay(0) }"
                        class="lg:col-span-2"
                    >
                        <div
                            class="stats-card group cursor-pointer p-5 transition-transform duration-150"
                            :class="{ 'scale-[0.98]': pressedCard === 'sales' }"
                            @mousedown="handleCardPress('sales')"
                            @mouseup="handleCardRelease"
                            @mouseleave="handleCardRelease"
                            @touchstart.passive="handleCardPress('sales')"
                            @touchend="handleCardRelease"
                        >
                            <!-- Gradient Background Decoration -->
                            <div class="absolute inset-0 opacity-[0.03]">
                                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary blur-3xl" />
                                <div class="absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-accent blur-3xl" />
                            </div>

                            <div class="relative flex items-start justify-between">
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-muted-foreground">Total Penjualan</span>
                                        <ArrowUpRight class="h-4 w-4 text-green-500" />
                                    </div>
                                    <PriceDisplay
                                        :price="stats.total_sales"
                                        size="xl"
                                        class="text-3xl! md:text-4xl!"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Total revenue keseluruhan
                                    </p>
                                </div>
                                <div class="stats-icon--gold">
                                    <Wallet class="h-6 w-6 text-white" />
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Today Orders Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ ...springPresets.bouncy, delay: staggerDelay(1) }"
                    >
                        <div
                            class="stats-card stats-card--blue group cursor-pointer p-5 transition-transform duration-150"
                            :class="{ 'scale-[0.98]': pressedCard === 'today' }"
                            @mousedown="handleCardPress('today')"
                            @mouseup="handleCardRelease"
                            @mouseleave="handleCardRelease"
                            @touchstart.passive="handleCardPress('today')"
                            @touchend="handleCardRelease"
                            @click="navigateToOrders"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-muted-foreground">Pesanan Hari Ini</span>
                                    <span class="text-3xl font-bold">{{ formatNumber(stats.today_orders) }}</span>
                                    <p class="text-xs text-muted-foreground">
                                        Pesanan masuk
                                    </p>
                                </div>
                                <div class="stats-icon">
                                    <ShoppingBag class="h-5 w-5 text-white" />
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Pending Orders Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ ...springPresets.bouncy, delay: staggerDelay(2) }"
                    >
                        <div
                            class="stats-card group cursor-pointer p-5 transition-transform duration-150"
                            :class="[
                                { 'scale-[0.98]': pressedCard === 'pending' },
                                stats.pending_orders > 0 ? 'stats-card--gold' : '',
                            ]"
                            @mousedown="handleCardPress('pending')"
                            @mouseup="handleCardRelease"
                            @mouseleave="handleCardRelease"
                            @touchstart.passive="handleCardPress('pending')"
                            @touchend="handleCardRelease"
                            @click="navigateToOrders"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-muted-foreground">Pending</span>
                                        <span
                                            v-if="stats.pending_orders > 0"
                                            class="flex h-2 w-2"
                                        >
                                            <span class="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-amber-400 opacity-75" />
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500" />
                                        </span>
                                    </div>
                                    <span class="text-3xl font-bold">{{ formatNumber(stats.pending_orders) }}</span>
                                    <p class="text-xs text-muted-foreground">
                                        Menunggu konfirmasi
                                    </p>
                                </div>
                                <div class="stats-icon--gold">
                                    <Clock class="h-5 w-5 text-white" />
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Secondary Stats Row -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Active Products Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                    >
                        <Link :href="productsIndex().url" @click="handleQuickAction">
                            <div
                                class="stats-card stats-card--success group cursor-pointer p-4 transition-transform duration-150"
                                :class="{ 'scale-[0.98]': pressedCard === 'products' }"
                                @mousedown="handleCardPress('products')"
                                @mouseup="handleCardRelease"
                                @mouseleave="handleCardRelease"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="stats-icon--success">
                                        <Package class="h-5 w-5 text-white" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-2xl font-bold">{{ formatNumber(stats.active_products) }}</span>
                                        <span class="text-sm text-muted-foreground">Produk Aktif</span>
                                    </div>
                                    <ChevronRight class="ml-auto h-5 w-5 text-muted-foreground/50 transition-transform group-hover:translate-x-1" />
                                </div>
                            </div>
                        </Link>
                    </Motion>

                    <!-- Notification Status Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(4) }"
                    >
                        <div class="stats-card p-4">
                            <div class="flex items-center gap-4">
                                <div
                                    :class="[
                                        'flex h-12 w-12 items-center justify-center rounded-2xl',
                                        notificationPermission === 'granted'
                                            ? 'bg-green-100 dark:bg-green-900/40'
                                            : 'bg-muted',
                                    ]"
                                >
                                    <Bell
                                        v-if="notificationPermission === 'granted'"
                                        class="h-5 w-5 text-green-600 dark:text-green-400"
                                    />
                                    <BellOff
                                        v-else-if="notificationPermission === 'denied'"
                                        class="h-5 w-5 text-red-500"
                                    />
                                    <Bell
                                        v-else
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">Notifikasi</span>
                                    <span
                                        :class="[
                                            'text-xs',
                                            notificationPermission === 'granted'
                                                ? 'text-green-600 dark:text-green-400'
                                                : 'text-muted-foreground',
                                        ]"
                                    >
                                        {{
                                            notificationPermission === 'granted'
                                                ? 'Aktif'
                                                : notificationPermission === 'denied'
                                                  ? 'Diblokir'
                                                  : 'Tidak aktif'
                                        }}
                                    </span>
                                </div>
                                <Button
                                    v-if="notificationSupported && notificationPermission !== 'granted'"
                                    size="sm"
                                    variant="outline"
                                    class="ml-auto ios-button"
                                    @click="requestPermission"
                                >
                                    Aktifkan
                                </Button>
                            </div>
                        </div>
                    </Motion>

                    <!-- Quick Actions Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(5) }"
                    >
                        <div class="stats-card p-4">
                            <p class="mb-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                Quick Actions
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="productsIndex().url" @click="handleQuickAction">
                                    <div class="quick-action-pill">
                                        <Package class="text-primary" />
                                        Produk
                                    </div>
                                </Link>
                                <Link :href="categoriesIndex().url" @click="handleQuickAction">
                                    <div class="quick-action-pill">
                                        <FolderTree class="text-primary" />
                                        Kategori
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Main Content Grid -->
                <div class="grid gap-6 lg:grid-cols-5">
                    <!-- Recent Orders - Wider Column -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: 0.25 }"
                        class="lg:col-span-3"
                    >
                        <div class="admin-form-section">
                            <div class="admin-form-section-header">
                                <div class="flex items-center justify-between">
                                    <h3>
                                        <ShoppingBag />
                                        Pesanan Terbaru
                                    </h3>
                                    <Link
                                        :href="ordersIndex().url"
                                        class="text-sm font-medium text-primary hover:underline"
                                        @click="handleQuickAction"
                                    >
                                        Lihat Semua
                                    </Link>
                                </div>
                            </div>
                            <div class="admin-form-section-content p-0">
                                <!-- Empty State -->
                                <div
                                    v-if="stats.recent_orders.length === 0"
                                    class="admin-empty-state"
                                >
                                    <div class="icon-wrapper">
                                        <ShoppingBag />
                                    </div>
                                    <h3>Belum Ada Pesanan</h3>
                                    <p>Pesanan customer akan muncul di sini secara real-time.</p>
                                </div>

                                <!-- Orders List -->
                                <div v-else class="divide-y divide-border/50">
                                    <Motion
                                        v-for="(order, orderIndex) in stats.recent_orders"
                                        :key="order.id"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.3 + orderIndex * 0.05 }"
                                        class="ios-list-item cursor-pointer p-4 transition-all duration-150 hover:bg-muted/50"
                                        role="button"
                                        tabindex="0"
                                        @click="navigateToOrder(order.id)"
                                        @keydown.enter="navigateToOrder(order.id)"
                                    >
                                        <!-- Order Header -->
                                        <div class="flex flex-1 items-center justify-between gap-4">
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-sm font-semibold text-primary">
                                                        {{ order.order_number }}
                                                    </span>
                                                    <OrderStatusBadge :status="order.status as 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'" />
                                                </div>
                                                <p class="font-medium">{{ order.customer_name }}</p>
                                                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                                    <span class="flex items-center gap-1">
                                                        <Package class="h-3 w-3" />
                                                        {{ order.items_count }} item
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <Calendar class="h-3 w-3" />
                                                        {{ order.created_at_human }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col items-end gap-1">
                                                <PriceDisplay :price="order.total" size="sm" class="font-semibold" />
                                                <ChevronRight class="h-4 w-4 text-muted-foreground/50" />
                                            </div>
                                        </div>
                                    </Motion>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Status Breakdown - Narrower Column -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: 0.3 }"
                        class="lg:col-span-2"
                    >
                        <div class="admin-form-section h-full">
                            <div class="admin-form-section-header">
                                <h3>
                                    <TrendingUp />
                                    Status Pesanan
                                </h3>
                            </div>
                            <div class="admin-form-section-content">
                                <!-- Empty State -->
                                <div
                                    v-if="Object.keys(stats.order_status_breakdown).length === 0"
                                    class="flex flex-col items-center justify-center py-8 text-center"
                                >
                                    <Clock class="mb-2 h-10 w-10 text-muted-foreground/40" />
                                    <p class="text-sm text-muted-foreground">Belum ada data status</p>
                                </div>

                                <!-- Status List -->
                                <div v-else class="flex flex-col gap-3">
                                    <Motion
                                        v-for="(count, status, statusIndex) in stats.order_status_breakdown"
                                        :key="status"
                                        :initial="{ opacity: 0, x: 20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.35 + (statusIndex as number) * 0.05 }"
                                        class="flex items-center justify-between rounded-xl border border-border/50 bg-muted/20 p-3.5 transition-colors hover:bg-muted/40"
                                    >
                                            <OrderStatusBadge :status="status as 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'" />
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-2xl font-bold tabular-nums">{{ formatNumber(count) }}</span>
                                            <span class="text-xs text-muted-foreground">pesanan</span>
                                        </div>
                                    </Motion>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>
    </AppLayout>
</template>
