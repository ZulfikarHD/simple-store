<script setup lang="ts">
/**
 * UserBottomNav Component
 * Bottom navigation untuk user/storefront pada mobile view
 * dengan badge untuk cart items count
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { home } from '@/routes'
import { show as cartShow } from '@/routes/cart'
import {
    Home,
    ShoppingCart,
    User,
} from 'lucide-vue-next'

const page = usePage()

/**
 * Computed untuk mendapatkan jumlah item di cart
 */
const cartItemsCount = computed(() => {
    const cart = (page.props as { cart?: { total_items: number } }).cart
    return cart?.total_items ?? 0
})

/**
 * Computed untuk current route matching
 */
const currentPath = computed(() => page.url)

/**
 * Computed untuk cek apakah user sudah login
 */
const isAuthenticated = computed(() => {
    return !!(page.props as { auth?: { user?: unknown } }).auth?.user
})

/**
 * Navigation items untuk bottom nav
 */
const navItems = computed(() => [
    {
        title: 'Beranda',
        href: home(),
        icon: Home,
        badge: 0,
        isActive: currentPath.value === '/' || currentPath.value.startsWith('/products'),
    },
    {
        title: 'Keranjang',
        href: cartShow(),
        icon: ShoppingCart,
        badge: cartItemsCount.value,
        isActive: currentPath.value.startsWith('/cart') || currentPath.value.startsWith('/checkout'),
    },
    {
        title: 'Akun',
        href: '/account',
        icon: User,
        badge: 0,
        isActive: currentPath.value.startsWith('/account') || currentPath.value.startsWith('/settings') || currentPath.value.startsWith('/login') || currentPath.value.startsWith('/register'),
    },
])
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 md:hidden">
        <!-- Safe area padding untuk iPhone -->
        <div class="flex items-center justify-around pb-[env(safe-area-inset-bottom)]">
            <Link
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                class="relative flex flex-1 flex-col items-center gap-1 py-3 transition-colors"
                :class="[
                    item.isActive
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground',
                ]"
            >
                <!-- Icon Container with Badge -->
                <div class="relative">
                    <component
                        :is="item.icon"
                        class="h-6 w-6"
                        :class="{ 'text-primary': item.isActive }"
                    />
                    <!-- Badge untuk cart items -->
                    <Badge
                        v-if="item.badge && item.badge > 0"
                        class="absolute -right-2 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </Badge>
                </div>

                <!-- Label -->
                <span
                    class="text-xs font-medium"
                    :class="{ 'text-primary': item.isActive }"
                >
                    {{ item.title }}
                </span>
            </Link>
        </div>
    </nav>
</template>

