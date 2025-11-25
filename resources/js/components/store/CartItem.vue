<script setup lang="ts">
/**
 * CartItem Component
 * Menampilkan item dalam keranjang belanja dengan kontrol kuantitas
 * termasuk fitur increment, decrement, dan remove item
 */
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Minus, Plus, Trash2 } from 'lucide-vue-next'

/**
 * Props definition untuk CartItem
 */
interface Props {
    item: {
        id: number
        product: {
            id: number
            name: string
            price: number
            image?: string | null
        }
        quantity: number
    }
}

const props = defineProps<Props>()

/**
 * Emit events untuk cart operations
 */
const emit = defineEmits<{
    increment: [itemId: number]
    decrement: [itemId: number]
    remove: [itemId: number]
}>()

/**
 * Format harga ke format Rupiah Indonesia
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.item.product.price)
})

/**
 * Hitung subtotal untuk item ini
 */
const subtotal = computed(() => {
    const total = props.item.product.price * props.item.quantity
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(total)
})

/**
 * Generate URL gambar produk
 */
const imageUrl = computed(() => {
    if (props.item.product.image) {
        return `/storage/${props.item.product.image}`
    }
    return null
})
</script>

<template>
    <div class="flex items-center gap-4 rounded-xl bg-card p-4">
        <!-- Product Image -->
        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-muted">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="item.product.name"
                class="h-full w-full object-cover"
                loading="lazy"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center"
            >
                <span class="text-2xl">🛒</span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="flex flex-1 flex-col gap-1">
            <h4 class="line-clamp-1 font-semibold text-foreground">
                {{ item.product.name }}
            </h4>
            <p class="text-sm text-muted-foreground">
                {{ formattedPrice }}
            </p>
        </div>

        <!-- Quantity Controls -->
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="item.quantity <= 1"
                @click="emit('decrement', item.id)"
            >
                <Minus class="h-4 w-4" />
            </Button>

            <span class="w-8 text-center font-medium">
                {{ item.quantity }}
            </span>

            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                @click="emit('increment', item.id)"
            >
                <Plus class="h-4 w-4" />
            </Button>
        </div>

        <!-- Subtotal & Remove -->
        <div class="flex flex-col items-end gap-2">
            <p class="font-bold text-primary">
                {{ subtotal }}
            </p>
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                @click="emit('remove', item.id)"
            >
                <Trash2 class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

