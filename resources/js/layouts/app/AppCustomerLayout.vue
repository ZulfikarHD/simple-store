<script setup lang="ts">
/**
 * AppCustomerLayout Component
 * Layout untuk customer/user settings dengan iOS-like design, yaitu:
 * - Header dengan frosted glass effect dan blur
 * - Dynamic store branding dari shared props
 * - Spring animations untuk smooth page transitions
 * - Safe area support untuk iOS devices
 * - Gradient background untuk depth
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { home } from '@/routes'
import { ShoppingBag } from 'lucide-vue-next'
import { Motion } from 'motion-v'
import NavUser from '@/components/NavUser.vue'
import { springPresets } from '@/composables/useMotionV'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const page = usePage()

/**
 * Interface dan computed untuk store branding
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})
</script>

<template>
    <div class="ios-app-container min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <!-- iOS-style Header dengan frosted glass -->
        <header class="ios-navbar sticky top-0 z-50 w-full border-b border-border/50 bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60">
            <div class="container flex h-14 items-center px-4 sm:px-6">
                <!-- Logo dengan press feedback dan dynamic branding -->
                <Motion
                    :initial="{ opacity: 0, x: -10 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springPresets.ios"
                >
                    <Link
                        :href="home()"
                        class="ios-press flex items-center gap-2.5 font-semibold transition-transform active:scale-[0.97]"
                    >
                        <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-primary shadow-sm">
                            <img
                                v-if="store.logo"
                                :src="`/storage/${store.logo}`"
                                :alt="store.name"
                                class="h-full w-full object-contain"
                            />
                            <ShoppingBag v-else class="h-5 w-5 text-primary-foreground" />
                        </div>
                        <span class="text-lg font-bold tracking-tight">{{ store.name }}</span>
                    </Link>
                </Motion>

                <!-- Spacer -->
                <div class="flex-1" />

                <!-- User Menu dengan animation -->
                <Motion
                    :initial="{ opacity: 0, x: 10 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: 0.1 }"
                >
                    <NavUser />
                </Motion>
            </div>
        </header>

        <!-- Main Content dengan smooth transition -->
        <Motion
            tag="main"
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ ...springPresets.ios, delay: 0.15 }"
            class="container px-4 py-6 sm:px-6 md:py-8"
        >
            <slot />
        </Motion>

        <!-- Bottom safe area untuk iOS -->
        <div class="ios-safe-bottom h-safe-area-bottom md:hidden" />
    </div>
</template>

<style scoped>
/**
 * iOS-style customer layout enhancements
 * Menggunakan glass effect dan spring animations
 */
.ios-app-container {
    /* Smooth scrolling dengan momentum */
    -webkit-overflow-scrolling: touch;
}

.ios-navbar {
    /* iOS navbar dengan glass effect */
    --tw-shadow: 0 1px 3px oklch(0 0 0 / 0.05);
    box-shadow: var(--tw-shadow);
}

/* Safe area untuk iOS devices */
.h-safe-area-bottom {
    height: env(safe-area-inset-bottom, 0px);
}

/* Press feedback animation */
.ios-press {
    transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.ios-press:active {
    transform: scale(0.97);
}
</style>
