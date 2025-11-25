<script setup lang="ts">
/**
 * PriceDisplay Component
 * Menampilkan harga dalam format Rupiah dengan opsi harga coret
 * untuk menampilkan diskon atau harga original
 */
import { computed } from 'vue'

interface Props {
    /** Harga yang ditampilkan (dalam Rupiah) */
    price: number
    /** Harga original sebelum diskon (opsional) */
    originalPrice?: number
    /** Size variant */
    size?: 'sm' | 'md' | 'lg' | 'xl'
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
})

/**
 * Format harga ke format Rupiah Indonesia
 */
function formatPrice(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}

const formattedPrice = computed(() => formatPrice(props.price))
const formattedOriginalPrice = computed(() =>
    props.originalPrice ? formatPrice(props.originalPrice) : null,
)

/**
 * Hitung persentase diskon
 */
const discountPercentage = computed(() => {
    if (!props.originalPrice || props.originalPrice <= props.price) return null
    const discount = ((props.originalPrice - props.price) / props.originalPrice) * 100
    return Math.round(discount)
})

/**
 * Size classes untuk berbagai ukuran
 */
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl',
    }
    return sizes[props.size]
})
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <!-- Current Price -->
        <span
            class="font-bold text-primary"
            :class="sizeClasses"
        >
            {{ formattedPrice }}
        </span>

        <!-- Original Price (Strikethrough) -->
        <span
            v-if="formattedOriginalPrice"
            class="text-sm text-muted-foreground line-through"
        >
            {{ formattedOriginalPrice }}
        </span>

        <!-- Discount Badge -->
        <span
            v-if="discountPercentage"
            class="rounded-full bg-destructive/10 px-2 py-0.5 text-xs font-medium text-destructive"
        >
            -{{ discountPercentage }}%
        </span>
    </div>
</template>

