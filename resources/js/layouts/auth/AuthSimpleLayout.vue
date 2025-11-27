<script setup lang="ts">
/**
 * AuthSimpleLayout - Layout untuk halaman autentikasi
 * Dengan iOS-like design, spring animations, dan glass effects
 *
 * @author Zulfikar Hidayatullah
 */
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
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

        <!-- Main content -->
        <div
            v-motion
            :initial="{ opacity: 0, y: 30, scale: 0.95 }"
            :enter="{
                opacity: 1,
                y: 0,
                scale: 1,
                transition: {
                    type: 'spring',
                    stiffness: 300,
                    damping: 25,
                },
            }"
            class="relative w-full max-w-sm"
        >
            <!-- iOS-style card container -->
            <div class="ios-card rounded-3xl border border-border/50 bg-card/80 p-8 shadow-xl backdrop-blur-xl sm:p-10">
                <div class="flex flex-col gap-8">
                    <!-- Logo section with animation -->
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            v-motion
                            :initial="{ scale: 0, rotate: -180 }"
                            :enter="{
                                scale: 1,
                                rotate: 0,
                                transition: {
                                    type: 'spring',
                                    stiffness: 400,
                                    damping: 15,
                                    delay: 100,
                                },
                            }"
                            class="ios-button flex flex-col items-center gap-2 font-medium"
                        >
                            <div
                                class="mb-1 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary shadow-lg transition-transform duration-200 hover:scale-105"
                            >
                                <AppLogoIcon
                                    class="size-8 fill-current text-primary-foreground"
                                />
                            </div>
                            <span class="sr-only">{{ title }}</span>
                        </Link>

                        <!-- Title and description with staggered animation -->
                        <div class="space-y-2 text-center">
                            <h1
                                v-motion
                                :initial="{ opacity: 0, y: 10 }"
                                :enter="{
                                    opacity: 1,
                                    y: 0,
                                    transition: {
                                        type: 'spring',
                                        stiffness: 300,
                                        damping: 25,
                                        delay: 200,
                                    },
                                }"
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{ title }}
                            </h1>
                            <p
                                v-motion
                                :initial="{ opacity: 0, y: 10 }"
                                :enter="{
                                    opacity: 1,
                                    y: 0,
                                    transition: {
                                        type: 'spring',
                                        stiffness: 300,
                                        damping: 25,
                                        delay: 250,
                                    },
                                }"
                                class="text-center text-sm text-muted-foreground"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <!-- Form slot with animation -->
                    <div
                        v-motion
                        :initial="{ opacity: 0, y: 20 }"
                        :enter="{
                            opacity: 1,
                            y: 0,
                            transition: {
                                type: 'spring',
                                stiffness: 300,
                                damping: 25,
                                delay: 300,
                            },
                        }"
                    >
                        <slot />
                    </div>
                </div>
            </div>

            <!-- Footer branding -->
            <p
                v-motion
                :initial="{ opacity: 0 }"
                :enter="{
                    opacity: 1,
                    transition: {
                        delay: 500,
                        duration: 300,
                    },
                }"
                class="mt-6 text-center text-xs text-muted-foreground/60"
            >
                &copy; {{ new Date().getFullYear() }} Simple Store
            </p>
        </div>
    </div>
</template>
