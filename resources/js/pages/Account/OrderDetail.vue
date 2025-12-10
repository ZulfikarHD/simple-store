<script setup lang="ts">
/**
 * Account Order Detail Page
 * Halaman detail pesanan user dengan status tracking, items list,
 * dan iOS-like animations
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import StoreHeader from '@/components/store/StoreHeader.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { OrderStatusBadge } from '@/components/store'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ArrowLeft,
    Package,
    Clock,
    MapPin,
    Phone,
    User,
    FileText,
    CheckCircle,
    ChefHat,
    Truck,
    XCircle,
    ExternalLink,
} from 'lucide-vue-next'

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

/**
 * Interface untuk order item
 */
interface OrderItem {
    id: number
    product_name: string
    product_price: number
    quantity: number
    subtotal: number
    notes?: string | null
}

/**
 * Interface untuk order timestamps
 */
interface OrderTimestamps {
    created_at: string | null
    created_at_human: string | null
    confirmed_at: string | null
    preparing_at: string | null
    ready_at: string | null
    delivered_at: string | null
    cancelled_at: string | null
}

/**
 * Interface untuk order data
 */
interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address: string | null
    notes: string | null
    subtotal: number
    delivery_fee: number
    total: number
    status: OrderStatus
    status_label: string
    items: OrderItem[]
    items_count: number
    cancellation_reason: string | null
    timestamps: OrderTimestamps
    whatsapp_url?: string // URL WhatsApp untuk konfirmasi (hanya untuk pending orders)
}

/**
 * Props dari controller
 */
interface Props {
    order: Order
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

const haptic = useHapticFeedback()

/**
 * Press states untuk iOS-like feedback
 */
const isBackPressed = ref(false)
const isWhatsAppPressed = ref(false)

/**
 * Buka WhatsApp untuk konfirmasi pesanan
 */
function openWhatsApp() {
    if (props.order.whatsapp_url) {
        haptic.medium()
        window.open(props.order.whatsapp_url, '_blank')
    }
}

/**
 * Status timeline steps
 */
const statusSteps = computed(() => {
    const steps = [
        {
            status: 'pending' as const,
            label: 'Menunggu',
            icon: Clock,
            time: props.order.timestamps.created_at,
        },
        {
            status: 'confirmed' as const,
            label: 'Dikonfirmasi',
            icon: CheckCircle,
            time: props.order.timestamps.confirmed_at,
        },
        {
            status: 'preparing' as const,
            label: 'Diproses',
            icon: ChefHat,
            time: props.order.timestamps.preparing_at,
        },
        {
            status: 'ready' as const,
            label: 'Siap',
            icon: Package,
            time: props.order.timestamps.ready_at,
        },
        {
            status: 'delivered' as const,
            label: 'Selesai',
            icon: Truck,
            time: props.order.timestamps.delivered_at,
        },
    ]

    // If cancelled, show cancelled status
    if (props.order.status === 'cancelled') {
        return [
            ...steps.slice(0, 1),
            {
                status: 'cancelled' as const,
                label: 'Dibatalkan',
                icon: XCircle,
                time: props.order.timestamps.cancelled_at,
            },
        ]
    }

    return steps
})

/**
 * Get current step index
 */
const currentStepIndex = computed(() => {
    const statusOrder: OrderStatus[] = ['pending', 'confirmed', 'preparing', 'ready', 'delivered']
    if (props.order.status === 'cancelled') return -1
    return statusOrder.indexOf(props.order.status)
})

/**
 * Format harga ke format Rupiah Indonesia
 */
function formatPrice(price: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price)
}

/**
 * Format tanggal
 */
