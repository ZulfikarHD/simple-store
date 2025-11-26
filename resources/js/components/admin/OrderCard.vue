<script setup lang="ts">
/**
 * OrderCard Component
 * Card untuk menampilkan pesanan di mobile view dengan quick actions
 * dan urgency indicators berdasarkan waktu tunggu
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { OrderStatusBadge } from '@/components/store'
import { show } from '@/routes/admin/orders'
import {
    Phone,
    Eye,
    Check,
    Package,
    Clock,
    ChefHat,
    Truck,
    CircleCheck,
    Loader2,
    MapPin,
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

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address?: string
    total: number
    status: string
    items: OrderItem[]
    created_at: string
    waiting_minutes?: number
}

interface Props {
    order: Order
    statuses: Record<string, string>
}

const props = defineProps<Props>()

const emit = defineEmits<{
    statusUpdated: [orderId: number, newStatus: string]
}>()

/**
 * Loading state untuk quick action
 */
const isUpdating = ref(false)

/**
 * Computed untuk waktu tunggu dalam menit
 */
const waitingMinutes = computed(() => {
    if (props.order.waiting_minutes !== undefined) {
        return props.order.waiting_minutes
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
 * Quick status update handler
 */
async function quickUpdateStatus(): Promise<void> {
    if (!nextStatus.value || isUpdating.value) return

    isUpdating.value = true

    try {
        const response = await fetch(`/admin/api/orders/${props.order.id}/quick-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ status: nextStatus.value.status }),
        })

        if (response.ok) {
            emit('statusUpdated', props.order.id, nextStatus.value.status)
            // Reload halaman untuk refresh data
            router.reload({ only: ['orders', 'pending_orders_count'] })
        }
    } catch (error) {
        console.error('Failed to update order status:', error)
    } finally {
        isUpdating.value = false
    }
}

/**
 * Open WhatsApp dengan nomor customer
 */
function openWhatsApp(): void {
    const cleanPhone = props.order.customer_phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
}

/**
 * Get status icon
 */
function getStatusIcon(status: string) {
    switch (status) {
        case 'pending':
            return Clock
        case 'confirmed':
            return Check
        case 'preparing':
            return ChefHat
        case 'ready':
            return Package
        case 'delivered':
            return Truck
        default:
            return Clock
    }
}
</script>

<template>
    <Card
        :class="[
            'overflow-hidden transition-all duration-200',
            urgencyBorderClass,
            urgencyLevel === 'urgent' && 'animate-pulse-subtle',
        ]"
    >
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

                <!-- Waiting Time Badge (untuk status aktif) -->
                <Badge
                    v-if="['pending', 'confirmed', 'preparing'].includes(order.status)"
                    :variant="urgencyLevel === 'urgent' ? 'destructive' : urgencyLevel === 'warning' ? 'secondary' : 'outline'"
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

            <!-- Footer Row: Total & Actions -->
            <div class="mt-4 flex items-center justify-between gap-3">
                <PriceDisplay
                    :price="order.total"
                    size="lg"
                    class="font-bold"
                />

                <div class="flex items-center gap-2">
                    <!-- WhatsApp Button -->
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 w-9 p-0 sm:h-9 sm:w-auto sm:px-3"
                        @click="openWhatsApp"
                    >
                        <Phone class="h-4 w-4" />
                        <span class="hidden sm:ml-2 sm:inline">WhatsApp</span>
                    </Button>

                    <!-- View Detail Button -->
                    <Link :href="show(order.id).url">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-9 w-9 p-0 sm:h-9 sm:w-auto sm:px-3"
                        >
                            <Eye class="h-4 w-4" />
                            <span class="hidden sm:ml-2 sm:inline">Detail</span>
                        </Button>
                    </Link>

                    <!-- Quick Action Button -->
                    <Button
                        v-if="nextStatus"
                        size="sm"
                        class="h-9 gap-1.5"
                        :disabled="isUpdating"
                        @click="quickUpdateStatus"
                    >
                        <Loader2 v-if="isUpdating" class="h-4 w-4 animate-spin" />
                        <component :is="nextStatus.icon" v-else class="h-4 w-4" />
                        <span class="hidden xs:inline">{{ nextStatus.label }}</span>
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
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


