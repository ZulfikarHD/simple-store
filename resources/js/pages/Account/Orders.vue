<script setup lang="ts">
/**
 * Account Orders Page
 * Halaman riwayat pesanan user dengan iOS-like animations
 * menggunakan motion-v, list pesanan, status tracking, dan filter
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import StoreHeader from '@/components/store/StoreHeader.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { OrderStatusBadge } from '@/components/store'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ShoppingBag,
    ArrowLeft,
    Package,
    Clock,
    ChevronRight,
    ShoppingCart,
    Filter,
} from 'lucide-vue-next'

type OrderStatus = 'all' | 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

/**
 * Interface untuk order item
 */
interface OrderItem {
    id: number
    product_name: string
    quantity: number
    subtotal: number
}

/**
 * Interface untuk order data
 */
interface Order {
    id: number
    order_number: string
    total: number
    status: string
    items: OrderItem[]
    items_count: number
    created_at: string
    created_at_human: string
}

/**
 * Props dari controller
 */
interface Props {
    orders: Order[]
}

const props = defineProps<Props>()

const page = usePage()

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

const haptic = useHapticFeedback()

/**
 * Press states untuk iOS-like feedback
 */
const isBackPressed = ref(false)
const pressedOrderId = ref<number | null>(null)

/**
 * Status filter state
 */
const activeFilter = ref<OrderStatus>('all')

/**
 * Filter options untuk status pesanan
 */
