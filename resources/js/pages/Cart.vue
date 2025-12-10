<script setup lang="ts">
/**
 * Cart Page - Halaman Keranjang Belanja
 * Menampilkan daftar item di keranjang dengan iOS-like interactions
 * menggunakan motion-v, swipe-to-delete, spring animations,
 * dan checkout button dengan pulse effect
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import { show as checkoutShow } from '@/actions/App/Http/Controllers/CheckoutController'
import CartItem from '@/components/store/CartItem.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { Button } from '@/components/ui/button'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
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
 * Haptic feedback
 */
const haptic = useHapticFeedback()

/**
 * Press states untuk iOS-like feedback
 */
const isBackPressed = ref(false)
const isCheckoutPressed = ref(false)

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

/**
 * Handle checkout button click
 */
function handleCheckoutClick() {
    haptic.medium()
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head title="Keranjang Belanja - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
                <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                    <!-- Logo & Brand -->
                <Motion
                    tag="a"
                        :href="home()"
                        :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                        class="flex items-center gap-2 sm:gap-3"
                    >
                        <div class="brand-logo h-9 w-9 sm:h-10 sm:w-10">
                            <ShoppingBag class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                            <span class="hidden text-[10px] font-medium text-brand-gold sm:block">Premium Quality Products</span>
                        </div>
                </Motion>

                    <!-- Cart Counter & Auth -->
                    <nav class="flex items-center gap-2 sm:gap-3">
                        <CartCounter :count="cart.total_items" />

                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard"
                            class="ios-button hidden rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm sm:inline-flex"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                href="/login"
                                class="ios-button hidden rounded-xl px-4 py-2.5 text-sm font-medium text-foreground sm:inline-flex"
                            >
                                Masuk
                            </Link>
                            <Link
                                href="/register"
                                class="ios-button hidden rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm sm:inline-flex"
                            >
                                Daftar
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <!-- Spacer untuk fixed header -->
            <div class="h-14 sm:h-16" />

            <!-- Main Content -->
            <PullToRefresh>
            <main class="mx-auto max-w-7xl px-4 py-6 pb-40 sm:px-6 sm:py-8 sm:pb-8 lg:px-8">
                <!-- Back Button dengan iOS press feedback -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                >
                    <Motion
                        :animate="{ scale: isBackPressed ? 0.95 : 1, opacity: isBackPressed ? 0.7 : 1 }"
                        :transition="snappyTransition"
                    >
                        <Link
                            :href="home()"
                            class="ios-button mb-4 inline-flex h-11 items-center gap-2 rounded-xl px-3 text-sm text-muted-foreground hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
                    @mousedown="isBackPressed = true"
                    @mouseup="isBackPressed = false"
                    @mouseleave="isBackPressed = false"
                    @touchstart.passive="isBackPressed = true"
                    @touchend="isBackPressed = false"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke Katalog
                </Link>
                    </Motion>
                </Motion>

                <!-- Page Title dengan animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.05 }"
                    class="mb-6 sm:mb-8"
                >
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Keranjang Belanja
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                        {{ isEmpty ? 'Keranjang Anda kosong' : `${cart.total_items} item dalam keranjang` }}
                    </p>
                </Motion>

                <!-- Empty State dengan animation -->
                <AnimatePresence>
                    <Motion
                    v-if="isEmpty"
                    :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.95 }"
                        :transition="{ ...springTransition, delay: 0.1 }"
                >
                    <EmptyState
                        icon="🛒"
                        title="Keranjang Kosong"
                        description="Belum ada produk di keranjang. Yuk mulai belanja!"
                    >
                        <template #action>
                            <Link :href="home()">
                                <Button class="ios-button mt-4 gap-2 rounded-xl">
                                    <ShoppingCart class="h-4 w-4" />
                                    Mulai Belanja
                                </Button>
                            </Link>
                        </template>
                    </EmptyState>
                    </Motion>
                </AnimatePresence>

                <!-- Cart Content -->
                <div v-if="!isEmpty" class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                    <!-- Cart Items dengan staggered animations -->
                    <div class="lg:col-span-2">
                        <div class="space-y-3 sm:space-y-4">
                            <CartItem
                                v-for="(item, index) in cart.items"
                                :key="item.id"
                                :item="item"
                                :index="index"
                            />
                        </div>
                    </div>

                    <!-- Order Summary - Desktop Only dengan slide animation -->
                    <Motion
                        :initial="{ opacity: 0, x: 20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ ...springTransition, delay: 0.2 }"
                        class="hidden lg:col-span-1 lg:block"
                    >
                        <div class="premium-card sticky top-24 rounded-2xl p-6">
                            <h2 class="mb-4 text-lg font-semibold text-foreground">
                                Ringkasan Pesanan
                            </h2>

                            <!-- Summary Details -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Subtotal ({{ cart.total_items }} item)</span>
                                    <Motion
                                        :key="cart.subtotal"
                                        :initial="{ scale: 1.1, opacity: 0 }"
                                        :animate="{ scale: 1, opacity: 1 }"
                                        :transition="bouncyTransition"
                                        class="font-medium text-foreground"
                                    >
                                        {{ formattedSubtotal }}
                                    </Motion>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Ongkos Kirim</span>
                                    <span class="font-medium text-brand-gold">Dihitung saat checkout</span>
                                </div>

                                <hr class="border-border" />

                                <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 p-3 dark:from-brand-blue-900/30 dark:to-brand-gold-900/20">
                                    <span class="font-semibold text-foreground">Total</span>
                                    <Motion
                                        :key="cart.subtotal"
                                        :initial="{ scale: 1.1, opacity: 0 }"
                                        :animate="{ scale: 1, opacity: 1 }"
                                        :transition="bouncyTransition"
                                        class="price-tag text-xl font-bold"
                                    >
                                        {{ formattedSubtotal }}
                                    </Motion>
                                </div>
                            </div>

                            <!-- Checkout Button dengan pulse animation -->
                            <Motion
                                :animate="{ scale: isCheckoutPressed ? 0.95 : 1 }"
                                :transition="snappyTransition"
                            >
                            <Link :href="checkoutShow()" @click="handleCheckoutClick">
                                <Button
                                    size="lg"
                                        class="ios-button mt-6 h-13 w-full gap-2 rounded-2xl bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 shadow-lg shadow-brand-blue-500/25 hover:from-brand-blue-600 hover:to-brand-blue-700"
                                    @mousedown="isCheckoutPressed = true"
                                    @mouseup="isCheckoutPressed = false"
                                    @mouseleave="isCheckoutPressed = false"
                                >
                                    Lanjut ke Checkout
                                    <ArrowRight class="h-4 w-4" />
                                </Button>
                            </Link>
                            </Motion>

                            <!-- Continue Shopping Link -->
                            <Link
                                :href="home()"
                                class="mt-4 block text-center text-sm text-muted-foreground transition-colors hover:text-brand-blue"
                            >
                                atau lanjut belanja
                            </Link>
                        </div>
                    </Motion>
                </div>
            </main>

            <!-- Mobile Sticky Checkout Footer dengan iOS Glass Effect -->
            <AnimatePresence>
                <Motion
                v-if="!isEmpty"
                    :initial="{ y: 100, opacity: 0 }"
                    :animate="{ y: 0, opacity: 1 }"
                    :exit="{ y: 100, opacity: 0 }"
                    :transition="springTransition"
                class="ios-glass fixed inset-x-0 bottom-16 z-40 border-t border-brand-blue-200/30 p-4 dark:border-brand-blue-800/30 md:bottom-0 lg:hidden"
            >
                <div class="mx-auto max-w-7xl">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Total ({{ cart.total_items }} item)</span>
                            <Motion
                            :key="cart.subtotal"
                            :initial="{ scale: 1.1, opacity: 0 }"
                                :animate="{ scale: 1, opacity: 1 }"
                                :transition="bouncyTransition"
                            class="price-tag text-lg font-bold"
                        >
                            {{ formattedSubtotal }}
                            </Motion>
                    </div>
                        <Motion
                            :animate="{ scale: isCheckoutPressed ? 0.95 : 1 }"
                            :transition="snappyTransition"
                        >
                    <Link :href="checkoutShow()" class="block" @click="handleCheckoutClick">
                        <Button
                            size="lg"
                                    class="ios-button h-13 w-full gap-2 rounded-2xl bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 text-base shadow-lg shadow-brand-blue-500/25 hover:from-brand-blue-600 hover:to-brand-blue-700"
                            @mousedown="isCheckoutPressed = true"
                            @mouseup="isCheckoutPressed = false"
                            @mouseleave="isCheckoutPressed = false"
                            @touchstart.passive="isCheckoutPressed = true"
                            @touchend="isCheckoutPressed = false"
                        >
                            Lanjut ke Checkout
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                    </Link>
                        </Motion>
                </div>
                </Motion>
            </AnimatePresence>

            <!-- Footer -->
            <footer class="mt-16 hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background md:block">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-center text-sm text-muted-foreground">
                            &copy; {{ new Date().getFullYear() }} Simple Store. Dibuat dengan ❤️ oleh Zulfikar Hidayatullah.
                        </p>
                        <p class="text-xs text-brand-gold">Premium Quality Products</p>
                    </div>
                </div>
            </footer>
            </PullToRefresh>
        </div>

        <!-- Mobile Bottom Navigation (Outside untuk fixed positioning) -->
        <UserBottomNav />
</template>
