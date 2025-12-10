<script setup lang="ts">
/**
 * ActiveOrdersSection Component
 * Section untuk menampilkan pesanan aktif user di halaman Home
 * dengan horizontal scroll cards design untuk UX mobile yang lebih baik
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { Badge } from '@/components/ui/badge'
import OrderTrackingCard from './OrderTrackingCard.vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    Package,
    ChevronDown,
    ChevronRight,
    Clock,
} from 'lucide-vue-next'

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

interface ActiveOrder {
    id: number
    order_number: string
    status: OrderStatus
    total: number
    items_count: number
    items_preview: string
    created_at: string
    created_at_human: string
}

interface ActiveOrdersData {
    count: number
    orders: ActiveOrder[]
}

const page = usePage()
const haptic = useHapticFeedback()

/**
 * State untuk expanded/collapsed section
 * Default collapsed jika lebih dari 2 pesanan untuk menghemat ruang
 */
const isExpanded = ref(true)
const isHeaderPressed = ref(false)

/**
 * Computed untuk active orders dari shared props
 */
const activeOrders = computed<ActiveOrdersData>(() => {
    return (page.props as { active_orders?: ActiveOrdersData }).active_orders ?? {
        count: 0,
        orders: [],
    }
})

/**
 * Computed untuk cek apakah user sudah login
 */
const isAuthenticated = computed(() => {
    return !!(page.props as { auth?: { user?: unknown } }).auth?.user
})

/**
 * Limit pesanan yang ditampilkan ke 5 untuk performa
 */
const displayedOrders = computed(() => {
    return activeOrders.value.orders.slice(0, 5)
})

/**
 * Cek apakah ada lebih banyak pesanan dari yang ditampilkan
 */
const hasMoreOrders = computed(() => {
    return activeOrders.value.count > displayedOrders.value.length
})

/**
 * Toggle expanded state dengan haptic feedback
 */
function toggleExpanded() {
    haptic.selection()
    isExpanded.value = !isExpanded.value
}

const springTransition = { type: 'spring' as const, ...springPresets.ios }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <!-- Only show when user is authenticated and has active orders -->
    <AnimatePresence>
        <Motion
            v-if="isAuthenticated && activeOrders.count > 0"
            :initial="{ opacity: 0, y: -10, height: 0 }"
            :animate="{ opacity: 1, y: 0, height: 'auto' }"
            :exit="{ opacity: 0, y: -10, height: 0 }"
            :transition="springTransition"
            class="mb-5 overflow-hidden sm:mb-6"
        >
            <!-- Section Container dengan premium styling -->
            <div class="rounded-2xl border border-brand-gold-200/50 bg-gradient-to-br from-brand-gold-50/80 via-white to-brand-blue-50/50 shadow-sm dark:border-brand-gold-800/30 dark:from-brand-gold-900/20 dark:via-background dark:to-brand-blue-900/10">
                <!-- Collapsible Header -->
                <Motion
                    :animate="{ scale: isHeaderPressed ? 0.99 : 1 }"
                    :transition="snappyTransition"
                >
                    <button
                        class="flex w-full items-center justify-between px-4 py-3"
                        @click="toggleExpanded"
                        @mousedown="isHeaderPressed = true"
                        @mouseup="isHeaderPressed = false"
                        @mouseleave="isHeaderPressed = false"
                        @touchstart.passive="isHeaderPressed = true"
                        @touchend="isHeaderPressed = false"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-gold-400 to-brand-gold-500 shadow-sm">
                                <Package class="h-4 w-4 text-white" />
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-foreground">Pesanan Aktif</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ activeOrders.count }} pesanan dalam proses
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge
                                variant="secondary"
                                class="bg-brand-gold-100 text-brand-gold-700 dark:bg-brand-gold-900/30 dark:text-brand-gold-400"
                            >
                                {{ activeOrders.count }}
                            </Badge>
                            <Motion
                                :animate="{ rotate: isExpanded ? 180 : 0 }"
                                :transition="snappyTransition"
                            >
                                <ChevronDown class="h-5 w-5 text-muted-foreground" />
                            </Motion>
                        </div>
                    </button>
                </Motion>

                <!-- Collapsible Content dengan Horizontal Scroll -->
                <AnimatePresence>
                    <Motion
                        v-if="isExpanded"
                        :initial="{ opacity: 0, height: 0 }"
                        :animate="{ opacity: 1, height: 'auto' }"
                        :exit="{ opacity: 0, height: 0 }"
                        :transition="springTransition"
                        class="overflow-hidden"
                    >
                        <div class="pb-3">
                            <!-- Horizontal Scroll Container - Full bleed untuk touch scroll yang nyaman -->
                            <div class="-mx-4 overflow-x-auto scrollbar-hide sm:mx-0 sm:overflow-visible">
                                <div class="flex gap-2.5 px-4 pb-1 sm:flex-wrap sm:px-0">
                                    <!-- Order Cards dalam horizontal scroll -->
                                    <OrderTrackingCard
                                        v-for="(order, index) in displayedOrders"
                                        :key="order.id"
                                        :order="order"
                                        :index="index"
                                        class="w-64 shrink-0 sm:w-auto sm:shrink"
                                    />

                                    <!-- View More Card jika ada lebih banyak pesanan -->
                                    <Link
                                        v-if="hasMoreOrders"
                                        href="/account/orders"
                                        class="group flex w-32 shrink-0 flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-brand-gold-300 bg-white/60 p-4 text-center transition-all hover:border-brand-gold-400 hover:bg-white/80 dark:border-brand-gold-700/50 dark:bg-background/60 dark:hover:bg-background/80 sm:w-auto sm:shrink sm:px-6"
                                    >
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-gold-100 dark:bg-brand-gold-900/30">
                                            <Clock class="h-4 w-4 text-brand-gold-600 dark:text-brand-gold-400" />
                                        </div>
                                        <span class="text-xs font-medium text-brand-gold-700 dark:text-brand-gold-400">
                                            +{{ activeOrders.count - displayedOrders.length }} lagi
                                        </span>
                                    </Link>
                                </div>
                            </div>

                            <!-- View All Link -->
                            <div class="mt-2 px-4">
                                <Link
                                    href="/account/orders"
                                    class="group flex items-center justify-center gap-1.5 rounded-xl bg-white/60 py-2 text-xs font-medium text-brand-blue transition-all hover:bg-white/80 dark:bg-background/60 dark:hover:bg-background/80"
                                >
                                    Lihat Semua Riwayat
                                    <ChevronRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                                </Link>
                            </div>
                        </div>
                    </Motion>
                </AnimatePresence>
            </div>
        </Motion>
    </AnimatePresence>
</template>

