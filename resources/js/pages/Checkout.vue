<script setup lang="ts">
/**
 * Checkout Page - Halaman Checkout
 * Menampilkan form data customer dengan iOS-like form animations
 * menggunakan motion-v, spring transitions, haptic feedback,
 * dan success celebration state
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Form } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { show as showCart } from '@/routes/cart'
import { store as checkoutStore } from '@/actions/App/Http/Controllers/CheckoutController'
import CartCounter from '@/components/store/CartCounter.vue'
import StoreHeader from '@/components/store/StoreHeader.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { useShakeAnimation, springPresets } from '@/composables/useMotionV'
import {
    ShoppingBag,
    ArrowLeft,
    Package,
    User,
    MapPin,
    Loader2,
    AlertCircle,
    CheckCircle,
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
 * Interface untuk customer data dari authenticated user
 */
interface CustomerData {
    name: string
    phone: string | null
    address: string | null
}

/**
 * Props yang diterima dari CheckoutController::show
 */
interface Props {
    cart: CartData
    customer: CustomerData | null
}

const props = defineProps<Props>()

/**
 * Page instance untuk mengakses shared props
 */
const page = usePage()

/**
 * Interface dan computed untuk store branding
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})

/**
 * Haptic feedback dan shake animation
 */
const haptic = useHapticFeedback()
const { shakeClass, shake } = useShakeAnimation()

/**
 * Press states
 */
const isBackPressed = ref(false)
const isSubmitPressed = ref(false)

/**
 * Focus states untuk form fields dengan iOS-like animation
 */
const focusedField = ref<string | null>(null)

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
 * Format harga item
 */
function formatPrice(price: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price)
}

/**
 * Handle form error dengan shake animation
 */
function handleFormError() {
    haptic.error()
    shake()
}

/**
 * Handle field focus
 */
function handleFieldFocus(fieldName: string) {
    focusedField.value = fieldName
    haptic.selection()
}

/**
 * Handle field blur
 */
