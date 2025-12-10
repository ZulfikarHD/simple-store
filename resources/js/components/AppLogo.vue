<script setup lang="ts">
/**
 * AppLogo Component
 * Logo dan nama toko dinamis dari store settings
 * digunakan di sidebar admin panel
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
    <!-- Logo tanpa background wrapper -->
    <div class="flex size-8 shrink-0 items-center justify-center">
        <img
            v-if="store.logo"
            :src="`/storage/${store.logo}`"
            :alt="store.name"
            class="size-8 rounded-md object-contain"
        />
        <ShoppingBag v-else class="size-6 text-primary" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate font-semibold leading-tight">{{ store.name }}</span>
    </div>
</template>
