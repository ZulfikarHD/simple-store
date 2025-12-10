<script setup lang="ts">
/**
 * NewOrderAlert Component
 * Banner alert untuk menampilkan pesanan baru yang perlu diproses
 * dengan polling otomatis dan quick actions untuk konfirmasi langsung
 * serta password confirmation untuk keamanan aksi sensitif
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import PasswordConfirmDialog from '@/components/admin/PasswordConfirmDialog.vue'
import {
    Bell,
    X,
    Eye,
    Check,
    Phone,
    Clock,
    Package,
    ChevronDown,
    ChevronUp,
    Loader2,
} from 'lucide-vue-next'

/**
 * Interface untuk pending order data dari API
 */
interface PendingOrder {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    total: number
    items_count: number
    created_at: string
    waiting_minutes: number
}

/**
 * State untuk pending orders dari polling
 */
const pendingOrders = ref<PendingOrder[]>([])
const totalPending = ref(0)
const isConfirming = ref<number | null>(null)
const isDismissed = ref(false)
const isExpanded = ref(false)
const lastSeenOrderId = ref<number | null>(null)
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null)

/**
 * State untuk password confirmation dialog
 */
const showPasswordDialog = ref(false)
const pendingConfirmOrderId = ref<number | null>(null)

const page = usePage()

/**
 * Computed untuk menampilkan alert (ada pesanan pending dan belum di-dismiss)
 */
const showAlert = computed(() => {
    return pendingOrders.value.length > 0 && !isDismissed.value
})

/**
 * Computed untuk latest order (yang ditampilkan di banner)
 */
const latestOrder = computed(() => pendingOrders.value[0] ?? null)

/**
 * Computed untuk cek apakah ada order baru sejak terakhir dilihat
 */

/**
 * Fetch pending orders dari API
 */
async function fetchPendingOrders(): Promise<void> {
    try {
        const response = await fetch('/admin/api/orders/pending', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })

        if (!response.ok) return

        const data = await response.json()
        const previousLatestId = pendingOrders.value[0]?.id ?? null

        pendingOrders.value = data.orders
        totalPending.value = data.total_pending

        // Jika ada order baru, reset dismiss state dan show alert
        if (data.orders.length > 0 && data.orders[0].id !== previousLatestId) {
            if (previousLatestId !== null) {
                isDismissed.value = false
            }
        }
    } catch (error) {
        console.error('Failed to fetch pending orders:', error)
    }
}

/**
 * Memulai proses konfirmasi order dengan membuka password dialog
 * untuk keamanan aksi sensitif
 */
function requestConfirmOrder(orderId: number): void {
    if (isConfirming.value !== null) return
    pendingConfirmOrderId.value = orderId
    showPasswordDialog.value = true
}

/**
 * Eksekusi konfirmasi order setelah password diverifikasi
 * menggunakan Inertia router untuk menghindari CSRF issues
 */
function executeConfirmOrder(): void {
    if (pendingConfirmOrderId.value === null) return

    const orderId = pendingConfirmOrderId.value
    isConfirming.value = orderId
    showPasswordDialog.value = false

    router.patch(
        `/admin/orders/${orderId}/status`,
        { status: 'confirmed' },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh pending orders
                fetchPendingOrders()
            },
            onError: (errors) => {
                console.error('Failed to confirm order:', errors)
            },
            onFinish: () => {
                isConfirming.value = null
                pendingConfirmOrderId.value = null
            },
        }
    )
}

/**
 * Handle cancel password dialog
 */
function handlePasswordDialogCancel(): void {
    showPasswordDialog.value = false
    pendingConfirmOrderId.value = null
}

/**
 * Navigate ke halaman detail order
 */
function viewOrderDetail(orderId: number): void {
    lastSeenOrderId.value = orderId
    router.visit(`/admin/orders/${orderId}`)
}

/**
 * Dismiss alert sementara (akan muncul lagi jika ada order baru)
 */
function dismissAlert(): void {
    isDismissed.value = true
    if (latestOrder.value) {
        lastSeenOrderId.value = latestOrder.value.id
    }
}

/**
 * Toggle expanded state untuk lihat semua pending orders
 */
function toggleExpand(): void {
    isExpanded.value = !isExpanded.value
}

/**
 * Open WhatsApp dengan nomor customer
 */
function openWhatsApp(phone: string): void {
    const cleanPhone = phone.replace(/\D/g, '')
    window.open(`https://wa.me/${cleanPhone}`, '_blank')
}

/**
 * Format waktu tunggu ke string yang readable
 */
function formatWaitingTime(minutes: number): string {
    if (minutes < 1) return 'Baru saja'
    if (minutes < 60) return `${minutes} menit`
    const hours = Math.floor(minutes / 60)
    const remainingMinutes = minutes % 60
    return `${hours} jam ${remainingMinutes} menit`
}

/**
 * Get urgency class berdasarkan waktu tunggu
 */
function getUrgencyClass(minutes: number): string {
    if (minutes >= 15) return 'border-red-500 bg-red-50 dark:bg-red-950/30'
    if (minutes >= 5) return 'border-amber-500 bg-amber-50 dark:bg-amber-950/30'
    return 'border-green-500 bg-green-50 dark:bg-green-950/30'
}

/**
 * Get urgency badge variant
 */
