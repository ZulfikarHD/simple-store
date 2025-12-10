<script setup lang="ts">
/**
 * OrderTrackingCard Component
 * Compact order tracking card untuk menampilkan pesanan aktif
 * dengan iOS-like design, haptic feedback, dan progress visualization
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { Badge } from '@/components/ui/badge'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    Clock,
    CheckCircle,
    ChefHat,
    Package,
    ChevronRight,
} from 'lucide-vue-next'

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

interface Props {
    order: {
        id: number
        order_number: string
        status: OrderStatus
        total: number
        items_count: number
        items_preview: string
        created_at: string
        created_at_human: string
    }
    index?: number
}

const props = withDefaults(defineProps<Props>(), {
    index: 0,
})

const haptic = useHapticFeedback()
const isPressed = ref(false)

/**
 * Animation delay berdasarkan index
 */
const animationDelay = computed(() => props.index * 0.08)

/**
 * Format harga ke format Rupiah Indonesia
 */
const formattedTotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(props.order.total)
})

/**
 * Konfigurasi status untuk icon dan warna
 */
const statusConfig = computed(() => {
    const configs: Record<OrderStatus, { icon: typeof Clock; label: string; color: string; bgColor: string; progress: number }> = {
        pending: {
            icon: Clock,
            label: 'Menunggu',
            color: 'text-yellow-600 dark:text-yellow-400',
            bgColor: 'bg-yellow-100 dark:bg-yellow-900/30',
            progress: 25,
        },
        confirmed: {
            icon: CheckCircle,
            label: 'Dikonfirmasi',
            color: 'text-blue-600 dark:text-blue-400',
            bgColor: 'bg-blue-100 dark:bg-blue-900/30',
            progress: 50,
        },
        preparing: {
            icon: ChefHat,
            label: 'Diproses',
            color: 'text-orange-600 dark:text-orange-400',
            bgColor: 'bg-orange-100 dark:bg-orange-900/30',
            progress: 75,
        },
        ready: {
            icon: Package,
            label: 'Siap',
            color: 'text-green-600 dark:text-green-400',
            bgColor: 'bg-green-100 dark:bg-green-900/30',
            progress: 100,
        },
        delivered: {
            icon: CheckCircle,
            label: 'Selesai',
            color: 'text-green-600 dark:text-green-400',
            bgColor: 'bg-green-100 dark:bg-green-900/30',
            progress: 100,
        },
        cancelled: {
            icon: Clock,
            label: 'Dibatalkan',
            color: 'text-red-600 dark:text-red-400',
            bgColor: 'bg-red-100 dark:bg-red-900/30',
            progress: 0,
        },
    }
    return configs[props.order.status] ?? configs.pending
})

/**
 * Handle press events untuk iOS-like feedback
 */
function handlePressStart() {
    isPressed.value = true
    haptic.light()
}

function handlePressEnd() {
    isPressed.value = false
}

const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
const springTransition = { type: 'spring' as const, ...springPresets.ios }
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 15, scale: 0.97 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ ...springTransition, delay: animationDelay }"
    >
        <Motion
            :animate="{ scale: isPressed ? 0.97 : 1 }"
            :transition="snappyTransition"
        >
            <Link
                :href="`/account/orders/${order.id}`"
                class="group block rounded-2xl border border-brand-blue-100 bg-white/80 p-3 shadow-sm backdrop-blur transition-all hover:border-brand-blue-200 hover:shadow-md dark:border-brand-blue-800/30 dark:bg-background/80 dark:hover:border-brand-blue-700/50"
                @mousedown="handlePressStart"
                @mouseup="handlePressEnd"
                @mouseleave="handlePressEnd"
                @touchstart.passive="handlePressStart"
                @touchend="handlePressEnd"
                @touchcancel="handlePressEnd"
            >
                <!-- Header: Status & Order Number -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div :class="['flex h-8 w-8 items-center justify-center rounded-full', statusConfig.bgColor]">
                            <component :is="statusConfig.icon" :class="['h-4 w-4', statusConfig.color]" />
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">
                                {{ order.order_number }}
                            </p>
                            <Badge
                                variant="outline"
                                :class="['text-[10px] font-medium', statusConfig.color, 'border-current/20']"
                            >
                                {{ statusConfig.label }}
                            </Badge>
                        </div>
                    </div>
                    <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </div>

                <!-- Progress Bar -->
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-muted">
                    <Motion
                        :initial="{ width: 0 }"
                        :animate="{ width: `${statusConfig.progress}%` }"
                        :transition="{ ...springTransition, delay: animationDelay + 0.2 }"
                        class="h-full rounded-full bg-gradient-to-r from-brand-blue-500 to-brand-gold-500"
                    />
                </div>

                <!-- Footer: Items & Total -->
                <div class="mt-2 flex items-center justify-between">
                    <p class="truncate text-xs text-muted-foreground">
                        {{ order.items_count }} item · {{ order.items_preview }}
                    </p>
                    <p class="shrink-0 text-sm font-semibold text-foreground">
                        {{ formattedTotal }}
                    </p>
                </div>
            </Link>
        </Motion>
    </Motion>
</template>

