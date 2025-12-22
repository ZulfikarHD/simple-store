<script setup lang="ts">
/**
 * Public Order View Page - Halaman Verifikasi dan Detail Pesanan
 * Menampilkan form verifikasi nomor telepon untuk akses order via ULID
 * dengan security protection menggunakan phone verification
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import StoreHeader from '@/components/store/StoreHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ShieldCheck,
    Phone,
    Lock,
    AlertCircle,
    Package,
    User,
    MapPin,
    FileText,
    CheckCircle2,
    XCircle,
    Home,
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
    notes: string | null
}

/**
 * Interface untuk order data
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
    status: string
    status_label: string
    timestamps: {
        created_at: string
        created_at_human: string
    }
}

/**
 * Props yang diterima dari PublicOrderController
 */
interface Props {
    order: OrderData | null
    ulid: string
    expired?: boolean
    verified?: boolean
    orderNumber?: string
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
 * Form untuk verifikasi nomor telepon
 */
const form = useForm({
    customer_phone: '',
})

/**
 * Submit verifikasi phone
 */
function submitVerification() {
    haptic.light()
    form.post(`/orders/${props.ulid}/verify`, {
        preserveScroll: true,
        onSuccess: () => {
            haptic.success()
        },
        onError: () => {
            haptic.error()
        },
    })
}

/**
 * Press states untuk iOS-like feedback
 */
const isSubmitPressed = ref(false)
const isHomePressed = ref(false)

/**
 * Format harga
 */
function formatPrice(price: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price)
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`Verifikasi Pesanan - ${store.name}`">
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

