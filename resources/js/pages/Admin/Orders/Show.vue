<script setup lang="ts">
/**
 * Admin Order Detail Page
 * Menampilkan detail pesanan dengan fitur update status, yaitu:
 * - Customer info card dengan nama, telepon, alamat
 * - Order items dengan card layout untuk mobile dan table untuk desktop
 * - Order summary dengan subtotal, delivery fee, total
 * - Status timeline dengan timestamps
 * - Sticky bottom action bar untuk mobile dengan quick status update
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetFooter,
} from '@/components/ui/sheet'
import PasswordConfirmDialog from '@/components/admin/PasswordConfirmDialog.vue'
import StatusUpdateSuccessDialog from '@/components/admin/StatusUpdateSuccessDialog.vue'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as ordersIndex, updateStatus } from '@/routes/admin/orders'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    ArrowLeft,
    User,
    Phone,
    MapPin,
    FileText,
    Package,
    Clock,
    CheckCircle2,
    XCircle,
    Truck,
    ChefHat,
    CircleDot,
    MessageSquare,
    ExternalLink,
    ShoppingBag,
    Receipt,
    ChevronRight,
    Check,
    Loader2,
    Navigation,
} from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { usePhoneFormat } from '@/composables/usePhoneFormat'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Phone format composable untuk WhatsApp integration
 */
const { openWhatsApp: openWhatsAppComposable } = usePhoneFormat()

interface OrderItem {
    id: number
    product_name: string
    product_price: number
    quantity: number
    subtotal: number
    notes: string | null
}

interface OrderTimestamps {
    created_at: string | null
    created_at_human: string | null
    confirmed_at: string | null
    preparing_at: string | null
    ready_at: string | null
    delivered_at: string | null
    cancelled_at: string | null
}

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address: string
    notes: string | null
    subtotal: number
    delivery_fee: number
    total: number
    status: string
    status_label: string
    items: OrderItem[]
    items_count: number
    cancellation_reason: string | null
    timestamps: OrderTimestamps
}

interface WhatsAppUrls {
    confirmed: string
    preparing: string
    ready: string
    delivered: string
    cancelled: string
}

interface Props {
    order: Order
    statuses: Record<string, string>
    whatsappUrls: WhatsAppUrls
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pesanan', href: ordersIndex().url },
    { title: props.order.order_number, href: '#' },
]

// Flash messages dari session
const flashSuccess = computed(() => (page.props as unknown as { flash?: { success?: string } }).flash?.success)
const flashError = computed(() => (page.props as unknown as { flash?: { error?: string } }).flash?.error)

// Local state untuk update status
const selectedStatus = ref(props.order.status)
const cancellationReason = ref('')
const showConfirmDialog = ref(false)
const showPasswordDialog = ref(false)
const showSuccessDialog = ref(false)
const successStatus = ref('')
const isUpdating = ref(false)
const showMobileStatusSheet = ref(false)

/**
 * Get next status untuk quick action pada mobile
 * Menghitung status selanjutnya berdasarkan current status
 */
const nextStatus = computed(() => {
    switch (props.order.status) {
        case 'pending':
            return { status: 'confirmed', label: 'Konfirmasi', icon: Check }
        case 'confirmed':
            return { status: 'preparing', label: 'Proses', icon: ChefHat }
        case 'preparing':
            return { status: 'ready', label: 'Siap Kirim', icon: Package }
        case 'ready':
            return { status: 'delivered', label: 'Selesai', icon: Truck }
        default:
            return null
    }
})

/**
 * Quick update ke status berikutnya (untuk mobile bottom bar)
 */
function quickUpdateStatus() {
    if (!nextStatus.value) return
    haptic.medium()
    selectedStatus.value = nextStatus.value.status
    showConfirmDialog.value = true
}

/**
 * Computed untuk cek apakah status cancelled
 */
const isCancelledStatus = computed(() => selectedStatus.value === 'cancelled')

/**
 * Computed untuk cek apakah status berubah
 */
