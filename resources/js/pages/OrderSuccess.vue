<script setup lang="ts">
/**
 * Order Success Page - Halaman Konfirmasi Pesanan
 * Menampilkan detail pesanan yang berhasil dibuat dengan iOS-like animations
 * menggunakan motion-v, celebration effects, dan bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, onMounted, ref } from 'vue'
import { home } from '@/routes'
import CartCounter from '@/components/store/CartCounter.vue'
import StoreHeader from '@/components/store/StoreHeader.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Button } from '@/components/ui/button'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ShoppingBag,
    CheckCircle2,
    Package,
    User,
    Phone,
    MapPin,
    FileText,
    ExternalLink,
    Home,
    Copy,
    Check,
} from 'lucide-vue-next'

/**
 * Interface untuk order item
 */
interface OrderItemData {
    id: number
    product_name: string
    product_price: number
    quantity: number
    subtotal: number
    formatted_subtotal: string
}

/**
 * Interface untuk order data dari OrderService
 */
interface OrderData {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address: string
    notes: string | null
    items: OrderItemData[]
    subtotal: number
    delivery_fee: number
    total: number
    formatted_total: string
    status: string
    created_at: string
}

/**
 * Props yang diterima dari CheckoutController::success
 */
interface Props {
    order: OrderData
    whatsappUrl: string
}

const props = defineProps<Props>()

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
 * Haptic feedback
 */
const haptic = useHapticFeedback()

/**
 * State untuk copy feedback
 */
const copied = ref(false)

/**
 * Press states untuk iOS-like feedback
 */
const isWhatsAppPressed = ref(false)
const isHomePressed = ref(false)
const isCopyPressed = ref(false)

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
 * Copy order number ke clipboard
 */
async function copyOrderNumber() {
    try {
        haptic.light()
        await navigator.clipboard.writeText(props.order.order_number)
        copied.value = true
        haptic.success()
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
    }
}

/**
 * Redirect ke WhatsApp
 */
function openWhatsApp() {
    haptic.medium()
    window.open(props.whatsappUrl, '_blank')
}

/**
 * Celebration effect on mount
 */
