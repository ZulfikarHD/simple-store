<script setup lang="ts">
/**
 * CartItem Component
 * Menampilkan item dalam keranjang belanja dengan kontrol kuantitas
 * termasuk fitur increment, decrement, dan remove item
 * dengan integrasi Inertia untuk operasi cart
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Minus, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import { update, destroy } from '@/actions/App/Http/Controllers/CartController'

/**
 * Props definition untuk CartItem
 */
interface Props {
    item: {
        id: number
        product: {
            id: number
            name: string
            slug: string
            price: number
            image?: string | null
            is_available: boolean
        }
        quantity: number
        subtotal: number
    }
}

const props = defineProps<Props>()

/**
 * Loading states untuk operasi cart
 */
const isUpdating = ref(false)
const isRemoving = ref(false)

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
 * Format subtotal ke format Rupiah Indonesia
 */
const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.item.subtotal)
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

/**
 * Handler untuk increment quantity
 */
function handleIncrement() {
    if (isUpdating.value) return

    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity + 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk decrement quantity
 */
function handleDecrement() {
    if (isUpdating.value || props.item.quantity <= 1) return

    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity - 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk remove item dari cart
 */
function handleRemove() {
    if (isRemoving.value) return

    isRemoving.value = true
    router.delete(destroy.url(props.item.id), {
        preserveScroll: true,
        onFinish: () => {
            isRemoving.value = false
        },
    })
}
</script>

<template>
    <div
        class="flex items-center gap-4 rounded-xl bg-card p-4 transition-opacity"
        :class="{ 'opacity-50': isRemoving }"
    >
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
            <!-- Availability Warning -->
            <p
                v-if="!item.product.is_available"
                class="text-xs text-destructive"
            >
                Produk tidak tersedia
            </p>
        </div>

        <!-- Quantity Controls -->
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="item.quantity <= 1 || isUpdating"
                @click="handleDecrement"
            >
                <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                <Minus v-else class="h-4 w-4" />
            </Button>

            <span class="w-8 text-center font-medium">
                {{ item.quantity }}
            </span>

            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="isUpdating"
                @click="handleIncrement"
            >
                <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                <Plus v-else class="h-4 w-4" />
            </Button>
        </div>

        <!-- Subtotal & Remove -->
        <div class="flex flex-col items-end gap-2">
            <p class="font-bold text-primary">
                {{ formattedSubtotal }}
            </p>
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                :disabled="isRemoving"
                @click="handleRemove"
            >
                <Loader2 v-if="isRemoving" class="h-4 w-4 animate-spin" />
                <Trash2 v-else class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