const hasStatusChanged = computed(() => selectedStatus.value !== props.order.status)

/**
 * Get status badge class
 */
function getStatusClass(orderStatus: string): string {
    switch (orderStatus) {
        case 'pending':
            return 'admin-badge--pending'
        case 'confirmed':
            return 'admin-badge--confirmed'
        case 'preparing':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
        case 'ready':
            return 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300'
        case 'delivered':
            return 'admin-badge--success'
        case 'cancelled':
            return 'admin-badge--destructive'
        default:
            return ''
    }
}

/**
 * Get timeline icon berdasarkan status
 */
function getTimelineIcon(status: string) {
    switch (status) {
        case 'created':
            return Clock
        case 'confirmed':
            return CheckCircle2
        case 'preparing':
            return ChefHat
        case 'ready':
            return Package
        case 'delivered':
            return Truck
        case 'cancelled':
            return XCircle
        default:
            return CircleDot
    }
}

/**
 * Format date untuk display
 */
function formatDate(dateString: string | null): string {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Open WhatsApp dengan nomor telepon customer (tanpa template)
 * menggunakan composable usePhoneFormat untuk format internasional
 */
function openWhatsApp(phone: string) {
    haptic.medium()
    openWhatsAppComposable(phone)
}

/**
 * Open WhatsApp dengan template message berdasarkan status
 */
function openWhatsAppWithTemplate(status: string) {
    haptic.medium()
    const url = props.whatsappUrls[status as keyof WhatsAppUrls]
    if (url) {
        window.open(url, '_blank')
    }
}

/**
 * Buka dialog konfirmasi update status (tahap 1)
 * Setelah user konfirmasi akan dilanjutkan ke password verification
 */
function confirmStatusUpdate() {
    if (!hasStatusChanged.value) return
    if (isCancelledStatus.value && !cancellationReason.value.trim()) {
        return
    }
    haptic.medium()
    showConfirmDialog.value = true
}

/**
 * Lanjutkan ke password verification setelah konfirmasi (tahap 2)
 */
function proceedToPasswordVerification() {
    showConfirmDialog.value = false
    showPasswordDialog.value = true
}

/**
 * Eksekusi update status setelah password diverifikasi (tahap 3)
 * Setelah sukses akan menampilkan success dialog dengan opsi WhatsApp
 */
function executeStatusUpdate() {
    haptic.heavy()
    isUpdating.value = true
    showPasswordDialog.value = false

    // Simpan status yang akan diupdate untuk success dialog
    const statusToUpdate = selectedStatus.value

    router.patch(
        updateStatus(props.order.id).url,
        {
            status: statusToUpdate,
            cancellation_reason: isCancelledStatus.value ? cancellationReason.value : null,
        },
        {
            onSuccess: () => {
                haptic.success()
                // Tampilkan success dialog dengan status yang baru
                successStatus.value = statusToUpdate
                showSuccessDialog.value = true
            },
            onError: () => {
                haptic.error()
            },
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handle kirim WhatsApp dari success dialog
 */
function handleSuccessWhatsApp() {
    const url = props.whatsappUrls[successStatus.value as keyof WhatsAppUrls]
    if (url) {
        window.open(url, '_blank')
    }
}

/**
 * Handle tutup success dialog
 */
function handleSuccessDialogClose() {
    showSuccessDialog.value = false
}

/**
 * Handle cancel password dialog
 */
function handlePasswordDialogCancel() {
    showPasswordDialog.value = false
}

/**
 * Build timeline items dari timestamps
 */
const timelineItems = computed(() => {
    const items = []

    if (props.order.timestamps.created_at) {
        items.push({
            status: 'created',
            label: 'Pesanan Dibuat',
            timestamp: props.order.timestamps.created_at,
            active: true,
        })
    }

    if (props.order.timestamps.confirmed_at) {
        items.push({
            status: 'confirmed',
            label: 'Dikonfirmasi',
            timestamp: props.order.timestamps.confirmed_at,
            active: true,
        })
    }

    if (props.order.timestamps.preparing_at) {
        items.push({
            status: 'preparing',
            label: 'Sedang Diproses',
            timestamp: props.order.timestamps.preparing_at,
            active: true,
        })
    }

    if (props.order.timestamps.ready_at) {
        items.push({
            status: 'ready',
            label: 'Siap Dikirim',
            timestamp: props.order.timestamps.ready_at,
            active: true,
        })
    }

    if (props.order.timestamps.delivered_at) {
        items.push({
            status: 'delivered',
            label: 'Terkirim',
            timestamp: props.order.timestamps.delivered_at,
            active: true,
        })
    }

    if (props.order.timestamps.cancelled_at) {
        items.push({
            status: 'cancelled',
            label: 'Dibatalkan',
            timestamp: props.order.timestamps.cancelled_at,
            active: true,
        })
    }

    return items
})

/**
 * Handle back navigation
 */
function goBack() {
    haptic.light()
    router.visit(ordersIndex().url)
}

/**
 * Open Google Maps dengan alamat customer
 */
function openMaps(address: string) {
    haptic.medium()
    const encodedAddress = encodeURIComponent(address)
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank')
}

/**
 * Make phone call
 */
function makeCall(phone: string) {
    haptic.medium()
    window.location.href = `tel:${phone}`
}
</script>

<template>
    <Head :title="`Detail Pesanan - ${order.order_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="ios-button h-10 w-10 shrink-0"
                            @click="goBack"
                        >
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-3">
                                <h1 class="text-xl font-bold tracking-tight md:text-2xl">
                                    {{ order.order_number }}
                                </h1>
                                <Motion
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :transition="springPresets.bouncy"
                                >
                                    <span :class="['admin-badge text-sm', getStatusClass(order.status)]">
                                        {{ order.status_label }}
                                    </span>
                                </Motion>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Detail pesanan dan update status
                            </p>
                        </div>
                    </div>
                </Motion>

                <!-- Flash Messages -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashSuccess"
                        class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        {{ flashSuccess }}
                    </div>
                </Transition>
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashError"
                        class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {{ flashError }}
                    </div>
                </Transition>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <!-- Customer Info Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <User />
                                        Informasi Customer
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <!-- Nama Customer -->
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                                                <User class="h-5 w-5 text-primary" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Nama</p>
                                                <p class="truncate font-semibold">{{ order.customer_name }}</p>
                                            </div>
                                        </div>

                                        <!-- Telepon - dengan action buttons -->
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/40">
                                                <Phone class="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Telepon</p>
                                                <p class="font-semibold">{{ order.customer_phone }}</p>
                                                <!-- Quick action buttons untuk mobile -->
                                                <div class="mt-2 flex gap-2">
                                                    <button
                                                        class="ios-button flex h-9 items-center gap-1.5 rounded-lg bg-green-600 px-3 text-sm font-medium text-white active:scale-95"
                                                        @click="openWhatsApp(order.customer_phone)"
                                                    >
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                        </svg>
                                                        <span class="hidden sm:inline">WhatsApp</span>
                                                    </button>
                                                    <button
                                                        class="ios-button flex h-9 items-center gap-1.5 rounded-lg border border-border bg-background px-3 text-sm font-medium active:scale-95"
                                                        @click="makeCall(order.customer_phone)"
                                                    >
                                                        <Phone class="h-4 w-4" />
                                                        <span class="hidden sm:inline">Telepon</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Alamat - dengan link ke maps -->
                                        <div v-if="order.customer_address" class="flex items-start gap-3 sm:col-span-2">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/40">
                                                <MapPin class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Alamat</p>
                                                <p class="font-medium">{{ order.customer_address }}</p>
                                                <button
                                                    class="mt-2 flex h-9 items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-3 text-sm font-medium text-orange-700 active:scale-95 dark:border-orange-800 dark:bg-orange-950 dark:text-orange-300"
                                                    @click="openMaps(order.customer_address)"
                                                >
                                                    <Navigation class="h-4 w-4" />
                                                    Buka di Maps
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Catatan -->
                                        <div v-if="order.notes" class="flex items-start gap-3 sm:col-span-2">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-muted">
                                                <FileText class="h-5 w-5 text-muted-foreground" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Catatan</p>
                                                <p class="font-medium">{{ order.notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Order Items Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <div class="flex items-center justify-between">
                                        <h3>
                                            <ShoppingBag />
                                            Detail Pesanan
                                        </h3>
                                        <Badge variant="secondary" class="tabular-nums">
                                            {{ order.items_count }} item
                                        </Badge>
                                    </div>
                                </div>
                                <div class="p-0">
                                    <!-- Mobile: Card-based layout -->
                                    <div class="flex flex-col gap-3 p-4 md:hidden">
                                        <Motion
                                            v-for="(item, index) in order.items"
                                            :key="item.id"
                                            :initial="{ opacity: 0, x: -20 }"
                                            :animate="{ opacity: 1, x: 0 }"
                                            :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                            class="rounded-xl border bg-muted/20 p-4"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="font-semibold leading-snug">{{ item.product_name }}</h4>
                                                    <p v-if="item.notes" class="mt-1 text-sm text-muted-foreground">
                                                        {{ item.notes }}
                                                    </p>
                                                </div>
                                                <Badge variant="secondary" class="shrink-0 tabular-nums">
                                                    x{{ item.quantity }}
                                                </Badge>
                                            </div>
                                            <div class="mt-3 flex items-center justify-between border-t border-border/50 pt-3">
                                                <span class="text-sm text-muted-foreground">
                                                    <PriceDisplay :price="item.product_price" size="sm" /> / item
                                                </span>
                                                <PriceDisplay :price="item.subtotal" size="base" class="font-bold text-primary" />
                                            </div>
                                        </Motion>
                                    </div>

                                    <!-- Desktop: Table layout -->
                                    <div class="hidden overflow-x-auto md:block">
                                        <table class="admin-table">
                                            <thead>
                                                <tr>
                                                    <th>Produk</th>
                                                    <th class="text-right">Harga</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <Motion
                                                    v-for="(item, index) in order.items"
                                                    :key="item.id"
                                                    tag="tr"
                                                    :initial="{ opacity: 0, x: -20 }"
                                                    :animate="{ opacity: 1, x: 0 }"
                                                    :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                                >
                                                    <td>
                                                        <div class="flex flex-col gap-0.5">
                                                            <span class="font-medium">{{ item.product_name }}</span>
                                                            <span v-if="item.notes" class="text-sm text-muted-foreground">
                                                                {{ item.notes }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <PriceDisplay :price="item.product_price" size="sm" />
                                                    </td>
                                                    <td class="text-center">
                                                        <Badge variant="outline" class="tabular-nums">
                                                            {{ item.quantity }}
                                                        </Badge>
                                                    </td>
                                                    <td class="text-right">
                                                        <PriceDisplay :price="item.subtotal" size="sm" class="font-semibold" />
                                                    </td>
                                                </Motion>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Order Summary -->
                                    <div class="border-t bg-muted/30 p-5">
                                        <div class="flex flex-col gap-3">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-muted-foreground">Subtotal</span>
                                                <PriceDisplay :price="order.subtotal" size="sm" />
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-muted-foreground">Ongkos Kirim</span>
                                                <PriceDisplay :price="order.delivery_fee" size="sm" />
                                            </div>
                                            <div class="flex justify-between border-t pt-3">
                                                <span class="text-lg font-bold">Total</span>
                                                <PriceDisplay :price="order.total" size="lg" class="text-xl! font-bold" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>
                    </div>

                    <!-- Sidebar -->
                    <div class="flex flex-col gap-6">
                        <!-- Update Status Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <CheckCircle2 />
                                        Update Status
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-4">
                                        <div class="admin-input-group">
                                            <Label for="status">Status Pesanan</Label>
                                            <select
                                                id="status"
                                                v-model="selectedStatus"
                                                class="admin-select"
                                            >
                                                <option
                                                    v-for="(label, value) in statuses"
                                                    :key="value"
                                                    :value="value"
                                                >
                                                    {{ label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Cancellation Reason -->
                                        <Transition
                                            enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                                            enter-from-class="opacity-0 -translate-y-2"
                                            enter-to-class="opacity-100 translate-y-0"
                                            leave-active-class="transition-all duration-200"
                                            leave-from-class="opacity-100"
                                            leave-to-class="opacity-0"
                                        >
                                            <div v-if="isCancelledStatus" class="admin-input-group">
                                                <Label for="cancellation_reason">
                                                    Alasan Pembatalan <span class="text-destructive">*</span>
                                                </Label>
                                                <textarea
                                                    id="cancellation_reason"
                                                    v-model="cancellationReason"
                                                    placeholder="Masukkan alasan pembatalan..."
                                                    rows="3"
                                                    class="admin-textarea"
                                                />
                                            </div>
                                        </Transition>

                                        <Button
                                            :disabled="!hasStatusChanged || (isCancelledStatus && !cancellationReason.trim())"
                                            class="admin-btn-primary"
                                            @click="confirmStatusUpdate"
                                        >
                                            Update Status
                                        </Button>

                                        <!-- WhatsApp Template Button -->
                                        <div class="mt-2 border-t border-border pt-4">
                                            <p class="mb-2 text-sm text-muted-foreground">
                                                Kirim notifikasi ke customer:
                                            </p>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="ios-button w-full gap-2 border-green-500 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20"
                                                @click="openWhatsAppWithTemplate(order.status)"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                                Kirim via WhatsApp
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Status Timeline Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <Clock />
                                        Timeline
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="status-timeline">
                                        <Motion
                                            v-for="(item, index) in timelineItems"
                                            :key="item.status"
                                            :initial="{ opacity: 0, x: -20 }"
                                            :animate="{ opacity: 1, x: 0 }"
                                            :transition="{ ...springPresets.ios, delay: 0.25 + index * 0.05 }"
                                            class="timeline-item"
                                            :class="{ completed: item.status !== 'cancelled', active: index === timelineItems.length - 1 }"
                                        >
                                            <div class="timeline-dot">
                                                <component
                                                    :is="getTimelineIcon(item.status)"
                                                    class="h-4 w-4"
                                                />
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ item.label }}</span>
                                                <span class="text-sm text-muted-foreground">
                                                    {{ formatDate(item.timestamp) }}
                                                </span>
                                            </div>
                                        </Motion>
                                    </div>

                                    <!-- Cancellation Reason Display -->
                                    <Motion
                                        v-if="order.status === 'cancelled' && order.cancellation_reason"
                                        :initial="{ opacity: 0, y: 10 }"
                                        :animate="{ opacity: 1, y: 0 }"
                                        :transition="springPresets.ios"
                                        class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950"
                                    >
                                        <div class="flex items-start gap-2">
                                            <MessageSquare class="mt-0.5 h-4 w-4 shrink-0 text-red-600 dark:text-red-400" />
                                            <div>
                                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                    Alasan Pembatalan
                                                </p>
                                                <p class="text-sm text-red-700 dark:text-red-300">
                                                    {{ order.cancellation_reason }}
                                                </p>
                                            </div>
                                        </div>
                                    </Motion>
                                </div>
                            </div>
                        </Motion>
                    </div>
                </div>

                <!-- Bottom padding untuk mobile nav + sticky bar -->
                <div class="h-40 md:hidden" />
            </div>
        </PullToRefresh>

        <!-- Mobile Sticky Bottom Action Bar -->
        <div
            v-if="nextStatus"
            class="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 md:hidden"
            style="padding-bottom: env(safe-area-inset-bottom)"
        >
            <div class="flex items-center gap-3 p-4">
                <!-- Status Info -->
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-muted-foreground">Status Sekarang</p>
                    <div class="flex items-center gap-2">
                        <span :class="['admin-badge', getStatusClass(order.status)]">
                            {{ order.status_label }}
                        </span>
                        <ChevronRight class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm font-medium text-primary">
                            {{ statuses[nextStatus.status] }}
                        </span>
                    </div>
                </div>

                <!-- Quick Action Button -->
                <Button
                    class="h-12 shrink-0 gap-2 rounded-xl px-6"
                    :disabled="isUpdating"
                    @click="quickUpdateStatus"
                >
                    <Loader2 v-if="isUpdating" class="h-5 w-5 animate-spin" />
                    <component :is="nextStatus.icon" v-else class="h-5 w-5" />
                    {{ nextStatus.label }}
                </Button>
            </div>
        </div>

        <!-- More Options Sheet for Mobile -->
        <Sheet v-model:open="showMobileStatusSheet">
            <SheetContent side="bottom" class="rounded-t-3xl">
                <SheetHeader>
                    <SheetTitle>Update Status Pesanan</SheetTitle>
                    <SheetDescription>
                        Pilih status baru untuk pesanan {{ order.order_number }}
                    </SheetDescription>
                </SheetHeader>
                <div class="mt-4 flex flex-col gap-2">
                    <button
                        v-for="(label, value) in statuses"
                        :key="value"
                        class="flex items-center justify-between rounded-xl px-4 py-4 text-left transition-colors"
                        :class="[
                            selectedStatus === value
                                ? 'bg-primary/10 text-primary'
                                : 'hover:bg-muted/50',
                        ]"
                        @click="selectedStatus = value as string; showMobileStatusSheet = false; confirmStatusUpdate()"
                    >
                        <span class="font-medium">{{ label }}</span>
                        <Check v-if="selectedStatus === value" class="h-5 w-5 text-primary" />
                    </button>
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>

    <!-- Confirm Status Update Dialog -->
    <Dialog v-model:open="showConfirmDialog">
        <DialogContent class="rounded-2xl">
            <DialogHeader>
                <DialogTitle>Konfirmasi Update Status</DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin mengubah status pesanan menjadi
                    <strong>{{ statuses[selectedStatus] }}</strong>?
                    <span v-if="isCancelledStatus" class="mt-2 block text-red-600">
                        Tindakan pembatalan tidak dapat dibatalkan.
                    </span>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    class="ios-button"
                    @click="showConfirmDialog = false"
                >
                    Batal
                </Button>
                <Button
                    :variant="isCancelledStatus ? 'destructive' : 'default'"
                    class="ios-button"
                    @click="proceedToPasswordVerification"
                >
                    Ya, Lanjutkan
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Password Confirmation Dialog -->
    <PasswordConfirmDialog
        v-model:open="showPasswordDialog"
        :title="isCancelledStatus ? 'Konfirmasi Pembatalan' : 'Konfirmasi Update Status'"
        :description="`Masukkan password Anda untuk ${isCancelledStatus ? 'membatalkan' : 'mengupdate status'} pesanan ini.`"
        :confirm-label="isCancelledStatus ? 'Batalkan Pesanan' : 'Update Status'"
        :confirm-variant="isCancelledStatus ? 'destructive' : 'default'"
        :loading="isUpdating"
        @confirm="executeStatusUpdate"
        @cancel="handlePasswordDialogCancel"
    />

    <!-- Success Dialog setelah update status -->
    <StatusUpdateSuccessDialog
        v-model:open="showSuccessDialog"
        :new-status="successStatus"
        :new-status-label="statuses[successStatus] || ''"
        :order-number="order.order_number"
        :customer-name="order.customer_name"
        :whatsapp-url="whatsappUrls[successStatus as keyof WhatsAppUrls]"
        @close="handleSuccessDialogClose"
        @send-whats-app="handleSuccessWhatsApp"
    />
</template>
