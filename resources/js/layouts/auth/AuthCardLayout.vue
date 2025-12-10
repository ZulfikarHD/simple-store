<script setup lang="ts">
/**
 * AuthCardLayout - Layout untuk halaman autentikasi dengan iOS-style card
 * Menggunakan spring animations, glass effects, dan gradient background
 *
 * @author Zulfikar Hidayatullah
 */
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { home } from '@/routes'
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'

defineProps<{
    title?: string
    description?: string
}>()

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-gradient-to-br from-muted via-background to-muted/50 p-6 md:p-10"
    >
        <!-- Decorative background orbs untuk iOS-like depth -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -left-32 -top-32 h-64 w-64 rounded-full bg-primary/8 blur-3xl"
            />
            <div
                class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-primary/5 blur-3xl"
            />
            <div
                class="absolute left-1/2 top-1/2 h-48 w-48 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary/3 blur-3xl"
            />
        </div>

        <!-- Main content dengan spring animation -->
        <Motion
            :initial="{ opacity: 0, y: 30, scale: 0.95 }"
            :animate="{ opacity: 1, y: 0, scale: 1 }"
            :transition="springTransition"
            class="relative flex w-full max-w-md flex-col gap-6"
        >
            <!-- Logo dengan bouncy animation -->
            <Motion
                :initial="{ scale: 0, rotate: -180 }"
                :animate="{ scale: 1, rotate: 0 }"
                :transition="{ ...bouncyTransition, delay: 0.1 }"
            >
                <Link
                    :href="home()"
                    class="ios-press flex items-center gap-2 self-center font-medium transition-transform active:scale-[0.97]"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary shadow-lg">
                        <AppLogoIcon
                            class="size-7 fill-current text-primary-foreground"
                        />
                    </div>
                </Link>
            </Motion>

            <!-- iOS-style Card dengan glass effect -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springTransition, delay: 0.15 }"
            >
                <Card class="ios-card overflow-hidden rounded-3xl border border-border/50 bg-card/90 shadow-xl backdrop-blur-xl">
                    <CardHeader class="px-8 pt-8 pb-0 text-center sm:px-10">
                        <Motion
                            tag="div"
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.2 }"
                        >
                            <CardTitle class="text-2xl font-bold tracking-tight">{{ title }}</CardTitle>
                        </Motion>
                        <Motion
                            tag="div"
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.25 }"
                        >
                            <CardDescription class="text-sm text-muted-foreground">
                                {{ description }}
                            </CardDescription>
                        </Motion>
                    </CardHeader>
                    <CardContent class="px-8 py-8 sm:px-10">
                        <Motion
                            :initial="{ opacity: 0, y: 15 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.3 }"
                        >
                            <slot />
                        </Motion>
                    </CardContent>
                </Card>
            </Motion>

            <!-- Footer branding -->
            <Motion
                tag="p"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ delay: 0.5, duration: 0.3 }"
                class="text-center text-xs text-muted-foreground/60"
            >
                &copy; {{ new Date().getFullYear() }} Simple Store
            </Motion>
        </Motion>
    </div>
</template>

<style scoped>
/**
 * iOS-style auth card enhancements
 */
.ios-card {
    /* Enhanced shadow for card depth */
    --tw-shadow: 0 8px 32px oklch(0 0 0 / 0.08), 0 2px 8px oklch(0 0 0 / 0.04);
    box-shadow: var(--tw-shadow);
}

.ios-press {
    transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.ios-press:active {
    transform: scale(0.97);
}
</style>
