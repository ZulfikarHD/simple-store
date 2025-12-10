<script setup lang="ts">
/**
 * StoreFooter Component
 * Footer untuk halaman user dengan nama toko dan tagline dinamis
 * menggunakan store branding dari shared props
 *
 * @author Zulfikar Hidayatullah
 */
import { usePage } from '@inertiajs/vue3'
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
</script>

<template>
    <footer class="mt-16 hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background md:block">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center gap-2">
                <p class="text-center text-sm text-muted-foreground">
                    &copy; {{ new Date().getFullYear() }} {{ store.name }}. Created By Zulfikar Hidayatullah.
                </p>
                <p class="text-xs text-brand-gold">{{ store.tagline }}</p>
            </div>
        </div>
    </footer>
</template>