                <!-- Home Button -->
                <Motion
                    :animate="{ scale: isHomePressed ? 0.95 : 1 }"
                    :transition="snappyTransition"
                >
                    <a :href="home()">
                        <Button
                            variant="outline"
                            size="sm"
                            class="ios-button rounded-xl"
                            @mousedown="isHomePressed = true"
                            @mouseup="isHomePressed = false"
                            @mouseleave="isHomePressed = false"
                            @touchstart.passive="isHomePressed = true"
                            @touchend="isHomePressed = false"
                        >
                            <Home class="h-4 w-4 sm:mr-2" />
                            <span class="hidden sm:inline">Beranda</span>
                        </Button>
                    </a>
                </Motion>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Expired State -->
            <AnimatePresence mode="wait">
                <Motion
                    v-if="expired"
                    :key="'expired'"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -20 }"
                    :transition="springTransition"
                    class="text-center"
                >
                    <Motion
                        :initial="{ scale: 0, opacity: 0 }"
                        :animate="{ scale: 1, opacity: 1 }"
                        :transition="{ ...bouncyTransition, delay: 0.2 }"
                        class="mb-6 inline-flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-red-100 to-orange-100 shadow-lg dark:from-red-900/30 dark:to-orange-900/30"
                    >
                        <XCircle class="h-12 w-12 text-red-600 dark:text-red-400" />
                    </Motion>

                    <h1 class="mb-4 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Akses Tidak Tersedia
                    </h1>
                    <p class="mb-2 text-muted-foreground">
                        Pesanan <strong>{{ orderNumber }}</strong> sudah tidak dapat diakses
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Akses ke pesanan ini telah kedaluwarsa karena pesanan telah selesai atau dibatalkan.
                    </p>

                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.4 }"
                        class="mt-8"
                    >
                        <a :href="home()">
                            <Button
                                size="lg"
                                class="ios-button gap-2 rounded-xl"
                            >
                                <Home class="h-4 w-4" />
                                Kembali ke Beranda
                            </Button>
                        </a>
                    </Motion>
                </Motion>

                <!-- Verification Form -->
                <Motion
                    v-else-if="!order || !verified"
                    :key="'verification'"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -20 }"
                    :transition="springTransition"
                >
                    <!-- Header -->
                    <div class="mb-8 text-center">
                        <Motion
                            :initial="{ scale: 0, opacity: 0 }"
                            :animate="{ scale: 1, opacity: 1 }"
                            :transition="{ ...bouncyTransition, delay: 0.2 }"
                            class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-blue-100 to-brand-gold-100 shadow-lg dark:from-brand-blue-900/30 dark:to-brand-gold-900/30"
                        >
                            <ShieldCheck class="h-10 w-10 text-brand-blue" />
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.3 }"
                        >
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Verifikasi Nomor Telepon
                            </h1>
                            <p class="mt-2 text-sm text-muted-foreground sm:text-base">
                                Untuk keamanan, silakan verifikasi nomor telepon Anda
                            </p>
                        </Motion>
                    </div>

                    <!-- Security Info Alert -->
                    <Motion
                        :initial="{ opacity: 0, y: -10, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ ...bouncyTransition, delay: 0.4 }"
                        class="mb-6 overflow-hidden rounded-2xl border border-brand-blue-200 bg-gradient-to-r from-brand-blue-50 to-white p-4 dark:border-brand-blue-800/30 dark:from-brand-blue-900/20 dark:to-background"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-blue shadow-sm">
                                <Lock class="h-4 w-4 text-white" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-foreground">Perlindungan Privasi</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Masukkan nomor telepon yang Anda gunakan saat memesan untuk mengakses detail pesanan.
                                </p>
                            </div>
                        </div>
                    </Motion>

                    <!-- Verification Form Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.5 }"
                        class="ios-card rounded-2xl border border-brand-blue-100 bg-card p-6 dark:border-brand-blue-800/30"
                    >
                        <form @submit.prevent="submitVerification" class="space-y-6">
                            <!-- Phone Input -->
                            <div class="space-y-2">
                                <Label for="customer_phone" class="flex items-center gap-2">
                                    <Phone class="h-4 w-4 text-brand-blue" />
                                    Nomor Telepon
                                </Label>
                                <Input
                                    id="customer_phone"
                                    v-model="form.customer_phone"
                                    type="tel"
                                    placeholder="08xxxxxxxxxx atau +62xxxxxxxxxx"
                                    class="ios-input h-12 rounded-xl"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.customer_phone }"
                                    :disabled="form.processing"
                                    required
                                />
                                <p v-if="form.errors.customer_phone" class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400">
                                    <AlertCircle class="h-4 w-4" />
                                    {{ form.errors.customer_phone }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    Gunakan nomor yang sama dengan saat memesan
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <Motion
                                :animate="{ scale: isSubmitPressed ? 0.97 : 1 }"
                                :transition="snappyTransition"
                            >
                                <Button
                                    type="submit"
                                    size="lg"
                                    class="ios-button w-full gap-2 rounded-xl"
                                    :disabled="form.processing"
                                    @mousedown="isSubmitPressed = true"
                                    @mouseup="isSubmitPressed = false"
                                    @mouseleave="isSubmitPressed = false"
                                    @touchstart.passive="isSubmitPressed = true"
                                    @touchend="isSubmitPressed = false"
                                >
                                    <ShieldCheck class="h-5 w-5" />
                                    {{ form.processing ? 'Memverifikasi...' : 'Verifikasi & Lihat Pesanan' }}
                                </Button>
                            </Motion>
                        </form>
                    </Motion>

                    <!-- Info Note -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.6 }"
                        class="mt-6 rounded-2xl border border-brand-gold-200 bg-gradient-to-r from-brand-gold-50 to-white p-4 dark:border-brand-gold-800/30 dark:from-brand-gold-900/20 dark:to-background"
                    >
                        <p class="text-center text-xs text-muted-foreground">
                            🔒 Data Anda aman dan terenkripsi. Kami melindungi privasi informasi pesanan Anda.
                        </p>
                    </Motion>
                </Motion>

                <!-- Order Details (After Successful Verification) -->
                <Motion
                    v-else-if="order && verified"
                    :key="'order-details'"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -20 }"
                    :transition="springTransition"
                >
                    <!-- Success Header -->
                    <div class="mb-8 text-center">
                        <Motion
                            :initial="{ scale: 0, opacity: 0 }"
                            :animate="{ scale: 1, opacity: 1 }"
                            :transition="{ ...bouncyTransition, delay: 0.2 }"
                            class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-green-100 to-brand-gold-100 shadow-lg dark:from-green-900/30 dark:to-brand-gold-900/30"
                        >
                            <CheckCircle2 class="h-10 w-10 text-green-600 dark:text-green-400" />
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springTransition, delay: 0.3 }"
                        >
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Detail Pesanan
                            </h1>
                            <p class="mt-2 text-sm text-muted-foreground sm:text-base">
                                Pesanan #{{ order.order_number }}
                            </p>
                        </Motion>
                    </div>

                    <!-- Status Badge -->
                    <Motion
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ ...springTransition, delay: 0.4 }"
                        class="mb-6 flex justify-center"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium"
                            :class="{
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200': order.status === 'pending',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200': order.status === 'confirmed' || order.status === 'preparing',
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200': order.status === 'ready' || order.status === 'delivered',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200': order.status === 'cancelled',
                            }"
                        >
                            {{ order.status_label }}
                        </div>
                    </Motion>

                    <!-- Customer Info -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.5 }"
                        class="ios-card mb-6 rounded-2xl border border-brand-blue-100 bg-card p-6 dark:border-brand-blue-800/30"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <User class="h-5 w-5 text-brand-blue" />
                            Data Penerima
                        </h2>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Nama</p>
                                    <p class="font-medium text-foreground">{{ order.customer_name }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Telepon</p>
                                    <p class="font-medium text-foreground">{{ order.customer_phone }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Alamat</p>
                                    <p class="font-medium text-foreground">{{ order.customer_address }}</p>
                                </div>
                            </div>

                            <div v-if="order.notes" class="flex items-start gap-3">
                                <FileText class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Catatan</p>
                                    <p class="font-medium text-foreground">{{ order.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Order Items -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.6 }"
                        class="ios-card mb-6 rounded-2xl border border-brand-blue-100 bg-card p-6 dark:border-brand-blue-800/30"
                    >
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <Package class="h-5 w-5 text-brand-blue" />
                            Detail Pesanan
                        </h2>

                        <!-- Items List -->
                        <div class="mb-4 space-y-3">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
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
                                    {{ formatPrice(item.subtotal) }}
                                </p>
                            </div>
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
                                <span class="price-tag text-xl font-bold">
                                    {{ formatPrice(order.total) }}
                                </span>
                            </div>
                        </div>
                    </Motion>

                    <!-- Info Note -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.7 }"
                        class="rounded-2xl border border-brand-blue-200 bg-gradient-to-r from-brand-blue-50 to-brand-gold-50 p-4 dark:border-brand-blue-800/30 dark:from-brand-blue-900/20 dark:to-brand-gold-900/20"
                    >
                        <p class="text-center text-sm text-brand-blue-700 dark:text-brand-blue-200">
                            📦 Pesanan dibuat {{ order.timestamps.created_at_human }}
                        </p>
                    </Motion>
                </Motion>
            </AnimatePresence>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-brand-blue-100 bg-gradient-to-b from-brand-blue-50/50 to-white dark:border-brand-blue-900/30 dark:from-brand-blue-900/20 dark:to-background">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-2">
                    <p class="text-center text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} {{ store.name }}.
                    </p>
                    <p class="text-xs text-brand-gold">{{ store.tagline }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

