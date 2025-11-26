<script setup lang="ts">
/**
 * Cart Page - Halaman Keranjang Belanja
 * Menampilkan daftar item di keranjang dengan kontrol quantity,
 * total harga, dan tombol checkout
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { home } from '@/routes'
import { show as checkoutShow } from '@/actions/App/Http/Controllers/CheckoutController'
import CartItem from '@/components/store/CartItem.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import { Button } from '@/components/ui/button'
import {
    ShoppingBag,
    ArrowLeft,
    ShoppingCart,
    ArrowRight,
} from 'lucide-vue-next'

/**
 * Interface untuk cart item dari CartService
 */
interface CartItemData {
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

/**
 * Interface untuk cart data dari CartController
 */
interface CartData {
    id: number
    items: CartItemData[]
    total_items: number
    subtotal: number
    formatted_subtotal: string
}

/**
 * Props yang diterima dari CartController::show
 */
interface Props {
    cart: CartData
}

const props = defineProps<Props>()

/**
 * Computed untuk mengecek apakah cart kosong
 */
const isEmpty = computed(() => props.cart.items.length === 0)

/**
 * Computed untuk format subtotal
 */
const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.cart.subtotal)
})
</script>

<template>
    <Head title="Keranjang Belanja - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <ShoppingBag class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <span class="text-xl font-bold text-foreground">Simple Store</span>
                </Link>

                <!-- Cart Counter & Auth -->
                <nav class="flex items-center gap-3">
                    <CartCounter :count="cart.total_items" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <Link
                :href="home()"
                class="mb-6 inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Katalog
            </Link>

            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Keranjang Belanja
                </h1>
                <p class="mt-2 text-muted-foreground">
                    {{ isEmpty ? 'Keranjang Anda kosong' : `${cart.total_items} item dalam keranjang` }}
                </p>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-if="isEmpty"
                icon="🛒"
                title="Keranjang Kosong"
                description="Belum ada produk di keranjang. Yuk mulai belanja!"
            >
                <template #action>
                    <Link :href="home()">
                        <Button class="mt-4 gap-2">
                            <ShoppingCart class="h-4 w-4" />
                            Mulai Belanja
                        </Button>
                    </Link>
                </template>
            </EmptyState>

            <!-- Cart Content -->
            <div v-else class="grid gap-8 lg:grid-cols-3">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        <CartItem
                            v-for="item in cart.items"
                            :key="item.id"
                            :item="item"
                        />
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-xl border border-border bg-card p-6">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Ringkasan Pesanan
                        </h2>

                        <!-- Summary Details -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal ({{ cart.total_items }} item)</span>
                                <span class="font-medium text-foreground">{{ formattedSubtotal }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Ongkos Kirim</span>
                                <span class="font-medium text-foreground">Dihitung saat checkout</span>
                            </div>

                            <hr class="border-border" />

                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-foreground">Total</span>
                                <span class="text-xl font-bold text-primary">{{ formattedSubtotal }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <Link :href="checkoutShow()">
                        <Button
                            size="lg"
                            class="mt-6 w-full gap-2"
                        >
                            Lanjut ke Checkout
                                <ArrowRight class="h-4 w-4" />
                        </Button>
                        </Link>

                        <!-- Continue Shopping Link -->
                        <Link
                            :href="home()"
                            class="mt-4 block text-center text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            atau lanjut belanja
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-border bg-muted/30">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

