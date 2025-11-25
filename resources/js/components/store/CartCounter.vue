<script setup lang="ts">
/**
 * CartCounter Component
 * Menampilkan badge counter untuk jumlah item di keranjang belanja
 * dengan link ke halaman cart dan animasi update
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import { show } from '@/actions/App/Http/Controllers/CartController'

/**
 * Props definition untuk CartCounter
 */
interface Props {
    /** Jumlah total item di keranjang */
    count: number
}

const props = withDefaults(defineProps<Props>(), {
    count: 0,
})

/**
 * Generate URL halaman cart menggunakan Wayfinder
 */
const cartUrl = computed(() => show.url())

/**
 * Computed untuk menampilkan counter dengan format yang benar
 * Jika lebih dari 99, tampilkan 99+
 */
const displayCount = computed(() => {
    if (props.count > 99) return '99+'
    return props.count.toString()
})

/**
 * Computed untuk menentukan apakah cart kosong
 */
const isEmpty = computed(() => props.count === 0)
</script>

<template>
    <Link
        :href="cartUrl"
        class="relative flex items-center justify-center rounded-lg p-2 text-foreground transition-colors hover:bg-accent"
        :aria-label="`Keranjang belanja, ${count} item`"
    >
        <ShoppingCart class="h-5 w-5" />

        <!-- Badge Counter -->
        <span
            v-if="!isEmpty"
            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-bold text-primary-foreground"
        >
            {{ displayCount }}
        </span>
    </Link>
</template>

