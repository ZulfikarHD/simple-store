<script setup lang="ts">
/**
 * Cart Page - Halaman Keranjang Belanja
 * Menampilkan daftar item di keranjang dengan kontrol quantity,
 * total harga, dan tombol checkout dengan mobile bottom navigation
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
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
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
        <!-- Header Navigation - Mobile Optimized -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>

                <!-- Cart Counter & Auth - Hidden auth on mobile -->
                <nav class="flex items-center gap-2 sm:gap-3">
                    <CartCounter :count="cart.total_items" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="hidden rounded-lg px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent sm:inline-flex"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content - Mobile Optimized dengan padding untuk sticky footer -->
        <main class="mx-auto max-w-7xl px-4 py-6 pb-32 sm:px-6 sm:py-8 sm:pb-8 lg:px-8">
            <!-- Back Button - Touch-friendly -->
            <Link
                :href="home()"
                class="mb-4 inline-flex h-11 items-center gap-2 rounded-lg px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Katalog
            </Link>

            <!-- Page Title - Responsive -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                    Keranjang Belanja
                </h1>
                <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
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

            <!-- Cart Content - Mobile Optimized -->
            <div v-else class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="space-y-3 sm:space-y-4">
                        <CartItem
                            v-for="item in cart.items"
                            :key="item.id"
                            :item="item"
                        />
                    </div>
                </div>

                <!-- Order Summary - Desktop Only -->
                <div class="hidden lg:col-span-1 lg:block">
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

        <!-- Mobile Sticky Checkout Footer - positioned above bottom nav -->
        <div
            v-if="!isEmpty"
            class="fixed inset-x-0 bottom-16 z-40 border-t border-border bg-background/95 p-4 backdrop-blur supports-[backdrop-filter]:bg-background/80 md:bottom-0 lg:hidden"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Total ({{ cart.total_items }} item)</span>
                    <span class="text-lg font-bold text-primary">{{ formattedSubtotal }}</span>
                </div>
                <Link :href="checkoutShow()" class="block">
                    <Button
                        size="lg"
                        class="h-12 w-full gap-2 text-base"
                    >
                        Lanjut ke Checkout
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Footer - Hidden on mobile -->
        <footer class="mt-16 hidden border-t border-border bg-muted/30 md:block">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>

