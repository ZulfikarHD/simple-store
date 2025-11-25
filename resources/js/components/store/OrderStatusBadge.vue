<script setup lang="ts">
/**
 * OrderStatusBadge Component
 * Menampilkan status pesanan dengan warna yang sesuai
 * berdasarkan status: pending, confirmed, preparing, ready, delivered, cancelled
 */
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

const props = defineProps<{
    status: OrderStatus
}>()

/**
 * Mapping status ke label dalam Bahasa Indonesia
 */
const statusLabel = computed((): string => {
    const labels: Record<OrderStatus, string> = {
        pending: 'Menunggu',
        confirmed: 'Dikonfirmasi',
        preparing: 'Diproses',
        ready: 'Siap',
        delivered: 'Selesai',
        cancelled: 'Dibatalkan',
    }
    return labels[props.status] ?? 'Unknown'
})

/**
 * Mapping status ke warna badge
 * menggunakan variant dan custom classes
 */
const statusClasses = computed((): string => {
    const classes: Record<OrderStatus, string> = {
        pending: 'border-yellow-200 bg-yellow-50 text-yellow-700 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-400',
        confirmed: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-400',
        preparing: 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-800 dark:bg-orange-950 dark:text-orange-400',
        ready: 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-400',
        delivered: 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-400',
        cancelled: 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-400',
    }
    return classes[props.status] ?? ''
})

/**
 * Icon untuk setiap status
 */
const statusIcon = computed((): string => {
    const icons: Record<OrderStatus, string> = {
        pending: '🕐',
        confirmed: '✓',
        preparing: '👨‍🍳',
        ready: '✅',
        delivered: '🎉',
        cancelled: '✕',
    }
    return icons[props.status] ?? '•'
})
</script>

<template>
    <Badge variant="outline" :class="statusClasses">
        <span class="mr-1">{{ statusIcon }}</span>
        {{ statusLabel }}
    </Badge>
</template>