const filterOptions: { value: OrderStatus; label: string }[] = [
    { value: 'all', label: 'Semua' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'confirmed', label: 'Dikonfirmasi' },
    { value: 'preparing', label: 'Diproses' },
    { value: 'ready', label: 'Siap' },
    { value: 'delivered', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
]

/**
 * Filtered orders berdasarkan status
 */
const filteredOrders = computed(() => {
    if (activeFilter.value === 'all') {
        return props.orders
    }
    return props.orders.filter(order => order.status === activeFilter.value)
})

/**
 * Count orders per status untuk badge
 */
const statusCounts = computed(() => {
    const counts: Record<OrderStatus, number> = {
        all: props.orders.length,
        pending: 0,
        confirmed: 0,
        preparing: 0,
        ready: 0,
        delivered: 0,
        cancelled: 0,
    }
    props.orders.forEach(order => {
        const status = order.status as Exclude<OrderStatus, 'all'>
        if (counts[status] !== undefined) {
            counts[status]++
        }
    })
    return counts
})

/**
 * Handle filter change dengan haptic feedback
 */
function handleFilterChange(filter: OrderStatus) {
    haptic.selection()
    activeFilter.value = filter
}

/**
 * Format tanggal
 */
function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Handle order card press
 */
function handleOrderPress(orderId: number) {
    pressedOrderId.value = orderId
    haptic.light()
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`Riwayat Pesanan - ${store.name}`">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                >
                    <StoreHeader />
                </Motion>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content dengan Pull to Refresh -->
        <PullToRefresh>
            <main class="mx-auto w-full max-w-2xl flex-1 px-4 py-4 sm:px-6 sm:py-8">
                <!-- Compact Header untuk Mobile -->
                <Motion
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springTransition"
                    class="mb-4"
                >
                    <!-- Mobile: Inline header -->
                    <div class="flex items-center justify-between gap-3 sm:hidden">
                        <Motion
                            :animate="{ scale: isBackPressed ? 0.9 : 1 }"
                            :transition="snappyTransition"
                        >
                            <Link
                                href="/"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-muted/50"
                                @mousedown="isBackPressed = true"
                                @mouseup="isBackPressed = false"
                                @mouseleave="isBackPressed = false"
                                @touchstart.passive="isBackPressed = true"
                                @touchend="isBackPressed = false"
                            >
                                <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                            </Link>
                        </Motion>
                        <div class="flex-1 text-center">
                            <h1 class="text-lg font-bold text-foreground">Riwayat Pesanan</h1>
                            <p class="text-xs text-muted-foreground">{{ filteredOrders.length }} pesanan</p>
                        </div>
                        <div class="h-10 w-10" /> <!-- Spacer untuk balance -->
                    </div>

                    <!-- Desktop: Full header -->
                    <div class="hidden sm:block">
                        <Motion
                            :animate="{ scale: isBackPressed ? 0.95 : 1, opacity: isBackPressed ? 0.7 : 1 }"
                            :transition="snappyTransition"
                        >
                            <Link
                                href="/account"
                                class="ios-button mb-4 inline-flex h-11 items-center gap-2 rounded-xl px-3 text-sm text-muted-foreground hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
                                @mousedown="isBackPressed = true"
                                @mouseup="isBackPressed = false"
                                @mouseleave="isBackPressed = false"
                                @touchstart.passive="isBackPressed = true"
                                @touchend="isBackPressed = false"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Kembali ke Akun
                            </Link>
                        </Motion>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">
                            Riwayat Pesanan
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ filteredOrders.length }} pesanan ditemukan
                        </p>
                    </div>
                </Motion>

                <!-- Status Filter - Horizontal scroll untuk mobile -->
                <Motion
                    v-if="orders.length > 0"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.05 }"
                    class="mb-4"
                >
                    <div class="-mx-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0">
                        <div class="flex gap-2">
                            <Button
                                v-for="filter in filterOptions"
                                :key="filter.value"
                                :variant="activeFilter === filter.value ? 'default' : 'outline'"
                                size="sm"
                                class="ios-button shrink-0 gap-1.5 rounded-full"
                                :class="[
                                    activeFilter === filter.value
                                        ? 'bg-brand-blue-500 text-white hover:bg-brand-blue-600'
                                        : 'border-brand-blue-100 hover:bg-brand-blue-50 dark:border-brand-blue-800/30',
                                ]"
                                @click="handleFilterChange(filter.value)"
                            >
                                {{ filter.label }}
                                <Badge
                                    v-if="statusCounts[filter.value] > 0"
                                    :variant="activeFilter === filter.value ? 'secondary' : 'outline'"
                                    class="h-5 min-w-5 rounded-full px-1.5 text-[10px]"
                                    :class="[
                                        activeFilter === filter.value
                                            ? 'bg-white/20 text-white'
                                            : 'bg-muted text-muted-foreground',
                                    ]"
                                >
                                    {{ statusCounts[filter.value] }}
                                </Badge>
                            </Button>
                        </div>
                    </div>
                </Motion>

                <!-- Empty State - No orders at all -->
                <AnimatePresence>
                    <Motion
                        v-if="orders.length === 0"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.95 }"
                        :transition="{ ...springTransition, delay: 0.1 }"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <Motion
                            :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :transition="{ ...bouncyTransition, delay: 0.15 }"
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                        >
                            <ShoppingCart class="h-8 w-8 text-muted-foreground" />
                        </Motion>
                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.2 }"
                        >
                            <h2 class="mb-2 text-lg font-semibold text-foreground">Belum Ada Pesanan</h2>
                        </Motion>
                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.25 }"
                        >
                            <p class="mb-6 text-center text-muted-foreground">
                                Anda belum pernah melakukan pemesanan. Yuk mulai belanja!
                            </p>
                        </Motion>
                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.3 }"
                        >
                            <Link :href="home()">
                                <Button class="ios-button gap-2 rounded-xl">
                                    <ShoppingBag class="h-4 w-4" />
                                    Mulai Belanja
                                </Button>
                            </Link>
                        </Motion>
                    </Motion>
                </AnimatePresence>

                <!-- Empty State - No orders matching filter -->
                <AnimatePresence>
                    <Motion
                        v-if="orders.length > 0 && filteredOrders.length === 0"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.95 }"
                        :transition="{ ...springTransition, delay: 0.1 }"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <Motion
                            :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :transition="{ ...bouncyTransition, delay: 0.15 }"
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                        >
                            <Filter class="h-8 w-8 text-muted-foreground" />
                        </Motion>
                        <h2 class="mb-2 text-lg font-semibold text-foreground">Tidak Ada Pesanan</h2>
                        <p class="mb-4 text-center text-muted-foreground">
                            Tidak ada pesanan dengan status ini
                        </p>
                        <Button
                            variant="outline"
                            class="ios-button rounded-xl"
                            @click="handleFilterChange('all')"
                        >
                            Lihat Semua Pesanan
                        </Button>
                    </Motion>
                </AnimatePresence>

                <!-- Orders List dengan staggered animation -->
                <div v-if="filteredOrders.length > 0" class="space-y-3">
                    <Motion
                        v-for="(order, index) in filteredOrders"
                        :key="order.id"
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.1 + index * 0.04 }"
                    >
                        <Motion
                            :animate="{
                                scale: pressedOrderId === order.id ? 0.98 : 1,
                            }"
                            :transition="snappyTransition"
                        >
                            <Link
                                :href="`/account/orders/${order.id}`"
                                class="block"
                                @mousedown="handleOrderPress(order.id)"
                                @mouseup="pressedOrderId = null"
                                @mouseleave="pressedOrderId = null"
                                @touchstart.passive="handleOrderPress(order.id)"
                                @touchend="pressedOrderId = null"
                            >
                                <Card
                                    class="ios-card cursor-pointer overflow-hidden rounded-2xl border-brand-blue-100 hover:border-brand-blue-200 dark:border-brand-blue-800/30 dark:hover:border-brand-blue-700/50"
                                >
                                    <CardContent class="p-3 sm:p-4">
                                        <!-- Header Row - Compact untuk mobile -->
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate font-mono text-sm font-semibold text-foreground">
                                                    {{ order.order_number }}
                                                </p>
                                                <p class="mt-0.5 flex items-center gap-1 text-[11px] text-muted-foreground sm:text-xs">
                                                    <Clock class="h-3 w-3 shrink-0" />
                                                    {{ formatDate(order.created_at) }}
                                                </p>
                                            </div>
                                            <OrderStatusBadge :status="order.status" />
                                        </div>

                                        <!-- Items Preview - Compact -->
                                        <div class="mt-2.5 flex items-center gap-2 rounded-lg bg-muted/50 px-2.5 py-2 sm:mt-3 sm:rounded-xl sm:p-3">
                                            <Package class="h-4 w-4 shrink-0 text-muted-foreground" />
                                            <p class="truncate text-xs text-muted-foreground sm:text-sm">
                                                {{ order.items_count }} item · {{ order.items.map(item => item.product_name).join(', ') }}
                                            </p>
                                        </div>

                                        <!-- Footer Row -->
                                        <div class="mt-2.5 flex items-center justify-between sm:mt-3">
                                            <PriceDisplay :price="order.total" size="lg" class="font-bold" />
                                            <span class="flex items-center gap-1 text-xs text-brand-blue sm:text-sm">
                                                Detail
                                                <ChevronRight class="h-4 w-4" />
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        </Motion>
                    </Motion>
                </div>
            </main>
        </PullToRefresh>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
