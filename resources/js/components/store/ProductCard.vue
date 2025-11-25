<script setup lang="ts">
/**
 * ProductCard Component
 * Menampilkan informasi produk dalam format card dengan Airbnb-style design
 * mencakup gambar, nama, kategori, harga, dan status ketersediaan
 * dengan navigasi ke halaman detail produk
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Plus, ShoppingCart } from 'lucide-vue-next'
import { show } from '@/actions/App/Http/Controllers/ProductController'

/**
 * Props definition untuk ProductCard
 * dengan struktur data produk yang lengkap
 */
interface Props {
    product: {
        id: number
        name: string
        slug: string
        description?: string | null
        price: number
        image?: string | null
        category?: {
            id: number
            name: string
        }
        is_available: boolean
    }
    /** Mode tampilan: grid atau list */
    mode?: 'grid' | 'list'
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'grid',
})

/**
 * Generate URL detail produk menggunakan Wayfinder
 * dengan slug sebagai parameter untuk SEO-friendly URL
 */
const detailUrl = computed(() => {
    return show.url(props.product.slug)
})

/**
 * Emit events untuk interaksi user
 * - addToCart: ketika user menambahkan produk ke keranjang
 */
const emit = defineEmits<{
    addToCart: [productId: number]
}>()

/**
 * Format harga ke format Rupiah Indonesia
 * menggunakan Intl.NumberFormat untuk konsistensi
 */
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(props.product.price)
})

/**
 * Handle click pada tombol add to cart
 * dengan validasi ketersediaan produk
 */
function handleAddToCart() {
    if (!props.product.is_available) return
    emit('addToCart', props.product.id)
}

/**
 * Generate URL gambar produk
 * dengan fallback ke placeholder jika tidak ada
 */
const imageUrl = computed(() => {
    if (props.product.image) {
        return `/storage/${props.product.image}`
    }
    return null
})
</script>

<template>
    <article
        class="product-card group cursor-pointer"
        :class="{
            'flex flex-col': mode === 'grid',
            'flex flex-row gap-4': mode === 'list',
        }"
    >
        <!-- Product Image Container dengan Link ke Detail -->
        <Link
            :href="detailUrl"
            class="relative overflow-hidden"
            :class="{
                'aspect-square': mode === 'grid',
                'w-24 h-24 shrink-0 rounded-lg': mode === 'list',
            }"
        >
            <!-- Image dengan hover zoom effect -->
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <!-- Placeholder ketika tidak ada gambar -->
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-muted"
            >
                <ShoppingCart class="h-8 w-8 text-muted-foreground" />
            </div>

            <!-- Availability Badge -->
            <Badge
                v-if="!product.is_available"
                variant="destructive"
                class="absolute left-3 top-3"
            >
                Habis
            </Badge>
        </Link>

        <!-- Product Info -->
        <div
            class="flex flex-1 flex-col"
            :class="{
                'p-4': mode === 'grid',
                'py-1': mode === 'list',
            }"
        >
            <!-- Category Label -->
            <p
                v-if="product.category"
                class="mb-1 text-xs font-medium uppercase tracking-wider text-muted-foreground"
            >
                {{ product.category.name }}
            </p>

            <!-- Product Name -->
            <h3
                class="font-semibold text-foreground"
                :class="{
                    'mb-2 line-clamp-2 text-base': mode === 'grid',
                    'mb-1 line-clamp-1 text-sm': mode === 'list',
                }"
            >
                {{ product.name }}
            </h3>

            <!-- Description (grid mode only) -->
            <p
                v-if="product.description && mode === 'grid'"
                class="mb-3 line-clamp-2 text-sm text-muted-foreground"
            >
                {{ product.description }}
            </p>

            <!-- Price & Action -->
            <div class="mt-auto flex items-center justify-between gap-2">
                <p class="text-lg font-bold text-primary">
                    {{ formattedPrice }}
                </p>

                <Button
                    v-if="mode === 'grid'"
                    size="icon"
                    variant="secondary"
                    :disabled="!product.is_available"
                    class="h-9 w-9 rounded-full bg-primary/10 text-primary hover:bg-primary/20 disabled:opacity-50"
                    @click.prevent="handleAddToCart"
                >
                    <Plus class="h-5 w-5" />
                </Button>
            </div>
        </div>
    </article>
</template>

