<script setup lang="ts">
/**
 * UserBottomNav Component
 * iOS-style bottom navigation dengan motion-v spring animations,
 * haptic feedback, badge bounce animations, dan order tracking
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { Badge } from '@/components/ui/badge'
import { home } from '@/routes'
import { show as cartShow } from '@/routes/cart'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    Home,
    ShoppingCart,
    User,
    Package,
} from 'lucide-vue-next'

const page = usePage()
const haptic = useHapticFeedback()

/**
 * State untuk press animation pada setiap item
 */
const pressedItem = ref<string | null>(null)

/**
 * State untuk badge bounce animation
 */
const badgeBounce = ref(false)
const orderBadgeBounce = ref(false)

/**
 * Computed untuk mendapatkan jumlah item di cart
 */
const cartItemsCount = computed(() => {
    const cart = (page.props as { cart?: { total_items: number } }).cart
    return cart?.total_items ?? 0
})

/**
 * Computed untuk mendapatkan jumlah active orders
 */
const activeOrdersCount = computed(() => {
    const activeOrders = (page.props as { active_orders?: { count: number } }).active_orders
    return activeOrders?.count ?? 0
})

/**
 * Watch cart count untuk trigger badge bounce
 */
watch(cartItemsCount, (newVal, oldVal) => {
    if (newVal > oldVal) {
        badgeBounce.value = true
        haptic.success()
        setTimeout(() => {
            badgeBounce.value = false
        }, 400)
    }
})

/**
 * Watch active orders count untuk trigger badge bounce
 */
watch(activeOrdersCount, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        orderBadgeBounce.value = true
        haptic.light()
        setTimeout(() => {
            orderBadgeBounce.value = false
        }, 400)
    }
})

/**
 * Computed untuk current route matching
 */
const currentPath = computed(() => page.url)

/**
 * Navigation items untuk bottom nav
 * Includes order tracking untuk user yang memiliki active orders
 */
const navItems = computed(() => {
    const items = [
        {
            id: 'home',
            title: 'Beranda',
            href: home(),
            icon: Home,
            badge: 0,
            badgeType: 'default' as const,
            isActive: currentPath.value === '/' || currentPath.value.startsWith('/products'),
        },
        {
            id: 'orders',
            title: 'Pesanan',
            href: '/account/orders',
            icon: Package,
            badge: activeOrdersCount.value,
            badgeType: 'orders' as const,
            isActive: currentPath.value.startsWith('/account/orders'),
        },
        {
            id: 'cart',
            title: 'Keranjang',
            href: cartShow(),
            icon: ShoppingCart,
            badge: cartItemsCount.value,
            badgeType: 'cart' as const,
            isActive: currentPath.value.startsWith('/cart') || currentPath.value.startsWith('/checkout'),
        },
        {
            id: 'account',
            title: 'Akun',
            href: '/account',
            icon: User,
            badge: 0,
            badgeType: 'default' as const,
            isActive: currentPath.value === '/account' || currentPath.value.startsWith('/settings') || currentPath.value.startsWith('/login') || currentPath.value.startsWith('/register'),
        },
    ]

    return items
})

/**
 * Handle press start dengan haptic feedback
 */
function handlePressStart(itemId: string) {
    pressedItem.value = itemId
    haptic.light()
}

/**
 * Handle press end
 */
function handlePressEnd() {
    pressedItem.value = null
}

/**
 * Handle navigation dengan medium haptic
 */
function handleNavigation() {
    haptic.medium()
}

/**
 * Spring transitions untuk iOS-like animations
 */
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <nav
        class="ios-tabbar fixed inset-x-0 bottom-0 z-50 border-t border-border/50 md:hidden"
    >
        <!-- Safe area padding untuk iPhone -->
        <div class="flex items-center justify-around">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="relative flex flex-1 flex-col items-center gap-1 py-3"
                :class="[
                    item.isActive
                        ? 'text-primary'
                        : 'text-muted-foreground',
                ]"
                @mousedown="handlePressStart(item.id)"
                @mouseup="handlePressEnd"
                @mouseleave="handlePressEnd"
                @touchstart.passive="handlePressStart(item.id)"
                @touchend="handlePressEnd"
                @touchcancel="handlePressEnd"
                @click="handleNavigation()"
            >
                <Motion
                    :animate="{
                        scale: pressedItem === item.id ? 0.9 : (item.isActive ? 1.1 : 1),
                        opacity: pressedItem === item.id ? 0.7 : 1,
                    }"
                    :transition="snappyTransition"
                    class="relative"
                >
                    <!-- Icon Container -->
                    <component
                        :is="item.icon"
                        class="h-6 w-6 transition-colors duration-200"
                        :class="[
                            item.isActive
                                ? 'text-primary'
                                : 'text-muted-foreground',
                        ]"
                        :stroke-width="item.isActive ? 2.5 : 2"
                    />

                    <!-- Badge dengan bounce animation -->
                    <AnimatePresence>
                        <Motion
                            v-if="item.badge && item.badge > 0"
                            :initial="{ scale: 0 }"
                            :animate="{
                                scale: (badgeBounce && item.badgeType === 'cart') || (orderBadgeBounce && item.badgeType === 'orders') ? [1, 1.3, 1] : 1,
                            }"
                            :exit="{ scale: 0 }"
                            :transition="bouncyTransition"
                            class="absolute -right-2.5 -top-1.5"
                        >
                            <Badge
                                :class="[
                                    'flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold shadow-sm ring-2 ring-background',
                                    item.badgeType === 'orders'
                                        ? 'bg-gradient-to-r from-brand-gold-500 to-brand-gold-600 text-white'
                                        : 'bg-primary text-primary-foreground',
                                ]"
                            >
                                {{ item.badge > 99 ? '99+' : item.badge }}
                            </Badge>
                        </Motion>
                    </AnimatePresence>
                </Motion>

                <!-- Label dengan fade animation -->
                <span
                    class="text-[11px] font-medium transition-all duration-200"
                    :class="[
                        item.isActive
                            ? 'text-primary font-semibold'
                            : 'text-muted-foreground',
                    ]"
                >
                    {{ item.title }}
                </span>

                <!-- Active indicator dot -->
                <AnimatePresence>
                    <Motion
                    v-if="item.isActive"
                    :initial="{ scale: 0, opacity: 0 }"
                        :animate="{ scale: 1, opacity: 1 }"
                        :exit="{ scale: 0, opacity: 0 }"
                        :transition="bouncyTransition"
                    class="absolute -bottom-0.5 h-1 w-1 rounded-full bg-primary"
                />
                </AnimatePresence>
            </Link>
        </div>
    </nav>
</template>
