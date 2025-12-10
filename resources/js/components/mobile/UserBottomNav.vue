<script setup lang="ts">
/**
 * UserBottomNav Component
 * iOS-style bottom navigation dengan spring animations,
 * haptic feedback, dan badge bounce animations
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { home } from '@/routes'
import { show as cartShow } from '@/routes/cart'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    Home,
    ShoppingCart,
    User,
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

/**
 * Computed untuk mendapatkan jumlah item di cart
 */
const cartItemsCount = computed(() => {
    const cart = (page.props as { cart?: { total_items: number } }).cart
    return cart?.total_items ?? 0
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
 * Computed untuk current route matching
 */
const currentPath = computed(() => page.url)

/**
 * Navigation items untuk bottom nav
 */
const navItems = computed(() => [
    {
        id: 'home',
        title: 'Beranda',
        href: home(),
        icon: Home,
        badge: 0,
        isActive: currentPath.value === '/' || currentPath.value.startsWith('/products'),
    },
    {
        id: 'cart',
        title: 'Keranjang',
        href: cartShow(),
        icon: ShoppingCart,
        badge: cartItemsCount.value,
        isActive: currentPath.value.startsWith('/cart') || currentPath.value.startsWith('/checkout'),
    },
    {
        id: 'account',
        title: 'Akun',
        href: '/account',
        icon: User,
        badge: 0,
        isActive: currentPath.value.startsWith('/account') || currentPath.value.startsWith('/settings') || currentPath.value.startsWith('/login') || currentPath.value.startsWith('/register'),
    },
])

/**
 * Handle press start dengan haptic feedback
 */
function handlePressStart() {
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
                class="relative flex flex-1 flex-col items-center gap-1 py-3 transition-all duration-150 ease-[var(--ios-spring-snappy)]"
                :class="[
                    item.isActive
                        ? 'text-primary'
                        : 'text-muted-foreground',
                    pressedItem === item.id ? 'scale-90 opacity-70' : 'scale-100 opacity-100',
                ]"
                @mousedown="handlePressStart(item.id)"
                @mouseup="handlePressEnd"
                @mouseleave="handlePressEnd"
                @touchstart.passive="handlePressStart(item.id)"
                @touchend="handlePressEnd"
                @touchcancel="handlePressEnd"
                @click="handleNavigation()"
            >
                <!-- Icon Container with Spring Animation -->
                <div
                    class="relative transition-transform duration-200 ease-[var(--ios-spring-bounce)]"
                    :class="{ 'scale-110': item.isActive }"
                >
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
                    <Badge
                        v-if="item.badge && item.badge > 0"
                        v-motion
                        :initial="{ scale: 0 }"
                        :enter="{
                            scale: 1,
                            transition: {
                                type: 'spring',
                                stiffness: 500,
                                damping: 15,
                            },
                        }"
                        class="absolute -right-2.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[10px] font-bold text-primary-foreground shadow-sm ring-2 ring-background transition-transform duration-300"
                        :class="{
                            'animate-ios-badge': badgeBounce && item.id === 'cart',
                        }"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </Badge>
                </div>

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
                <div
                    v-if="item.isActive"
                    v-motion
                    :initial="{ scale: 0, opacity: 0 }"
                    :enter="{
                        scale: 1,
                        opacity: 1,
                        transition: {
                            type: 'spring',
                            stiffness: 400,
                            damping: 20,
                        },
                    }"
                    class="absolute -bottom-0.5 h-1 w-1 rounded-full bg-primary"
                />
            </Link>
        </div>
    </nav>
</template>
