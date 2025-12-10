<script setup lang="ts">
/**
 * AppHeaderLayout Component
 * Layout dengan header navigation dan iOS-like design, yaitu:
 * - Header dengan frosted glass effect
 * - Smooth page transitions dengan spring physics
 * - Gradient background untuk visual depth
 *
 * @author Zulfikar Hidayatullah
 */
import AppContent from '@/components/AppContent.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppShell from '@/components/AppShell.vue'
import { Motion } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})
</script>

<template>
    <AppShell class="flex-col bg-gradient-to-br from-background via-background to-muted/20">
        <!-- iOS-style Header dengan glass effect -->
        <AppHeader
            :breadcrumbs="breadcrumbs"
            class="ios-header sticky top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl"
        />

        <!-- Main Content dengan smooth animation -->
        <Motion
            :initial="{ opacity: 0, y: 16 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="springPresets.ios"
        >
            <AppContent class="ios-content">
                <slot />
            </AppContent>
        </Motion>
    </AppShell>
</template>

<style scoped>
/**
 * iOS-style header layout enhancements
 */
.ios-header {
    /* Subtle shadow untuk depth */
    --tw-shadow: 0 1px 3px oklch(0 0 0 / 0.05);
    box-shadow: var(--tw-shadow);
}

.ios-content {
    /* Smooth scrolling */
    -webkit-overflow-scrolling: touch;
}
</style>
