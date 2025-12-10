<script setup lang="ts">
/**
 * Checkout Page - Halaman Checkout
 * Menampilkan form data customer dengan iOS-like form animations,
 * spring transitions, haptic feedback, dan success celebration state
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { Form } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { show as showCart } from '@/routes/cart'
import { store as checkoutStore } from '@/actions/App/Http/Controllers/CheckoutController'
import CartCounter from '@/components/store/CartCounter.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { useShakeAnimation } from '@/composables/useSpringAnimation'
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
 * Props yang diterima dari CheckoutController::show
 */
interface Props {
    cart: CartData
}

const props = defineProps<Props>()

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
</script>

<template>
    <Head title="Checkout - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-border/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link
                    href="/"
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
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <!-- Back Button dengan iOS press feedback -->
            <Link
                :href="showCart()"
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
                Kembali ke Keranjang
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
                    Checkout
                </h1>
                <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                    Lengkapi data pengiriman untuk melanjutkan pesanan
                </p>
            </div>

            <!-- Checkout Form dengan iOS styling -->
            <Form
                v-bind="checkoutStore.form()"
                #default="{ errors, processing, hasErrors }"
                class="grid gap-6 lg:grid-cols-3 lg:gap-8"
                @submit="hasErrors && handleFormError()"
            >
                <!-- Form Fields -->
                <div class="space-y-4 sm:space-y-6 lg:col-span-2">
                    <!-- Error Alert dengan shake animation -->
                    <div
                        v-if="hasErrors && errors.checkout"
                        v-motion
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :enter="{
                            opacity: 1,
                            scale: 1,
                            transition: {
                                type: 'spring',
                                stiffness: 400,
                                damping: 20,
                            },
                        }"
                        :class="shakeClass"
                        class="rounded-2xl border border-destructive/50 bg-destructive/10 p-4"
                    >
                        <div class="flex items-center gap-2 text-destructive">
                            <AlertCircle class="h-5 w-5 shrink-0" />
                            <span class="text-sm font-medium sm:text-base">{{ errors.checkout }}</span>
                        </div>
                    </div>

                    <!-- Data Penerima Section -->
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
                                delay: 100,
                            },
                        }"
                        class="ios-card rounded-2xl border border-border/50 p-4 sm:p-6"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <User class="h-5 w-5 text-primary" />
                            Data Penerima
                        </h2>

                        <div class="space-y-4">
                            <!-- Nama Lengkap dengan iOS-like focus animation -->
                            <div class="space-y-2">
                                <Label for="customer_name" class="text-sm sm:text-base">Nama Lengkap *</Label>
                                <div
                                    class="transition-transform duration-200"
                                    :class="{ 'scale-[1.02]': focusedField === 'customer_name' }"
                                >
                                    <Input
                                        id="customer_name"
                                        name="customer_name"
                                        type="text"
                                        placeholder="Masukkan nama lengkap"
                                        class="ios-input h-12 rounded-xl text-base sm:h-11 sm:text-sm"
                                        :aria-invalid="!!errors.customer_name"
                                        @focus="handleFieldFocus('customer_name')"
                                        @blur="handleFieldBlur"
                                    />
                                </div>
                                <p v-if="errors.customer_name" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_name }}
                                </p>
                            </div>

                            <!-- Nomor Telepon -->
                            <div class="space-y-2">
                                <Label for="customer_phone" class="text-sm sm:text-base">
                                    Nomor Telepon (WhatsApp) *
                                </Label>
                                <div
                                    class="transition-transform duration-200"
                                    :class="{ 'scale-[1.02]': focusedField === 'customer_phone' }"
                                >
                                    <Input
                                        id="customer_phone"
                                        name="customer_phone"
                                        type="tel"
                                        inputmode="tel"
                                        placeholder="08xxxxxxxxxx"
                                        class="ios-input h-12 rounded-xl text-base sm:h-11 sm:text-sm"
                                        :aria-invalid="!!errors.customer_phone"
                                        @focus="handleFieldFocus('customer_phone')"
                                        @blur="handleFieldBlur"
                                    />
                                </div>
                                <p v-if="errors.customer_phone" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_phone }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Pesanan akan dikonfirmasi via WhatsApp ke nomor ini
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman Section -->
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
                                delay: 150,
                            },
                        }"
                        class="ios-card rounded-2xl border border-border/50 p-4 sm:p-6"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <MapPin class="h-5 w-5 text-primary" />
                            Alamat Pengiriman
                        </h2>

                        <div class="space-y-4">
                            <!-- Alamat Lengkap -->
                            <div class="space-y-2">
                                <Label for="customer_address" class="text-sm sm:text-base">Alamat Lengkap *</Label>
                                <div
                                    class="transition-transform duration-200"
                                    :class="{ 'scale-[1.01]': focusedField === 'customer_address' }"
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
                                    ></textarea>
                                </div>
                                <p v-if="errors.customer_address" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.customer_address }}
                                </p>
                            </div>

                            <!-- Catatan -->
                            <div class="space-y-2">
                                <Label for="notes" class="text-sm sm:text-base">
                                    Catatan (Opsional)
                                </Label>
                                <div
                                    class="transition-transform duration-200"
                                    :class="{ 'scale-[1.01]': focusedField === 'notes' }"
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
                                </div>
                                <p v-if="errors.notes" class="animate-ios-shake text-sm text-destructive">
                                    {{ errors.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
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
                    class="lg:col-span-1"
                >
                    <div class="ios-card sticky top-24 rounded-2xl border border-border/50 p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <Package class="h-5 w-5 text-primary" />
                            Ringkasan Pesanan
                        </h2>

                        <!-- Items List dengan scroll -->
                        <div class="ios-scroll mb-4 max-h-64 space-y-3 overflow-y-auto">
                            <div
                                v-for="(item, index) in cart.items"
                                :key="item.id"
                                v-motion
                                :initial="{ opacity: 0, y: 10 }"
                                :enter="{
                                    opacity: 1,
                                    y: 0,
                                    transition: {
                                        type: 'spring',
                                        stiffness: 300,
                                        damping: 25,
                                        delay: 250 + index * 50,
                                    },
                                }"
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
                            </div>
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
                                <span class="font-medium text-green-600">Gratis</span>
                            </div>

                            <hr class="border-border" />

                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-foreground">Total</span>
                                <span class="text-xl font-bold text-primary">{{ formattedSubtotal }}</span>
                            </div>
                        </div>

                        <!-- Submit Button dengan iOS-like animation -->
                        <Button
                            type="submit"
                            size="lg"
                            class="ios-button mt-6 h-14 w-full gap-2 rounded-2xl shadow-lg transition-transform duration-150"
                            :class="{ 'scale-95': isSubmitPressed }"
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

                        <!-- Info -->
                        <p class="mt-4 text-center text-xs text-muted-foreground">
                            Dengan memesan, Anda akan diarahkan ke WhatsApp untuk konfirmasi pesanan
                        </p>
                    </div>
                </div>
            </Form>
        </main>

        <!-- Footer -->
        <footer class="mt-16 hidden border-t border-border bg-muted/30 md:block">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
