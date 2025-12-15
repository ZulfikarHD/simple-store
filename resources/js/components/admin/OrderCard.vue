<script setup lang="ts">
/**
 * OrderCard Component
 * Card untuk menampilkan pesanan di mobile view dengan quick actions
 * dan urgency indicators berdasarkan waktu tunggu
 * Dengan password confirmation dan success dialog untuk WhatsApp integration
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { usePhoneFormat } from '@/composables/usePhoneFormat'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import PasswordConfirmDialog from '@/components/admin/PasswordConfirmDialog.vue'
import StatusUpdateSuccessDialog from '@/components/admin/StatusUpdateSuccessDialog.vue'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { OrderStatusBadge } from '@/components/store'
import { show } from '@/routes/admin/orders'
import {
    Eye,
    Check,
    Package,
    Clock,
    ChefHat,
    CircleCheck,
    Loader2,
    AlertCircle,
    Phone,
} from 'lucide-vue-next'

/**
 * Props interface untuk order data
 */
interface OrderItem {
    id: number
    product_name: string
    quantity: number
    subtotal: number
}

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address?: string
    total: number
    status: OrderStatus
    items: OrderItem[]
    created_at: string
    waiting_minutes?: number
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
    whatsappUrls?: WhatsAppUrls
}

const props = defineProps<Props>()

/**
 * Phone format composable untuk WhatsApp integration
 */
const { openWhatsApp: openWhatsAppComposable } = usePhoneFormat()

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

const emit = defineEmits<{
    statusUpdated: [orderId: number, newStatus: string]
}>()

/**
 * Loading state untuk quick action
 */
const isUpdating = ref(false)

/**
 * Computed untuk waktu tunggu dalam menit (integer, tanpa desimal)
 */
const waitingMinutes = computed(() => {
    if (props.order.waiting_minutes !== undefined) {
        // Pastikan integer tanpa desimal
        return Math.floor(props.order.waiting_minutes)
    }
    // Fallback: hitung dari created_at
    const created = new Date(props.order.created_at)
    const now = new Date()
    return Math.floor((now.getTime() - created.getTime()) / 60000)
})

/**
 * Get urgency level berdasarkan waktu tunggu dan status
 */
const urgencyLevel = computed(() => {
    // Hanya untuk status pending/confirmed/preparing
    if (!['pending', 'confirmed', 'preparing'].includes(props.order.status)) {
        return 'none'
    }
    if (waitingMinutes.value >= 15) return 'urgent'
    if (waitingMinutes.value >= 5) return 'warning'
    return 'normal'
})

/**
 * Get urgency border class
 */
const urgencyBorderClass = computed(() => {
    switch (urgencyLevel.value) {
        case 'urgent':
            return 'border-l-4 border-l-red-500'
        case 'warning':
            return 'border-l-4 border-l-amber-500'
        case 'normal':
            return 'border-l-4 border-l-green-500'
        default:
            return ''
    }
})

/**
 * Format waktu tunggu ke string readable
 */
const waitingTimeText = computed(() => {
    const minutes = waitingMinutes.value
    if (minutes < 1) return 'Baru saja'
    if (minutes < 60) return `${minutes} menit`
    const hours = Math.floor(minutes / 60)
    const remainingMinutes = minutes % 60
    if (remainingMinutes === 0) return `${hours} jam`
    return `${hours} jam ${remainingMinutes} menit`
})

/**
 * Get next status berdasarkan current status
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
            return { status: 'delivered', label: 'Selesai', icon: CircleCheck }
        default:
            return null
    }
})

/**
 * Error message state untuk menampilkan error ke user
 */
const errorMessage = ref<string | null>(null)

/**
 * Confirmation modal state untuk mencegah aksi tidak sengaja
 */
const showConfirmModal = ref(false)

/**
 * Password confirmation dialog state
 */
const showPasswordDialog = ref(false)

/**
 * Success dialog state
 */
const showSuccessDialog = ref(false)
const successStatus = ref('')

/**
 * Open confirmation modal sebelum update status (tahap 1)
 * untuk mencegah aksi tidak sengaja oleh admin
 */
function openConfirmModal(): void {
    if (!nextStatus.value || isUpdating.value) return
    haptic.medium()
    showConfirmModal.value = true
}

/**
 * Close confirmation modal tanpa melakukan aksi
 */
function closeConfirmModal(): void {
    showConfirmModal.value = false
}

/**
 * Lanjutkan ke password verification setelah konfirmasi (tahap 2)
 */
function proceedToPasswordVerification(): void {
    showConfirmModal.value = false
    showPasswordDialog.value = true
}

