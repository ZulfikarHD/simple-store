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
        class="flex flex-col gap-3 rounded-lg bg-card p-3 transition-opacity sm:flex-row sm:items-center sm:gap-4 sm:rounded-xl sm:p-4"
        :class="{ 'opacity-50': isRemoving }"
    >
        <!-- Top Row: Image, Info & Remove (Mobile) -->
        <div class="flex items-start gap-3 sm:contents">
            <!-- Product Image -->
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-muted sm:h-20 sm:w-20">
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
                    <span class="text-xl sm:text-2xl">🛒</span>
                </div>
            </div>

            <!-- Product Info -->
            <div class="flex flex-1 flex-col gap-0.5 sm:gap-1">
                <h4 class="line-clamp-2 text-sm font-semibold text-foreground sm:line-clamp-1 sm:text-base">
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

            <!-- Remove Button - Mobile Only -->
            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 shrink-0 text-destructive hover:bg-destructive/10 hover:text-destructive sm:hidden"
                :disabled="isRemoving"
                aria-label="Hapus item"
                @click="handleRemove"
            >
                <Loader2 v-if="isRemoving" class="h-4 w-4 animate-spin" />
                <Trash2 v-else class="h-4 w-4" />
            </Button>
        </div>

        <!-- Bottom Row: Quantity & Subtotal (Mobile) -->
        <div class="flex items-center justify-between gap-3 sm:contents">
            <!-- Quantity Controls - Touch-friendly 44px -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <Button
                    variant="outline"
                    size="icon"
                    class="h-10 w-10 sm:h-8 sm:w-8"
                    :disabled="item.quantity <= 1 || isUpdating"
                    aria-label="Kurangi jumlah"
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
                    class="h-10 w-10 sm:h-8 sm:w-8"
                    :disabled="isUpdating"
                    aria-label="Tambah jumlah"
                    @click="handleIncrement"
                >
                    <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                    <Plus v-else class="h-4 w-4" />
                </Button>
            </div>

            <!-- Subtotal & Remove (Desktop) -->
            <div class="flex flex-col items-end gap-2">
                <p class="text-base font-bold text-primary sm:text-lg">
                    {{ formattedSubtotal }}
                </p>
                <Button
                    variant="ghost"
                    size="icon"
                    class="hidden h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive sm:flex"
                    :disabled="isRemoving"
                    aria-label="Hapus item"
                    @click="handleRemove"
                >
                    <Loader2 v-if="isRemoving" class="h-4 w-4 animate-spin" />
                    <Trash2 v-else class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
