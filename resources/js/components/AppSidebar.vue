<script setup lang="ts">
/**
 * AppSidebar Component
 * Sidebar admin panel dengan navigation dan badge notifikasi pesanan
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';
import { index as productsIndex } from '@/routes/admin/products';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as ordersIndex } from '@/routes/admin/orders';
import { index as settingsIndex } from '@/routes/admin/settings';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Package, FolderTree, ShoppingBag, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

/**
 * Computed untuk mendapatkan jumlah pesanan pending dari shared props
 */
const pendingOrdersCount = computed(() => {
    return (page.props as { pending_orders_count?: number }).pending_orders_count ?? 0;
});

/**
 * Main navigation items dengan badge untuk pesanan
 * Grouping: Utama (Dashboard, Pesanan) -> Katalog (Produk, Kategori) -> Sistem (Pengaturan)
 */
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Pesanan',
        href: ordersIndex(),
        icon: ShoppingBag,
        badge: pendingOrdersCount.value,
    },
    {
        title: 'Produk',
        href: productsIndex(),
        icon: Package,
    },
    {
        title: 'Kategori',
        href: categoriesIndex(),
        icon: FolderTree,
    },
    {
        title: 'Pengaturan',
        href: settingsIndex(),
        icon: Settings,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