function getUrgencyBadge(minutes: number): { variant: 'default' | 'secondary' | 'destructive', label: string } {
    if (minutes >= 15) return { variant: 'destructive', label: 'Urgent' }
    if (minutes >= 5) return { variant: 'secondary', label: 'Menunggu' }
    return { variant: 'default', label: 'Baru' }
}

/**
 * Start polling untuk pending orders
 */
function startPolling(): void {
    // Initial fetch
    fetchPendingOrders()

    // Polling setiap 30 detik
    pollingInterval.value = setInterval(() => {
        fetchPendingOrders()
    }, 30000)
}

/**
 * Stop polling
 */
function stopPolling(): void {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value)
        pollingInterval.value = null
    }
}

onMounted(() => {
    startPolling()
})

onUnmounted(() => {
    stopPolling()
})

// Watch untuk pending_orders_count dari Inertia props
// untuk trigger immediate fetch jika count berubah
watch(
    () => (page.props as { pending_orders_count?: number }).pending_orders_count,
    (newCount, oldCount) => {
        if (newCount !== oldCount) {
            fetchPendingOrders()
        }
    }
)
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-full"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-full"
    >
        <div
            v-if="showAlert"
            class="fixed inset-x-0 top-0 z-50 sm:relative sm:mb-4"
        >
            <!-- Main Alert Banner -->
            <div
                :class="[
                    'border-b-2 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 sm:mx-4 sm:rounded-lg sm:border-2 sm:shadow-lg',
                    latestOrder ? getUrgencyClass(latestOrder.waiting_minutes) : 'border-primary',
                ]"
            >
                <div class="p-3 sm:p-4">
                    <!-- Header Row -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                <Bell class="h-4 w-4 text-primary" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    {{ totalPending }} Pesanan Menunggu
                                </h3>
                                <p class="text-xs text-muted-foreground">
                                    Perlu segera diproses
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <Button
                                v-if="pendingOrders.length > 1"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                @click="toggleExpand"
                            >
                                <ChevronUp v-if="isExpanded" class="h-4 w-4" />
                                <ChevronDown v-else class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                @click="dismissAlert"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Latest Order Card -->
                    <div
                        v-if="latestOrder"
                        class="mt-3 rounded-lg border bg-card p-3"
                    >
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-semibold">
                                        {{ latestOrder.order_number }}
                                    </span>
                                    <Badge
                                        :variant="getUrgencyBadge(latestOrder.waiting_minutes).variant"
                                        class="text-xs"
                                    >
                                        {{ getUrgencyBadge(latestOrder.waiting_minutes).label }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-foreground">
                                    {{ latestOrder.customer_name }}
                                </p>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Package class="h-3 w-3" />
                                        {{ latestOrder.items_count }} item
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" />
                                        {{ formatWaitingTime(latestOrder.waiting_minutes) }}
                                    </span>
                                    <PriceDisplay :price="latestOrder.total" size="sm" class="font-semibold text-primary" />
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 pt-2 sm:pt-0">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 gap-1.5"
                                    @click="openWhatsApp(latestOrder.customer_phone)"
                                >
                                    <Phone class="h-3.5 w-3.5" />
                                    <span class="hidden sm:inline">WhatsApp</span>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 gap-1.5"
                                    @click="viewOrderDetail(latestOrder.id)"
                                >
                                    <Eye class="h-3.5 w-3.5" />
                                    <span class="hidden sm:inline">Detail</span>
                                </Button>
                                <Button
                                    size="sm"
                                    class="h-9 gap-1.5"
                                    :disabled="isConfirming === latestOrder.id"
                                    @click="requestConfirmOrder(latestOrder.id)"
                                >
                                    <Loader2 v-if="isConfirming === latestOrder.id" class="h-3.5 w-3.5 animate-spin" />
                                    <Check v-else class="h-3.5 w-3.5" />
                                    Konfirmasi
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Expanded Orders List -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 max-h-96"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div
                            v-if="isExpanded && pendingOrders.length > 1"
                            class="mt-2 space-y-2 overflow-hidden"
                        >
                            <div
                                v-for="order in pendingOrders.slice(1)"
                                :key="order.id"
                                class="rounded-lg border bg-card/50 p-3"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-medium truncate">
                                                {{ order.order_number }}
                                            </span>
                                            <Badge
                                                :variant="getUrgencyBadge(order.waiting_minutes).variant"
                                                class="text-xs shrink-0"
                                            >
                                                {{ formatWaitingTime(order.waiting_minutes) }}
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground truncate">
                                            {{ order.customer_name }} • {{ order.items_count }} item
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="viewOrderDetail(order.id)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            size="icon"
                                            class="h-8 w-8"
                                            :disabled="isConfirming === order.id"
                                            @click="requestConfirmOrder(order.id)"
                                        >
                                            <Loader2 v-if="isConfirming === order.id" class="h-4 w-4 animate-spin" />
                                            <Check v-else class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Password Confirmation Dialog -->
    <PasswordConfirmDialog
        v-model:open="showPasswordDialog"
        title="Konfirmasi Pesanan"
        description="Masukkan password Anda untuk mengkonfirmasi pesanan ini."
        confirm-label="Konfirmasi Pesanan"
        @confirm="executeConfirmOrder"
        @cancel="handlePasswordDialogCancel"
    />
</template>
