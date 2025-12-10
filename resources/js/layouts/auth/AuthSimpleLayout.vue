<script setup lang="ts">
/**
 * AuthSimpleLayout - Layout untuk halaman autentikasi
 * Dengan iOS-like design menggunakan motion-v, spring animations, dan glass effects
 * Logo dinamis dari store settings
 *
 * @author Zulfikar Hidayatullah
 */
import { Motion } from 'motion-v'
import { home } from '@/routes'
import { Link, usePage } from '@inertiajs/vue3'
import { springPresets } from '@/composables/useMotionV'
import { computed } from 'vue'
import { ShoppingBag } from 'lucide-vue-next'

/**
 * Interface untuk store branding dari shared props
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

defineProps<{
    title?: string
    description?: string
}>()

const page = usePage()

/**
 * Computed untuk mendapatkan data store dari shared props
 */
const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-gradient-to-br from-background via-background to-muted/30 p-6 md:p-10"
    >
        <!-- Decorative background elements -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <!-- Gradient orbs for iOS-like depth -->
            <div
                class="absolute -left-32 -top-32 h-64 w-64 rounded-full bg-primary/10 blur-3xl"
            />
            <div
                class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-3xl"
            />
        </div>

        <!-- Main content dengan spring animation -->
        <Motion
            :initial="{ opacity: 0, y: 30, scale: 0.95 }"
            :animate="{ opacity: 1, y: 0, scale: 1 }"
            :transition="springTransition"
            class="relative w-full max-w-sm"
        >
            <!-- iOS-style card container -->
            <div class="ios-card rounded-3xl border border-border/50 bg-card/80 p-8 shadow-xl backdrop-blur-xl sm:p-10">
                <div class="flex flex-col gap-8">
                    <!-- Logo section with bouncy animation -->
                    <div class="flex flex-col items-center gap-4">
                        <Motion
                            :initial="{ scale: 0, rotate: -180 }"
                            :animate="{ scale: 1, rotate: 0 }"
                            :transition="{ ...bouncyTransition, delay: 0.1 }"
                        >
                            <Link
                                :href="home()"
                                class="ios-button flex flex-col items-center gap-2 font-medium"
                            >
                                <!-- Logo dinamis dari store settings tanpa background wrapper -->
                                <div class="mb-1 flex h-14 w-14 items-center justify-center transition-transform duration-200 hover:scale-105">
                                    <img
                                        v-if="store.logo"
                                        :src="`/storage/${store.logo}`"
                                        :alt="store.name"
                                        class="h-14 w-14 rounded-2xl object-contain"
                                    />
                                    <div
                                        v-else
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary shadow-lg"
                                    >
                                        <ShoppingBag class="h-7 w-7 text-primary-foreground" />
                                    </div>
                                </div>
                                <span class="sr-only">{{ store.name }}</span>
                            </Link>
                        </Motion>

                        <!-- Title and description with staggered animation -->
                        <div class="space-y-2 text-center">
                            <Motion
                                tag="h1"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ ...springTransition, delay: 0.2 }"
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{ title }}
                            </Motion>
                            <Motion
                                tag="p"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ ...springTransition, delay: 0.25 }"
                                class="text-center text-sm text-muted-foreground"
                            >
                                {{ description }}
                            </Motion>
                        </div>
                    </div>

                    <!-- Form slot with animation -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.3 }"
                    >
                        <slot />
                    </Motion>
                </div>
            </div>

            <!-- Footer branding -->
            <Motion
                tag="p"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ delay: 0.5, duration: 0.3 }"
                class="mt-6 text-center text-xs text-muted-foreground/60"
            >
                &copy; {{ new Date().getFullYear() }} {{ store.name }}
            </Motion>
        </Motion>
    </div>
</template>