function handleFieldBlur() {
    focusedField.value = null
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`Checkout - ${store.name}`">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <StoreHeader />

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
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
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
                        :href="showCart()"
                        class="ios-button mb-4 inline-flex h-11 items-center gap-2 rounded-xl px-3 text-sm text-muted-foreground hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
                @mousedown="isBackPressed = true"
                @mouseup="isBackPressed = false"
                @mouseleave="isBackPressed = false"
                @touchstart.passive="isBackPressed = true"
                @touchend="isBackPressed = false"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Keranjang
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
                    Checkout
                </h1>
                <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                    Lengkapi data pengiriman untuk melanjutkan pesanan
                </p>
            </Motion>

            <!-- Checkout Form dengan iOS styling -->
            <Form
                v-bind="checkoutStore.form()"
                #default="{ errors, processing, hasErrors }"
                class="grid gap-6 lg:grid-cols-3 lg:gap-8"
                @error="handleFormError"
            >
                <!-- Form Fields -->
                <div class="space-y-4 sm:space-y-6 lg:col-span-2">
                    <!-- Error Alert dengan shake animation -->
                    <AnimatePresence>
                        <Motion
                        v-if="hasErrors && errors.checkout"
                        :initial="{ opacity: 0, scale: 0.95 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :exit="{ opacity: 0, scale: 0.95 }"
                            :transition="bouncyTransition"
                        :class="shakeClass"
                        class="rounded-2xl border border-destructive/50 bg-destructive/10 p-4"
                    >
                        <div class="flex items-center gap-2 text-destructive">
                            <AlertCircle class="h-5 w-5 shrink-0" />
                            <span class="text-sm font-medium sm:text-base">{{ errors.checkout }}</span>
                        </div>
                        </Motion>
                    </AnimatePresence>

                    <!-- Data Penerima Section -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.1 }"
                        class="ios-card rounded-2xl border border-brand-blue-100 bg-card p-4 dark:border-brand-blue-800/30 sm:p-6"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <User class="h-5 w-5 text-brand-blue" />
                            Data Penerima
                        </h2>

                        <div class="space-y-4">
                            <!-- Nama Lengkap dengan iOS-like focus animation -->
                            <div class="space-y-2">
                                <Label for="customer_name" class="text-sm sm:text-base">Nama Lengkap *</Label>
                                <Motion
                                    :animate="{ scale: focusedField === 'customer_name' ? 1.02 : 1 }"
                                    :transition="snappyTransition"
                                >
                                    <Input
                                        id="customer_name"
                                        name="customer_name"
                                        type="text"
                                        placeholder="Masukkan nama lengkap"
                                        :default-value="customer?.name ?? ''"
                                        class="ios-input h-12 rounded-xl text-base sm:h-11 sm:text-sm"
                                        :aria-invalid="!!errors.customer_name"
                                        @focus="handleFieldFocus('customer_name')"
                                        @blur="handleFieldBlur"
                                    />
                                </Motion>
                                <p v-if="errors.customer_name" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_name }}
                                </p>
                            </div>

                            <!-- Nomor Telepon -->
                            <div class="space-y-2">
                                <Label for="customer_phone" class="text-sm sm:text-base">
                                    Nomor Telepon (WhatsApp) *
                                </Label>
                                <Motion
                                    :animate="{ scale: focusedField === 'customer_phone' ? 1.02 : 1 }"
                                    :transition="snappyTransition"
                                >
                                    <Input
                                        id="customer_phone"
                                        name="customer_phone"
                                        type="tel"
                                        inputmode="tel"
                                        placeholder="08xxxxxxxxxx"
                                        :default-value="customer?.phone ?? ''"
                                        class="ios-input h-12 rounded-xl text-base sm:h-11 sm:text-sm"
                                        :aria-invalid="!!errors.customer_phone"
                                        @focus="handleFieldFocus('customer_phone')"
                                        @blur="handleFieldBlur"
                                    />
                                </Motion>
                                <p v-if="errors.customer_phone" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_phone }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Pesanan akan dikonfirmasi via WhatsApp ke nomor ini
                                </p>
                            </div>
                        </div>
                    </Motion>

                    <!-- Alamat Pengiriman Section -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.15 }"
                        class="ios-card rounded-2xl border border-brand-blue-100 bg-card p-4 dark:border-brand-blue-800/30 sm:p-6"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <MapPin class="h-5 w-5 text-brand-blue" />
                            Alamat Pengiriman
                        </h2>

                        <div class="space-y-4">
                            <!-- Alamat Lengkap -->
                            <div class="space-y-2">
                                <Label for="customer_address" class="text-sm sm:text-base">
                                    Alamat Lengkap
                                    <span class="text-muted-foreground">(Opsional)</span>
                                </Label>
                                <Motion
                                    :animate="{ scale: focusedField === 'customer_address' ? 1.01 : 1 }"
                                    :transition="snappyTransition"
                                >
                                    <textarea
                                        id="customer_address"
                                        name="customer_address"
                                        rows="4"
                                        placeholder="Masukkan alamat lengkap dengan RT/RW, kelurahan, kecamatan, kota, dan kode pos"
                                        class="ios-input flex min-h-[120px] w-full rounded-xl border-0 bg-muted/50 px-4 py-3 text-base shadow-xs placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                        :aria-invalid="!!errors.customer_address"
                                        @focus="handleFieldFocus('customer_address')"
                                        @blur="handleFieldBlur"
                                    >{{ customer?.address ?? '' }}</textarea>
                                </Motion>
                                <p v-if="errors.customer_address" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_address }}
                                </p>
                            </div>

                            <!-- Catatan -->
                            <div class="space-y-2">
                                <Label for="notes" class="text-sm sm:text-base">
                                    Catatan (Opsional)
                                </Label>
                                <Motion
                                    :animate="{ scale: focusedField === 'notes' ? 1.01 : 1 }"
                                    :transition="snappyTransition"
                                >
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="3"
                                        placeholder="Catatan tambahan untuk pesanan (misal: warna, ukuran, patokan alamat, dll)"
                                        class="ios-input flex min-h-[100px] w-full rounded-xl border-0 bg-muted/50 px-4 py-3 text-base shadow-xs placeholder:text-muted-foreground focus:bg-background focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                        :aria-invalid="!!errors.notes"
                                        @focus="handleFieldFocus('notes')"
                                        @blur="handleFieldBlur"
                                    ></textarea>
                                </Motion>
                                <p v-if="errors.notes" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.notes }}
                                </p>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Order Summary -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springTransition, delay: 0.2 }"
                    class="lg:col-span-1"
                >
                    <div class="premium-card sticky top-24 rounded-2xl p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <Package class="h-5 w-5 text-brand-blue" />
                            Ringkasan Pesanan
                        </h2>

                        <!-- Items List dengan scroll -->
                        <div class="ios-scroll mb-4 max-h-64 space-y-3 overflow-y-auto">
                            <Motion
                                v-for="(item, index) in cart.items"
                                :key="item.id"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ ...springTransition, delay: 0.25 + index * 0.05 }"
                                class="flex items-center gap-3 rounded-xl bg-muted/30 p-3"
                            >
                                <!-- Product Image -->
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-muted">
                                    <img
                                        v-if="item.product.image"
                                        :src="`/storage/${item.product.image}`"
                                        :alt="item.product.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center">
                                        <Package class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-foreground">
                                        {{ item.product.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ item.quantity }} x {{ formatPrice(item.product.price) }}
                                    </p>
                                </div>

                                <!-- Subtotal -->
                                <p class="text-sm font-medium text-foreground">
                                    {{ formatPrice(item.subtotal) }}
                                </p>
                            </Motion>
                        </div>

                        <hr class="my-4 border-border" />

                        <!-- Summary Details -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal ({{ cart.total_items }} item)</span>
                                <span class="font-medium text-foreground">{{ formattedSubtotal }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Ongkos Kirim</span>
                                <span class="font-medium text-brand-gold">Gratis</span>
                            </div>

                            <hr class="border-border" />

                            <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 p-3 dark:from-brand-blue-900/30 dark:to-brand-gold-900/20">
                                <span class="font-semibold text-foreground">Total</span>
                                <span class="price-tag text-xl font-bold">{{ formattedSubtotal }}</span>
                            </div>
                        </div>

                        <!-- Submit Button dengan iOS-like animation -->
                        <Motion
                            :animate="{ scale: isSubmitPressed ? 0.95 : 1 }"
                            :transition="snappyTransition"
                        >
                        <Button
                            type="submit"
                            size="lg"
                                class="ios-button mt-6 h-14 w-full gap-2 rounded-2xl bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 shadow-lg shadow-brand-blue-500/25 hover:from-brand-blue-600 hover:to-brand-blue-700"
                            :disabled="processing"
                            @mousedown="isSubmitPressed = true"
                            @mouseup="isSubmitPressed = false"
                            @mouseleave="isSubmitPressed = false"
                            @touchstart.passive="isSubmitPressed = true"
                            @touchend="isSubmitPressed = false"
                        >
                            <Loader2 v-if="processing" class="h-5 w-5 animate-spin" />
                            <CheckCircle v-else class="h-5 w-5" />
                            <span>{{ processing ? 'Memproses...' : 'Pesan via WhatsApp' }}</span>
                        </Button>
                        </Motion>

                        <!-- Info -->
                        <p class="mt-4 text-center text-xs text-muted-foreground">
                            Dengan memesan, Anda akan diarahkan ke WhatsApp untuk konfirmasi pesanan
                        </p>
                    </div>
                </Motion>
            </Form>
        </main>

        <!-- Footer -->
        <footer class="mt-16 hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background md:block">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-2">
                    <p class="text-center text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} {{ store.name }}.
                    </p>
                    <p class="text-xs text-brand-gold">{{ store.tagline }}</p>
                </div>
            </div>
        </footer>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
