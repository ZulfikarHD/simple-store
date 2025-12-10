<script setup lang="ts">
/**
 * AuthSplitLayout - Layout autentikasi dengan split view design
 * iOS-like design dengan glass effects, spring animations, dan gradient overlays
 *
 * @author Zulfikar Hidayatullah
 */
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { home } from '@/routes'
import { Link, usePage } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'

const page = usePage()
const name = page.props.name
const quote = page.props.quote

defineProps<{
    title?: string
    description?: string
}>()

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
</script>

<template>
    <div
        class="relative grid min-h-dvh flex-col items-center justify-center px-6 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <!-- Left Panel: Brand Section dengan iOS-style gradient -->
        <div
            class="relative hidden h-full flex-col overflow-hidden bg-zinc-900 p-10 text-white lg:flex dark:border-r dark:border-border/50"
        >
            <!-- Gradient overlay untuk depth -->
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900" />

            <!-- Decorative orbs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-primary/20 blur-3xl" />
                <div class="absolute -bottom-20 -right-20 h-80 w-80 rounded-full bg-primary/10 blur-3xl" />
            </div>

            <!-- Logo dengan animation -->
            <Motion
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="springTransition"
            >
                <Link
                    :href="home()"
                    class="ios-press relative z-20 flex items-center gap-3 text-lg font-medium transition-transform active:scale-[0.97]"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm">
                        <AppLogoIcon class="size-6 fill-current text-white" />
                    </div>
                    <span class="font-bold tracking-tight">{{ name }}</span>
                </Link>
            </Motion>

            <!-- Quote section dengan staggered animation -->
            <Motion
                v-if="quote"
                :initial="{ opacity: 0, y: 30 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springTransition, delay: 0.2 }"
                class="relative z-20 mt-auto"
            >
                <blockquote class="space-y-3">
                    <p class="text-xl font-light leading-relaxed opacity-90">
                        &ldquo;{{ quote.message }}&rdquo;
                    </p>
                    <footer class="text-sm font-medium text-neutral-400">
                        — {{ quote.author }}
                    </footer>
                </blockquote>
            </Motion>
        </div>

        <!-- Right Panel: Form Section dengan iOS-style design -->
        <div class="relative flex items-center justify-center bg-gradient-to-br from-background via-background to-muted/20 lg:p-8">
            <!-- Decorative orbs untuk mobile -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden lg:hidden">
                <div class="absolute -left-16 -top-16 h-48 w-48 rounded-full bg-primary/8 blur-3xl" />
                <div class="absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-primary/5 blur-3xl" />
            </div>

            <Motion
                :initial="{ opacity: 0, y: 30, scale: 0.95 }"
                :animate="{ opacity: 1, y: 0, scale: 1 }"
                :transition="springTransition"
                class="relative mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[380px]"
            >
                <!-- Mobile Logo -->
                <Motion
                    :initial="{ opacity: 0, scale: 0.8 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    class="flex justify-center lg:hidden"
                >
                    <Link
                        :href="home()"
                        class="ios-press flex items-center gap-2 transition-transform active:scale-[0.97]"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary shadow-lg">
                            <AppLogoIcon class="size-7 fill-current text-primary-foreground" />
                        </div>
                    </Link>
                </Motion>

                <!-- Title Section dengan animation -->
                <div class="flex flex-col space-y-2 text-center">
                    <Motion
                        v-if="title"
                        tag="h1"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.15 }"
                        class="text-2xl font-bold tracking-tight"
                    >
                        {{ title }}
                    </Motion>
                    <Motion
                        v-if="description"
                        tag="p"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.2 }"
                        class="text-sm text-muted-foreground"
                    >
                        {{ description }}
                    </Motion>
                </div>

                <!-- Form Slot dengan animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.25 }"
                >
                    <slot />
                </Motion>
            </Motion>
        </div>
    </div>
</template>

<style scoped>
/**
 * iOS-style split layout enhancements
 */
.ios-press {
    transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.ios-press:active {
    transform: scale(0.97);
}
</style>
