<script setup lang="ts">
/**
 * Cart Page - Halaman Keranjang Belanja
 * Menampilkan daftar item di keranjang dengan iOS-like interactions,
 * swipe-to-delete, spring animations, dan checkout button dengan pulse effect
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
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
</script>

<template>
    <Head title="Keranjang Belanja - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-border/30">
                <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                    <!-- Logo & Brand -->
                    <Link
                        :href="home()"
                        v-motion
                        :initial="{ opacity: 0, x: -20 }"
                        :enter="{
                            opacity: 1,
                            x: 0,
                            transition: {
                                type: 'spring',
                                stiffness: 300,
                                damping: 25,
                            },
                        }"
                        class="flex items-center gap-2 sm:gap-3"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-sm sm:h-10 sm:w-10">
                            <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                        </div>
                        <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                    </Link>

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
                <Link
                    :href="home()"
                    v-motion
                    :initial="{ opacity: 0, x: -20 }"
                    :enter="{
                        opacity: 1,
                        x: 0,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                        },
                    }"
                    class="ios-button mb-4 inline-flex h-11 items-center gap-2 rounded-xl px-3 text-sm text-muted-foreground transition-all duration-150 hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
                    :class="{ 'scale-95 opacity-70': isBackPressed }"
                    @mousedown="isBackPressed = true"
                    @mouseup="isBackPressed = false"
                    @mouseleave="isBackPressed = false"
                    @touchstart.passive="isBackPressed = true"
                    @touchend="isBackPressed = false"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke Katalog
                </Link>

                <!-- Page Title dengan animation -->
                <div
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{
                        opacity: 1,
                        y: 0,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 50,
                        },
                    }"
                    class="mb-6 sm:mb-8"
                >
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Keranjang Belanja
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                        {{ isEmpty ? 'Keranjang Anda kosong' : `${cart.total_items} item dalam keranjang` }}
                    </p>
                </div>

                <!-- Empty State dengan animation -->
                <div
                    v-if="isEmpty"
                    v-motion
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :enter="{
                        opacity: 1,
                        scale: 1,
                        transition: {
                            type: 'spring',
                            stiffness: 300,
                            damping: 25,
                            delay: 100,
                        },
                    }"
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
                </div>

                <!-- Cart Content -->
                <div v-else class="grid gap-6 lg:grid-cols-3 lg:gap-8">
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
                    <div
                        v-motion
                        :initial="{ opacity: 0, x: 20 }"
                        :enter="{
                            opacity: 1,
                            x: 0,
                            transition: {
                                type: 'spring',
                                stiffness: 300,
                                damping: 25,
                                delay: 200,
                            },
                        }"
                        class="hidden lg:col-span-1 lg:block"
                    >
                        <div class="ios-card sticky top-24 rounded-2xl border border-border/50 p-6">
                            <h2 class="mb-4 text-lg font-semibold text-foreground">
                                Ringkasan Pesanan
                            </h2>

                            <!-- Summary Details -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Subtotal ({{ cart.total_items }} item)</span>
                                    <span
                                        :key="cart.subtotal"
                                        v-motion
                                        :initial="{ scale: 1.1, opacity: 0 }"
                                        :enter="{
                                            scale: 1,
                                            opacity: 1,
                                            transition: {
                                                type: 'spring',
                                                stiffness: 400,
                                                damping: 20,
                                            },
                                        }"
                                        class="font-medium text-foreground"
                                    >
                                        {{ formattedSubtotal }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Ongkos Kirim</span>
                                    <span class="font-medium text-foreground">Dihitung saat checkout</span>
                                </div>

                                <hr class="border-border" />

                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-foreground">Total</span>
                                    <span
                                        :key="cart.subtotal"
                                        v-motion
                                        :initial="{ scale: 1.1, opacity: 0 }"
                                        :enter="{
                                            scale: 1,
                                            opacity: 1,
                                            transition: {
                                                type: 'spring',
                                                stiffness: 400,
                                                damping: 20,
                                            },
                                        }"
                                        class="text-xl font-bold text-primary"
                                    >
                                        {{ formattedSubtotal }}
                                    </span>
                                </div>
                            </div>

                            <!-- Checkout Button dengan pulse animation -->
                            <Link :href="checkoutShow()" @click="handleCheckoutClick">
                                <Button
                                    size="lg"
                                    class="ios-button mt-6 h-13 w-full gap-2 rounded-2xl shadow-lg transition-transform duration-150"
                                    :class="{ 'scale-95': isCheckoutPressed }"
                                    @mousedown="isCheckoutPressed = true"
                                    @mouseup="isCheckoutPressed = false"
                                    @mouseleave="isCheckoutPressed = false"
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

            <!-- Mobile Sticky Checkout Footer dengan iOS Glass Effect -->
            <div
                v-if="!isEmpty"
                class="ios-glass fixed inset-x-0 bottom-16 z-40 border-t border-border/30 p-4 md:bottom-0 lg:hidden"
            >
                <div class="mx-auto max-w-7xl">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Total ({{ cart.total_items }} item)</span>
                        <span
                            :key="cart.subtotal"
                            v-motion
                            :initial="{ scale: 1.1, opacity: 0 }"
                            :enter="{
                                scale: 1,
                                opacity: 1,
                                transition: {
                                    type: 'spring',
                                    stiffness: 400,
                                    damping: 20,
                                },
                            }"
                            class="text-lg font-bold text-primary"
                        >
                            {{ formattedSubtotal }}
                        </span>
                    </div>
                    <Link :href="checkoutShow()" class="block" @click="handleCheckoutClick">
                        <Button
                            size="lg"
                            class="ios-button h-13 w-full gap-2 rounded-2xl text-base shadow-lg transition-transform duration-150"
                            :class="{ 'scale-95': isCheckoutPressed }"
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
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-16 hidden border-t border-border bg-muted/30 md:block">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-muted-foreground">
                        <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                    </div>
                </div>
            </footer>
            </PullToRefresh>
        </div>

        <!-- Mobile Bottom Navigation (Outside untuk fixed positioning) -->
        <UserBottomNav />
</template>
