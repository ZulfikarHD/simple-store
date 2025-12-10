<script setup lang="ts">
/**
 * Admin Order Detail Page
 * Menampilkan detail pesanan dengan fitur update status, yaitu:
 * - Customer info card dengan nama, telepon, alamat
 * - Order items table dengan product name, quantity, price, subtotal
 * - Order summary dengan subtotal, delivery fee, total
 * - Status timeline dengan timestamps
 * - Update status section dengan dropdown dan confirmation
 * - iOS-like design dengan spring animations dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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

interface Props {
    order: Order
    statuses: Record<string, string>
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pesanan', href: ordersIndex().url },
    { title: props.order.order_number, href: '#' },
]

// Flash messages dari session
const flashSuccess = computed(() => page.props.flash?.success as string | undefined)
const flashError = computed(() => page.props.flash?.error as string | undefined)

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
 * Get status badge class untuk styling
 */
function getStatusClass(orderStatus: string): string {
    switch (orderStatus) {
        case 'pending':
            return 'bg-yellow-500 text-white'
        case 'confirmed':
            return 'bg-blue-500 text-white'
        case 'preparing':
            return 'bg-purple-500 text-white'
        case 'ready':
            return 'bg-cyan-500 text-white'
        case 'delivered':
            return 'bg-green-500 text-white'
        case 'cancelled':
            return 'bg-red-500 text-white'
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
 * Open WhatsApp dengan nomor telepon customer
 */
function openWhatsApp(phone: string) {
    haptic.medium()
    const cleanPhone = phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
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

    // Created
    if (props.order.timestamps.created_at) {
        items.push({
            status: 'created',
            label: 'Pesanan Dibuat',
            timestamp: props.order.timestamps.created_at,
            active: true,
        })
    }

    // Confirmed
    if (props.order.timestamps.confirmed_at) {
        items.push({
            status: 'confirmed',
            label: 'Dikonfirmasi',
            timestamp: props.order.timestamps.confirmed_at,
            active: true,
        })
    }

    // Preparing
    if (props.order.timestamps.preparing_at) {
        items.push({
            status: 'preparing',
            label: 'Sedang Diproses',
            timestamp: props.order.timestamps.preparing_at,
            active: true,
        })
    }

    // Ready
    if (props.order.timestamps.ready_at) {
        items.push({
            status: 'ready',
            label: 'Siap Dikirim',
            timestamp: props.order.timestamps.ready_at,
            active: true,
        })
    }

    // Delivered
    if (props.order.timestamps.delivered_at) {
        items.push({
            status: 'delivered',
            label: 'Terkirim',
            timestamp: props.order.timestamps.delivered_at,
            active: true,
        })
    }

    // Cancelled
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
            <div class="flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header dengan spring animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="ios-button h-8 w-8 p-0"
                                @click="goBack"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                                    {{ order.order_number }}
                                </h1>
                                <p class="text-muted-foreground">
                                    Detail pesanan dan update status
                                </p>
                            </div>
                        </div>
                    </div>
                    <Motion
                        :initial="{ scale: 0 }"
                        :animate="{ scale: 1 }"
                        :transition="{ ...springPresets.bouncy, delay: 0.1 }"
                    >
                        <Badge
                        :class="getStatusClass(order.status)"
                        class="h-8 px-4 text-sm"
                    >
                        {{ order.status_label }}
                    </Badge>
                    </Motion>
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
                        class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
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
                        class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {{ flashError }}
                    </div>
                </Transition>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content (2 columns) -->
                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <!-- Customer Info Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <User class="h-5 w-5" />
                                    Informasi Customer
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="flex items-start gap-3">
                                        <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm text-muted-foreground">Nama</p>
                                            <p class="font-medium">{{ order.customer_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm text-muted-foreground">Telepon</p>
                                            <button
                                                class="ios-button font-medium text-primary hover:underline"
                                                @click="openWhatsApp(order.customer_phone)"
                                            >
                                                {{ order.customer_phone }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 sm:col-span-2">
                                        <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm text-muted-foreground">Alamat</p>
                                            <p class="font-medium">{{ order.customer_address }}</p>
                                        </div>
                                    </div>
                                    <div v-if="order.notes" class="flex items-start gap-3 sm:col-span-2">
                                        <FileText class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm text-muted-foreground">Catatan</p>
                                            <p class="font-medium">{{ order.notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        </Motion>

                        <!-- Order Items Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Package class="h-5 w-5" />
                                    Detail Pesanan
                                </CardTitle>
                                <CardDescription>
                                    {{ order.items_count }} item dalam pesanan ini
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="p-0">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="border-b bg-muted/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                                    Produk
                                                </th>
                                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                                    Harga
                                                </th>
                                                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                                    Qty
                                                </th>
                                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                                    Subtotal
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            <Motion
                                                v-for="(item, index) in order.items"
                                                :key="item.id"
                                                tag="tr"
                                                :initial="{ opacity: 0, x: -20 }"
                                                :animate="{ opacity: 1, x: 0 }"
                                                :transition="{ ...springPresets.ios, delay: 0.15 + index * 0.03 }"
                                                class="transition-colors hover:bg-muted/50"
                                            >
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="font-medium">{{ item.product_name }}</span>
                                                        <span v-if="item.notes" class="text-sm text-muted-foreground">
                                                            {{ item.notes }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <PriceDisplay :price="item.product_price" size="sm" />
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <Badge variant="outline">
                                                        {{ item.quantity }}
                                                    </Badge>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <PriceDisplay :price="item.subtotal" size="sm" />
                                                </td>
                                            </Motion>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Order Summary -->
                                <div class="border-t p-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Subtotal</span>
                                            <PriceDisplay :price="order.subtotal" size="sm" />
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Ongkos Kirim</span>
                                            <PriceDisplay :price="order.delivery_fee" size="sm" />
                                        </div>
                                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                            <span>Total</span>
                                            <PriceDisplay :price="order.total" size="lg" />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        </Motion>
                    </div>

                    <!-- Sidebar (1 column) -->
                    <div class="flex flex-col gap-6">
                        <!-- Update Status Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <CheckCircle2 class="h-5 w-5" />
                                    Update Status
                                </CardTitle>
                                <CardDescription>
                                    Perbarui status pesanan ini
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-2">
                                        <Label for="status">Status Pesanan</Label>
                                        <select
                                            id="status"
                                            v-model="selectedStatus"
                                            class="h-10 w-full rounded-xl border border-input bg-background px-3 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring"
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
                                        <div v-if="isCancelledStatus" class="flex flex-col gap-2">
                                            <Label for="cancellation_reason">
                                                Alasan Pembatalan <span class="text-destructive">*</span>
                                            </Label>
                                            <textarea
                                                id="cancellation_reason"
                                                v-model="cancellationReason"
                                                placeholder="Masukkan alasan pembatalan..."
                                                rows="3"
                                                class="flex min-h-[80px] w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            />
                                        </div>
                                    </Transition>

                                    <Button
                                        :disabled="!hasStatusChanged || (isCancelledStatus && !cancellationReason.trim())"
                                        class="ios-button w-full"
                                        @click="confirmStatusUpdate"
                                    >
                                        Update Status
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                        </Motion>

                        <!-- Status Timeline Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Clock class="h-5 w-5" />
                                    Timeline Status
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="relative flex flex-col gap-4">
                                    <Motion
                                        v-for="(item, index) in timelineItems"
                                        :key="item.status"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.25 + index * 0.05 }"
                                        class="flex gap-3"
                                    >
                                        <!-- Timeline Line -->
                                        <div class="flex flex-col items-center">
                                            <div
                                                :class="[
                                                    'flex h-8 w-8 items-center justify-center rounded-full',
                                                    item.status === 'cancelled'
                                                        ? 'bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-400'
                                                        : 'bg-green-100 text-green-600 dark:bg-green-950 dark:text-green-400'
                                                ]"
                                            >
                                                <component
                                                    :is="getTimelineIcon(item.status)"
                                                    class="h-4 w-4"
                                                />
                                            </div>
                                            <div
                                                v-if="index < timelineItems.length - 1"
                                                class="h-full w-px bg-border"
                                            />
                                        </div>

                                        <!-- Timeline Content -->
                                        <div class="flex flex-col pb-4">
                                            <span class="font-medium">{{ item.label }}</span>
                                            <span class="text-sm text-muted-foreground">
                                                {{ formatDate(item.timestamp) }}
                                            </span>
                                        </div>
                                    </Motion>

                                    <!-- Cancellation Reason -->
                                    <Motion
                                        v-if="order.status === 'cancelled' && order.cancellation_reason"
                                        :initial="{ opacity: 0, y: 10 }"
                                        :animate="{ opacity: 1, y: 0 }"
                                        :transition="springPresets.ios"
                                        class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950"
                                    >
                                        <div class="flex items-start gap-2">
                                            <MessageSquare class="mt-0.5 h-4 w-4 text-red-600 dark:text-red-400" />
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
                            </CardContent>
                        </Card>
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
