<script setup lang="ts">
/**
 * AppSidebarLayout Component
 * Layout utama admin panel dengan iOS-like design, yaitu:
 * - Sidebar navigation dengan glass effect untuk desktop
 * - Bottom navigation dengan frosted glass untuk mobile
 * - NewOrderAlert untuk notifikasi pesanan baru
 * - Safe area support untuk iOS devices
 *
 * @author Zulfikar Hidayatullah
 */
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import NewOrderAlert from '@/components/admin/NewOrderAlert.vue'
import AdminBottomNav from '@/components/admin/AdminBottomNav.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})
</script>

<template>
    <AppShell variant="sidebar" class="bg-gradient-to-br from-background via-background to-muted/20">
        <!-- Desktop Sidebar dengan subtle glass effect -->
        <AppSidebar class="ios-sidebar" />

        <!-- Main Content Area dengan iOS-style spacing -->
        <AppContent
            variant="sidebar"
            class="ios-content overflow-x-hidden pb-20 md:pb-0"
        >
            <!-- Header dengan glass effect -->
            <AppSidebarHeader :breadcrumbs="breadcrumbs" class="ios-header" />

            <!-- New Order Alert Banner untuk mobile -->
            <NewOrderAlert />

            <!-- Content dengan smooth transitions -->
            <div class="ios-page-transition">
                <slot />
            </div>
        </AppContent>

        <!-- Admin Bottom Navigation dengan frosted glass untuk mobile -->
        <AdminBottomNav class="ios-tabbar" />
    </AppShell>
</template>

<style scoped>
/**
 * iOS-style layout enhancements
 * Menggunakan CSS variables dari app.css untuk konsistensi
 */
.ios-sidebar {
    /* Glass effect untuk sidebar */
    @apply backdrop-blur-xl bg-card/95;
}

.ios-content {
    /* Smooth scrolling dengan momentum */
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
}

.ios-header {
    /* Sticky header dengan glass effect */
    @apply sticky top-0 z-40 backdrop-blur-xl bg-background/80;
}

.ios-page-transition {
    /* Page content transition */
    animation: ios-fade-in 0.3s ease-out;
}

@keyframes ios-fade-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Safe area padding untuk iOS devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .ios-content {
        padding-bottom: calc(5rem + env(safe-area-inset-bottom));
    }

    @media (min-width: 768px) {
        .ios-content {
            padding-bottom: 0;
        }
    }
}
</style>