function formatDate(dateString: string | null): string {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head :title="`Pesanan ${order.order_number} - ${store.name}`">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-brand-blue-200/30 dark:border-brand-blue-800/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <StoreHeader />
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content dengan Pull to Refresh -->
        <PullToRefresh>
            <main class="mx-auto w-full max-w-2xl flex-1 px-4 py-4 sm:px-6 sm:py-8">
                <!-- Compact Header untuk Mobile -->
                <Motion
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springTransition"
                    class="mb-4"
                >
                    <!-- Mobile: Inline header -->
                    <div class="flex items-center justify-between gap-3 sm:hidden">
                        <Motion
                            :animate="{ scale: isBackPressed ? 0.9 : 1 }"
                            :transition="snappyTransition"
                        >
                            <Link
                                href="/account/orders"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-muted/50"
                                @mousedown="isBackPressed = true"
                                @mouseup="isBackPressed = false"
                                @mouseleave="isBackPressed = false"
                                @touchstart.passive="isBackPressed = true"
                                @touchend="isBackPressed = false"
                            >
                                <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                            </Link>
                        </Motion>
                        <div class="min-w-0 flex-1 text-center">
                            <h1 class="truncate text-base font-bold text-foreground">{{ order.order_number }}</h1>
                            <OrderStatusBadge :status="order.status" />
                        </div>
                        <div class="h-10 w-10" /> <!-- Spacer untuk balance -->
                    </div>

                    <!-- Desktop: Full header -->
                    <div class="hidden sm:block">
                        <Motion
                            :animate="{ scale: isBackPressed ? 0.95 : 1, opacity: isBackPressed ? 0.7 : 1 }"
                            :transition="snappyTransition"
                        >
                            <Link
                                href="/account/orders"
                                class="ios-button mb-4 inline-flex h-11 items-center gap-2 rounded-xl px-3 text-sm text-muted-foreground hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
                                @mousedown="isBackPressed = true"
                                @mouseup="isBackPressed = false"
                                @mouseleave="isBackPressed = false"
                                @touchstart.passive="isBackPressed = true"
                                @touchend="isBackPressed = false"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Kembali ke Riwayat
                            </Link>
                        </Motion>
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                                    {{ order.order_number }}
                                </h1>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ formatDate(order.timestamps.created_at) }}
                                </p>
                            </div>
                            <OrderStatusBadge :status="order.status" />
                        </div>
                    </div>
                </Motion>

                <!-- WhatsApp Confirmation Alert untuk Pending Orders -->
                <Motion
                    v-if="order.status === 'pending' && order.whatsapp_url"
                    :initial="{ opacity: 0, y: -10, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ ...springTransition, delay: 0.05 }"
                    class="mb-4"
                >
                    <div class="overflow-hidden rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-amber-50 to-orange-50 p-4 shadow-lg shadow-amber-500/10 dark:border-amber-600 dark:from-amber-900/30 dark:to-orange-900/30">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 shadow-md shadow-amber-500/30">
                                    <Clock class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                                        Menunggu Konfirmasi
                                    </p>
                                    <p class="text-sm text-amber-700 dark:text-amber-400">
                                        Kirim pesan WhatsApp untuk mengkonfirmasi pesanan ini
                                    </p>
                                </div>
                            </div>
                            <Motion
                                :animate="{ scale: isWhatsAppPressed ? 0.95 : 1 }"
                                :transition="snappyTransition"
                                class="shrink-0"
                            >
                                <button
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-green-600/30 transition-colors hover:bg-green-700 sm:w-auto"
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
                                </button>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Status Timeline -->
                <Motion
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    class="mb-4"
                >
                    <Card class="overflow-hidden rounded-2xl border-brand-blue-100 dark:border-brand-blue-800/30">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Status Pesanan</CardTitle>
                        </CardHeader>
                        <CardContent class="pb-4">
                            <div class="relative">
                                <!-- Progress Line -->
                                <div class="absolute left-4 top-4 h-[calc(100%-32px)] w-0.5 bg-muted sm:left-5" />
                                <div
                                    v-if="currentStepIndex >= 0"
                                    class="absolute left-4 top-4 w-0.5 bg-brand-blue-500 transition-all duration-500 sm:left-5"
                                    :style="{ height: `${Math.min((currentStepIndex / (statusSteps.length - 1)) * 100, 100)}%` }"
                                />

                                <!-- Steps -->
                                <div class="space-y-4">
                                    <div
                                        v-for="(step, index) in statusSteps"
                                        :key="step.status"
                                        class="relative flex items-start gap-3 sm:gap-4"
                                    >
                                        <div
                                            class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10"
                                            :class="[
                                                index <= currentStepIndex || step.status === order.status
                                                    ? step.status === 'cancelled'
                                                        ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                                                        : 'bg-brand-blue-100 text-brand-blue-600 dark:bg-brand-blue-900/30 dark:text-brand-blue-400'
                                                    : 'bg-muted text-muted-foreground',
                                            ]"
                                        >
                                            <component :is="step.icon" class="h-4 w-4 sm:h-5 sm:w-5" />
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1">
                                            <p
                                                class="text-sm font-medium"
                                                :class="[
                                                    index <= currentStepIndex || step.status === order.status
                                                        ? 'text-foreground'
                                                        : 'text-muted-foreground',
                                                ]"
                                            >
                                                {{ step.label }}
                                            </p>
                                            <p v-if="step.time" class="text-xs text-muted-foreground">
                                                {{ formatDate(step.time) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancellation Reason -->
                            <div v-if="order.status === 'cancelled' && order.cancellation_reason" class="mt-4 rounded-xl bg-red-50 p-3 dark:bg-red-900/20">
                                <p class="text-sm text-red-700 dark:text-red-400">
                                    <strong>Alasan:</strong> {{ order.cancellation_reason }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </Motion>

                <!-- Order Items -->
                <Motion
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.15 }"
                    class="mb-4"
                >
                    <Card class="overflow-hidden rounded-2xl border-brand-blue-100 dark:border-brand-blue-800/30">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Package class="h-4 w-4" />
                                Item Pesanan ({{ order.items_count }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pb-4">
                            <div class="space-y-3">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-start justify-between gap-3 rounded-xl bg-muted/50 p-3"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-foreground">{{ item.product_name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formatPrice(item.product_price) }} × {{ item.quantity }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 font-semibold text-foreground">
                                        {{ formatPrice(item.subtotal) }}
                                    </p>
                                </div>
                            </div>

                            <Separator class="my-4" />

                            <!-- Pricing Summary -->
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Subtotal</span>
                                    <span>{{ formatPrice(order.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Ongkos Kirim</span>
                                    <span>{{ formatPrice(order.delivery_fee) }}</span>
                                </div>
                                <Separator />
                                <div class="flex justify-between text-base font-bold">
                                    <span>Total</span>
                                    <span class="text-brand-blue">{{ formatPrice(order.total) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </Motion>

                <!-- Customer Info -->
                <Motion
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.2 }"
                    class="mb-4"
                >
                    <Card class="overflow-hidden rounded-2xl border-brand-blue-100 dark:border-brand-blue-800/30">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Info Pengiriman</CardTitle>
                        </CardHeader>
                        <CardContent class="pb-4">
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <User class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Nama</p>
                                        <p class="font-medium text-foreground">{{ order.customer_name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <Phone class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Telepon</p>
                                        <p class="font-medium text-foreground">{{ order.customer_phone }}</p>
                                    </div>
                                </div>
                                <div v-if="order.customer_address" class="flex items-start gap-3">
                                    <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Alamat</p>
                                        <p class="font-medium text-foreground">{{ order.customer_address }}</p>
                                    </div>
                                </div>
                                <div v-if="order.notes" class="flex items-start gap-3">
                                    <FileText class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Catatan</p>
                                        <p class="font-medium text-foreground">{{ order.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </Motion>
            </main>
        </PullToRefresh>

        <!-- Sticky WhatsApp CTA untuk Mobile Pending Orders -->
        <Motion
            v-if="order.status === 'pending' && order.whatsapp_url"
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ ...springTransition, delay: 0.3 }"
            class="fixed inset-x-0 bottom-20 z-40 md:hidden"
        >
            <div class="mx-4 overflow-hidden rounded-2xl border border-green-200 bg-white/95 p-3 shadow-xl shadow-green-500/10 backdrop-blur-md dark:border-green-800/30 dark:bg-background/95">
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-amber-600 dark:text-amber-400">⏳ Menunggu Konfirmasi</p>
                        <p class="truncate text-sm font-bold text-foreground">{{ order.order_number }}</p>
                    </div>
                    <Motion
                        :animate="{ scale: isWhatsAppPressed ? 0.95 : 1 }"
                        :transition="snappyTransition"
                    >
                        <button
                            class="flex shrink-0 items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-green-600/30 transition-colors hover:bg-green-700"
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
                            Konfirmasi
                            <ExternalLink class="h-4 w-4" />
                        </button>
                    </Motion>
                </div>
            </div>
        </Motion>

        <!-- Bottom padding untuk mobile nav + sticky CTA -->
        <div class="md:hidden" :class="order.status === 'pending' && order.whatsapp_url ? 'h-40' : 'h-20'" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>

