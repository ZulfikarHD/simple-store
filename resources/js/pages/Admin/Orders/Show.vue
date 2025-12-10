<script setup lang="ts">
/**
 * Admin Order Detail Page
 * Menampilkan detail pesanan dengan fitur update status, yaitu:
 * - Customer info card dengan nama, telepon, alamat
 * - Order items table dengan product name, quantity, price, subtotal
 * - Order summary dengan subtotal, delivery fee, total
 * - Status timeline dengan timestamps
 * - Update status section dengan dropdown dan confirmation
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
} from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

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
const isUpdating = ref(false)

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
 */
function openWhatsApp(phone: string) {
    haptic.medium()
    const cleanPhone = phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
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
 * Buka dialog konfirmasi update status
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
 * Eksekusi update status
 */
function executeStatusUpdate() {
    haptic.heavy()
    isUpdating.value = true

    router.patch(
        updateStatus(props.order.id).url,
        {
            status: selectedStatus.value,
            cancellation_reason: isCancelledStatus.value ? cancellationReason.value : null,
        },
        {
            onSuccess: () => {
                haptic.success()
            },
            onError: () => {
                haptic.error()
            },
            onFinish: () => {
                isUpdating.value = false
                showConfirmDialog.value = false
            },
        }
    )
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
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                                                <User class="h-5 w-5 text-primary" />
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Nama</p>
                                                <p class="font-semibold">{{ order.customer_name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/40">
                                                <Phone class="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Telepon</p>
                                                <button
                                                    class="flex items-center gap-1.5 font-semibold text-green-600 hover:underline dark:text-green-400"
                                                    @click="openWhatsApp(order.customer_phone)"
                                                >
                                                    {{ order.customer_phone }}
                                                    <ExternalLink class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3 sm:col-span-2">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/40">
                                                <MapPin class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Alamat</p>
                                                <p class="font-medium">{{ order.customer_address }}</p>
                                            </div>
                                        </div>
                                        <div v-if="order.notes" class="flex items-start gap-3 sm:col-span-2">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted">
                                                <FileText class="h-5 w-5 text-muted-foreground" />
                                            </div>
                                            <div>
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
                                    <div class="overflow-x-auto">
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

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>
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
                    :disabled="isUpdating"
                    @click="executeStatusUpdate"
                >
                    {{ isUpdating ? 'Memproses...' : 'Ya, Update' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