onMounted(() => {
    haptic.success()
})

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`Pesanan Berhasil - ${store.name}`">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                >
                    <StoreHeader />
                </Motion>

                <!-- Cart Counter & Auth -->
                <nav class="flex items-center gap-3">
                    <CartCounter :count="0" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="ios-button hidden rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="ios-button hidden rounded-xl px-4 py-2 text-sm font-medium text-foreground sm:inline-flex"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="ios-button hidden rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground sm:inline-flex"
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
        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Success Header dengan celebration animation -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="springTransition"
                class="mb-8 text-center"
            >
                <Motion
                    :initial="{ scale: 0, opacity: 0 }"
                    :animate="{ scale: 1, opacity: 1 }"
                    :transition="{ ...bouncyTransition, delay: 0.2 }"
                    class="mb-4 inline-flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-green-100 to-brand-gold-100 shadow-lg dark:from-green-900/30 dark:to-brand-gold-900/30"
                >
                    <CheckCircle2 class="h-12 w-12 text-green-600 dark:text-green-400" />
                </Motion>
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.3 }"
                >
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Pesanan Berhasil Dibuat! 🎉
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Terima kasih telah berbelanja. Silakan lanjutkan ke WhatsApp untuk konfirmasi pesanan.
                </p>
                </Motion>
            </Motion>

            <!-- Order Number dengan pop animation -->
            <Motion
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ ...springTransition, delay: 0.4 }"
                class="success-card mb-8 flex flex-col items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-brand-blue-50 via-white to-brand-gold-50 p-6 dark:from-brand-blue-900/30 dark:via-background dark:to-brand-gold-900/20"
            >
                <p class="text-sm font-medium text-muted-foreground">Nomor Pesanan</p>
                <div class="flex items-center gap-3">
                    <Motion
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ ...springTransition, delay: 0.5 }"
                        class="price-tag text-2xl font-bold"
                    >
                        {{ order.order_number }}
                    </Motion>
                    <Motion
                        :animate="{ scale: isCopyPressed ? 0.9 : 1 }"
                        :transition="snappyTransition"
                    >
                    <Button
                        variant="outline"
                        size="icon"
                            class="h-8 w-8 rounded-lg"
                        @click="copyOrderNumber"
                            @mousedown="isCopyPressed = true"
                            @mouseup="isCopyPressed = false"
                            @mouseleave="isCopyPressed = false"
                            @touchstart.passive="isCopyPressed = true"
                            @touchend="isCopyPressed = false"
                    >
                            <AnimatePresence mode="wait">
                                <Motion
                                    v-if="copied"
                                    :key="'check'"
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :exit="{ scale: 0 }"
                                    :transition="bouncyTransition"
                                >
                                    <Check class="h-4 w-4 text-green-600" />
                                </Motion>
                        <Copy v-else class="h-4 w-4" />
                            </AnimatePresence>
                    </Button>
                    </Motion>
                </div>
                <p class="text-xs text-muted-foreground">{{ order.created_at }}</p>
            </Motion>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Customer Info dengan slide animation -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springTransition, delay: 0.5 }"
                    class="ios-card rounded-2xl border border-brand-blue-100 bg-card p-6 dark:border-brand-blue-800/30"
                >
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                        <User class="h-5 w-5 text-brand-blue" />
                        Data Penerima
                    </h2>

                    <div class="space-y-4">
                        <Motion
                            :initial="{ opacity: 0, x: -10 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ ...springTransition, delay: 0.55 }"
                            class="flex items-start gap-3"
                        >
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Nama</p>
                                <p class="font-medium text-foreground">{{ order.customer_name }}</p>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, x: -10 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ ...springTransition, delay: 0.6 }"
                            class="flex items-start gap-3"
                        >
                            <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Telepon</p>
                                <p class="font-medium text-foreground">{{ order.customer_phone }}</p>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, x: -10 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ ...springTransition, delay: 0.65 }"
                            class="flex items-start gap-3"
                        >
                            <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Alamat</p>
                                <p class="font-medium text-foreground">{{ order.customer_address }}</p>
                            </div>
                        </Motion>

                        <Motion
                            v-if="order.notes"
                            :initial="{ opacity: 0, x: -10 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ ...springTransition, delay: 0.7 }"
                            class="flex items-start gap-3"
                        >
                            <FileText class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Catatan</p>
                                <p class="font-medium text-foreground">{{ order.notes }}</p>
                            </div>
                        </Motion>
                    </div>
                </Motion>

                <!-- Order Summary dengan slide animation -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springTransition, delay: 0.5 }"
                    class="ios-card rounded-2xl border border-brand-blue-100 bg-card p-6 dark:border-brand-blue-800/30"
                >
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                        <Package class="h-5 w-5 text-brand-blue" />
                        Detail Pesanan
                    </h2>

                    <!-- Items List -->
                    <div class="mb-4 space-y-3">
                        <Motion
                            v-for="(item, index) in order.items"
                            :key="item.id"
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.55 + index * 0.05 }"
                            class="flex items-center justify-between rounded-xl bg-muted/50 p-3"
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    {{ item.product_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ item.quantity }} x {{ formatPrice(item.product_price) }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-foreground">
                                {{ item.formatted_subtotal }}
                            </p>
                        </Motion>
                    </div>

                    <hr class="my-4 border-border" />

                    <!-- Totals -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-medium text-foreground">{{ formatPrice(order.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Ongkos Kirim</span>
                            <span class="font-medium text-brand-gold">
                                {{ order.delivery_fee > 0 ? formatPrice(order.delivery_fee) : 'Gratis' }}
                            </span>
                        </div>
                        <hr class="border-border" />
                        <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 p-3 dark:from-brand-blue-900/30 dark:to-brand-gold-900/20">
                            <span class="font-semibold text-foreground">Total</span>
                            <Motion
                                :initial="{ scale: 1.1, opacity: 0 }"
                                :animate="{ scale: 1, opacity: 1 }"
                                :transition="bouncyTransition"
                                class="price-tag text-xl font-bold"
                            >
                                {{ order.formatted_total }}
                            </Motion>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Action Buttons dengan stagger animation -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springTransition, delay: 0.7 }"
                class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center"
            >
                <Motion
                    :animate="{ scale: isWhatsAppPressed ? 0.95 : 1 }"
                    :transition="snappyTransition"
                >
                <Button
                    size="lg"
                        class="ios-button gap-2 rounded-xl bg-green-600 shadow-lg shadow-green-600/25 hover:bg-green-700"
                    @click="openWhatsApp"
                        @mousedown="isWhatsAppPressed = true"
                        @mouseup="isWhatsAppPressed = false"
                        @mouseleave="isWhatsAppPressed = false"
                        @touchstart.passive="isWhatsAppPressed = true"
                        @touchend="isWhatsAppPressed = false"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Konfirmasi via WhatsApp
                    <ExternalLink class="h-4 w-4" />
                </Button>
                </Motion>

                <Motion
                    :animate="{ scale: isHomePressed ? 0.95 : 1 }"
                    :transition="snappyTransition"
                >
                <Link :href="home()">
                    <Button
                        variant="outline"
                        size="lg"
                            class="ios-button w-full gap-2 rounded-xl border-brand-blue-200 text-brand-blue hover:bg-brand-blue-50 dark:border-brand-blue-700 dark:hover:bg-brand-blue-900/30 sm:w-auto"
                            @mousedown="isHomePressed = true"
                            @mouseup="isHomePressed = false"
                            @mouseleave="isHomePressed = false"
                            @touchstart.passive="isHomePressed = true"
                            @touchend="isHomePressed = false"
                    >
                        <Home class="h-4 w-4" />
                        Kembali ke Beranda
                    </Button>
                </Link>
                </Motion>
            </Motion>

            <!-- Info Note dengan fade animation -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springTransition, delay: 0.8 }"
                class="mt-8 rounded-2xl border border-brand-blue-200 bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 p-4 dark:border-brand-blue-800/30 dark:from-brand-blue-900/20 dark:to-brand-gold-900/20"
            >
                <p class="text-center text-sm text-brand-blue-700 dark:text-brand-blue-200">
                    💡 <strong>Tips:</strong> Simpan nomor pesanan Anda untuk referensi.
                    Kami akan menghubungi Anda via WhatsApp untuk konfirmasi dan pembayaran.
                </p>
            </Motion>
        </main>

        <!-- Footer - Hidden on mobile -->
        <footer class="mt-16 hidden border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background md:block">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-2">
                    <p class="text-center text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} {{ store.name }}. Created By Zulfikar Hidayatullah.
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