/**
 * Eksekusi update status setelah password diverifikasi (tahap 3)
 * Setelah sukses akan menampilkan success dialog dengan opsi WhatsApp
 */
function executeStatusUpdate(): void {
    if (!nextStatus.value || isUpdating.value) return

    haptic.heavy()
    isUpdating.value = true
    errorMessage.value = null
    showPasswordDialog.value = false

    // Store target status untuk success dialog
    const targetStatus = nextStatus.value.status

    router.patch(
        `/admin/orders/${props.order.id}/status`,
        { status: targetStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Set success state dan tampilkan success dialog
                successStatus.value = targetStatus
                showSuccessDialog.value = true
                emit('statusUpdated', props.order.id, targetStatus)
            },
            onError: (errors) => {
                haptic.error()
                errorMessage.value = errors.status || 'Gagal mengubah status pesanan'
                setTimeout(() => {
                    errorMessage.value = null
                }, 3000)
            },
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Computed untuk mendapatkan WhatsApp URL berdasarkan successStatus
 * URL dengan template message di-generate oleh backend (Order::getWhatsAppToCustomerUrl)
 * yang menggunakan customizable templates dari StoreSettingService
 */
const successWhatsAppUrl = computed(() => {
    if (!successStatus.value) return ''

    // Ambil URL dari props yang sudah di-generate oleh backend dengan template dari settings
    return props.whatsappUrls?.[successStatus.value as keyof WhatsAppUrls] ?? ''
})

/**
 * Handle kirim WhatsApp dari success dialog
 */
function handleSuccessWhatsApp(): void {
    haptic.medium()
    if (successWhatsAppUrl.value) {
        window.open(successWhatsAppUrl.value, '_blank')
    }
}

/**
 * Handle tutup success dialog
 */
function handleSuccessDialogClose(): void {
    showSuccessDialog.value = false
    successStatus.value = ''
}

/**
 * Get status label dalam Bahasa Indonesia untuk konfirmasi modal
 */
function getStatusLabel(status: string): string {
    const labels: Record<string, string> = {
        confirmed: 'Dikonfirmasi',
        preparing: 'Diproses',
        ready: 'Siap Kirim',
        delivered: 'Selesai',
    }
    return labels[status] || status
}

/**
 * Open WhatsApp dengan nomor customer
 * menggunakan composable usePhoneFormat untuk format internasional
 */
function openWhatsApp(): void {
    openWhatsAppComposable(props.order.customer_phone)
}
</script>

<template>
    <Card
        :class="[
            'overflow-hidden transition-all duration-200',
            urgencyBorderClass,
            urgencyLevel === 'urgent' && 'animate-pulse-subtle ring-2 ring-red-200 dark:ring-red-900',
            urgencyLevel === 'warning' && 'ring-1 ring-amber-200 dark:ring-amber-900',
        ]"
    >
        <!-- Urgency Banner for Urgent Orders -->
        <div
            v-if="urgencyLevel === 'urgent'"
            class="flex items-center gap-2 bg-red-500 px-4 py-2 text-sm font-medium text-white"
        >
            <AlertCircle class="h-4 w-4" />
            Menunggu {{ waitingTimeText }} - Segera proses!
        </div>

        <CardContent class="p-4">
            <!-- Header Row: Order Number & Status -->
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <Link
                            :href="show(order.id).url"
                            class="font-mono text-sm font-semibold text-primary hover:underline"
                        >
                            {{ order.order_number }}
                        </Link>
                        <OrderStatusBadge :status="order.status" />
                    </div>
                    <h3 class="mt-1 text-base font-medium text-foreground truncate">
                        {{ order.customer_name }}
                    </h3>
                </div>

                <!-- Waiting Time Badge (untuk status aktif, non-urgent) -->
                <Badge
                    v-if="['pending', 'confirmed', 'preparing'].includes(order.status) && urgencyLevel !== 'urgent'"
                    :variant="urgencyLevel === 'warning' ? 'secondary' : 'outline'"
                    class="shrink-0 gap-1"
                >
                    <Clock class="h-3 w-3" />
                    {{ waitingTimeText }}
                </Badge>
            </div>

            <!-- Order Info Row -->
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                <span class="flex items-center gap-1">
                    <Package class="h-4 w-4" />
                    {{ order.items.length }} item
                </span>
                <button
                    class="flex items-center gap-1 hover:text-primary transition-colors"
                    @click="openWhatsApp"
                >
                    <Phone class="h-4 w-4" />
                    {{ order.customer_phone }}
                </button>
            </div>

            <!-- Items Preview -->
            <div class="mt-3 text-sm text-muted-foreground">
                <p class="truncate">
                    {{ order.items.map(item => `${item.product_name} (${item.quantity})`).join(', ') }}
                </p>
            </div>

            <!-- Error Message -->
            <div
                v-if="errorMessage"
                class="mt-3 rounded-lg bg-red-50 p-2 text-sm text-red-600 dark:bg-red-950 dark:text-red-400"
            >
                {{ errorMessage }}
            </div>

            <!-- Footer Row: Total & Actions - Redesigned for better mobile UX -->
            <div class="mt-4 flex flex-col gap-3">
                <!-- Price Row -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Total Pesanan</span>
                    <PriceDisplay
                        :price="order.total"
                        size="lg"
                        class="font-bold text-primary"
                    />
                </div>

                <!-- Action Buttons Row - larger touch targets -->
                <div class="flex items-center gap-2">
                    <!-- WhatsApp Button - always show label -->
                    <Button
                        variant="outline"
                        class="h-11 flex-1 gap-2 text-green-600 hover:bg-green-50 hover:text-green-700 dark:text-green-400 dark:hover:bg-green-950"
                        @click="openWhatsApp"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span class="text-sm">WhatsApp</span>
                    </Button>

                    <!-- View Detail Button -->
                    <Link :href="show(order.id).url" class="flex-1">
                        <Button
                            variant="outline"
                            class="h-11 w-full gap-2"
                        >
                            <Eye class="h-4 w-4" />
                            <span class="text-sm">Detail</span>
                        </Button>
                    </Link>
                </div>

                <!-- Quick Action Button - Full width, prominent -->
                <Button
                    v-if="nextStatus"
                    class="h-12 w-full gap-2 rounded-xl text-base font-semibold"
                    :disabled="isUpdating"
                    @click="openConfirmModal"
                >
                    <Loader2 v-if="isUpdating" class="h-5 w-5 animate-spin" />
                    <component :is="nextStatus.icon" v-else class="h-5 w-5" />
                    {{ nextStatus.label }}
                </Button>
            </div>
        </CardContent>
    </Card>

    <!-- Step 1: Confirmation Modal untuk mencegah aksi tidak sengaja -->
    <Dialog v-model:open="showConfirmModal">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <AlertCircle class="h-5 w-5 text-amber-500" />
                    Konfirmasi Perubahan Status
                </DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin mengubah status pesanan
                    <span class="font-semibold text-foreground">{{ order.order_number }}</span>
                    menjadi
                    <span class="font-semibold text-primary">{{ nextStatus ? getStatusLabel(nextStatus.status) : '' }}</span>?
                </DialogDescription>
            </DialogHeader>

            <!-- Order Summary untuk konteks -->
            <div class="rounded-lg border bg-muted/50 p-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Customer:</span>
                    <span class="font-medium">{{ order.customer_name }}</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Total:</span>
                    <PriceDisplay :price="order.total" size="sm" />
                </div>
                <div class="mt-1 flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Items:</span>
                    <span>{{ order.items.length }} item</span>
                </div>
            </div>

            <DialogFooter class="flex-col gap-2 sm:flex-row">
                <Button
                    variant="outline"
                    class="w-full sm:w-auto"
                    @click="closeConfirmModal"
                >
                    Batal
                </Button>
                <Button
                    class="w-full sm:w-auto"
                    @click="proceedToPasswordVerification"
                >
                    <component :is="nextStatus?.icon" class="mr-2 h-4 w-4" />
                    Ya, Lanjutkan
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Step 2: Password Confirmation Dialog -->
    <PasswordConfirmDialog
        v-model:open="showPasswordDialog"
        title="Verifikasi Password"
        :description="`Masukkan password untuk mengubah status pesanan ${order.order_number} menjadi ${nextStatus ? getStatusLabel(nextStatus.status) : ''}.`"
        confirm-label="Update Status"
        :loading="isUpdating"
        @confirm="executeStatusUpdate"
    />

    <!-- Step 3: Success Dialog dengan opsi WhatsApp -->
    <StatusUpdateSuccessDialog
        v-model:open="showSuccessDialog"
        :new-status="successStatus"
        :new-status-label="getStatusLabel(successStatus)"
        :order-number="order.order_number"
        :customer-name="order.customer_name"
        :whatsapp-url="successWhatsAppUrl"
        @close="handleSuccessDialogClose"
        @send-whats-app="handleSuccessWhatsApp"
    />
</template>

<style scoped>
/* Subtle pulse animation untuk urgent orders */
@keyframes pulse-subtle {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.9;
    }
}

.animate-pulse-subtle {
    animation: pulse-subtle 2s ease-in-out infinite;
}
</style>


