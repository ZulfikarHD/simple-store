<script setup lang="ts">
/**
 * AdminBottomNav Component
 * Bottom navigation untuk admin panel pada mobile view
 * dengan badge untuk pending orders count
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { dashboard } from '@/routes/admin'
import { index as ordersIndex } from '@/routes/admin/orders'
import { index as productsIndex } from '@/routes/admin/products'
import { index as categoriesIndex } from '@/routes/admin/categories'
import { index as settingsIndex } from '@/routes/admin/settings'
import {
    LayoutGrid,
    ShoppingBag,
    Package,
    FolderTree,
    Settings,
    MoreHorizontal,
} from 'lucide-vue-next'

const page = usePage()

/**
 * Computed untuk mendapatkan jumlah pesanan pending dari shared props
 */
const pendingOrdersCount = computed(() => {
    return (page.props as { pending_orders_count?: number }).pending_orders_count ?? 0
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
        title: 'Pesanan',
        href: ordersIndex().url,
        icon: ShoppingBag,
        badge: pendingOrdersCount.value,
        isActive: currentPath.value.startsWith('/admin/orders'),
    },
    {
        title: 'Produk',
        href: productsIndex().url,
        icon: Package,
        badge: 0,
        isActive: currentPath.value.startsWith('/admin/products'),
    },
    {
        title: 'Kategori',
        href: categoriesIndex().url,
        icon: FolderTree,
        badge: 0,
        isActive: currentPath.value.startsWith('/admin/categories'),
    },
    {
        title: 'Lainnya',
        href: settingsIndex().url,
        icon: MoreHorizontal,
        badge: 0,
        isActive: currentPath.value.startsWith('/admin/settings') || currentPath.value === '/admin/dashboard',
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
                class="relative flex flex-1 flex-col items-center gap-1 py-2 transition-colors"
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
                        class="h-5 w-5"
                        :class="{ 'text-primary': item.isActive }"
                    />
                    <!-- Badge untuk pending orders -->
                    <Badge
                        v-if="item.badge && item.badge > 0"
                        class="absolute -right-2.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-medium text-destructive-foreground"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </Badge>
                </div>

                <!-- Label -->
                <span
                    class="text-[10px] font-medium"
                    :class="{ 'text-primary': item.isActive }"
                >
                    {{ item.title }}
                </span>

                <!-- Active Indicator -->
                <div
                    v-if="item.isActive"
                    class="absolute inset-x-4 top-0 h-0.5 rounded-full bg-primary"
                />
            </Link>
        </div>
    </nav>
</template>

