<script setup lang="ts">
/**
 * Account Orders Page
 * Halaman riwayat pesanan user dengan iOS-like animations
 * menggunakan motion-v, list pesanan, dan status tracking
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { ref } from 'vue'
import { home } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
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
} from 'lucide-vue-next'

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

defineProps<Props>()

const haptic = useHapticFeedback()

/**
 * Press states untuk iOS-like feedback
 */
const isBackPressed = ref(false)
const pressedOrderId = ref<number | null>(null)

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
    <Head title="Riwayat Pesanan - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                >
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                        <div class="brand-logo h-9 w-9 sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                        <span class="hidden text-[10px] font-medium text-brand-gold sm:block">Premium Quality Products</span>
                    </div>
                </Link>
                </Motion>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-6 sm:px-6 sm:py-8">
            <!-- Back Button dengan iOS press feedback -->
            <Motion
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="springTransition"
            >
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
            </Motion>

            <!-- Page Title -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springTransition, delay: 0.05 }"
                class="mb-6"
            >
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Riwayat Pesanan
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ orders.length }} pesanan ditemukan
                </p>
            </Motion>

            <!-- Empty State dengan animation -->
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

            <!-- Orders List dengan staggered animation -->
            <div v-if="orders.length > 0" class="space-y-4">
                <Motion
                    v-for="(order, index) in orders"
                    :key="order.id"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 + index * 0.05 }"
                >
                    <Motion
                        :animate="{
                            scale: pressedOrderId === order.id ? 0.98 : 1,
                        }"
                        :transition="snappyTransition"
                    >
                        <Card
                            class="ios-card cursor-pointer overflow-hidden rounded-2xl border-brand-blue-100 hover:border-brand-blue-200 dark:border-brand-blue-800/30 dark:hover:border-brand-blue-700/50"
                            @mousedown="handleOrderPress(order.id)"
                            @mouseup="pressedOrderId = null"
                            @mouseleave="pressedOrderId = null"
                            @touchstart.passive="handleOrderPress(order.id)"
                            @touchend="pressedOrderId = null"
                >
                    <CardContent class="p-4">
                        <!-- Header Row -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="price-tag font-mono text-sm font-semibold">
                                    {{ order.order_number }}
                                </p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-muted-foreground">
                                    <Clock class="h-3 w-3" />
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <OrderStatusBadge :status="order.status" />
                        </div>

                        <!-- Items Preview -->
                                <Motion
                                    :initial="{ opacity: 0 }"
                                    :animate="{ opacity: 1 }"
                                    :transition="{ ...springTransition, delay: 0.15 + index * 0.05 }"
                                    class="mt-3 rounded-xl bg-muted/50 p-3"
                                >
                            <div class="flex items-center gap-2 text-sm">
                                <Package class="h-4 w-4 text-muted-foreground" />
                                <span class="text-muted-foreground">{{ order.items_count }} item</span>
                            </div>
                                    <p class="mt-1 truncate text-sm text-muted-foreground">
                                {{ order.items.map(item => item.product_name).join(', ') }}
                            </p>
                                </Motion>

                        <!-- Footer Row -->
                        <div class="mt-3 flex items-center justify-between">
                            <PriceDisplay :price="order.total" size="lg" class="font-bold" />
                                    <Button variant="ghost" size="sm" class="ios-button gap-1 rounded-lg">
                                Detail
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
                    </Motion>
                </Motion>
            </div>
        </main>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
