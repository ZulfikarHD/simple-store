<script setup lang="ts">
/**
 * AppSidebarLayout Component
 * Layout utama admin panel dengan sidebar navigation untuk desktop
 * dan bottom navigation untuk mobile, termasuk NewOrderAlert
 * untuk notifikasi pesanan baru
 *
 * @author Zulfikar Hidayatullah
 */
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import NewOrderAlert from '@/components/admin/NewOrderAlert.vue';
import AdminBottomNav from '@/components/admin/AdminBottomNav.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden pb-16 md:pb-0">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <!-- New Order Alert Banner untuk mobile -->
            <NewOrderAlert />
            <slot />
        </AppContent>
        <!-- Admin Bottom Navigation untuk mobile -->
        <AdminBottomNav />
    </AppShell>
</template>
