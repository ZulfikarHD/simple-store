<script setup lang="ts">
/**
 * AppCustomerLayout Component
 * Layout untuk customer/user settings tanpa admin sidebar
 * Menggunakan header sederhana dengan navigation user-friendly
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { home } from '@/routes'
import { ShoppingBag } from 'lucide-vue-next'
import NavUser from '@/components/NavUser.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header untuk customer -->
        <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="container flex h-14 items-center">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center gap-2 font-semibold">
                    <ShoppingBag class="h-6 w-6" />
                    <span>Simple Store</span>
                </Link>

                <!-- Spacer -->
                <div class="flex-1" />

                <!-- User Menu -->
                <NavUser />
            </div>
        </header>

        <!-- Main Content -->
        <main class="container py-6">
            <slot />
        </main>
    </div>
</template>

