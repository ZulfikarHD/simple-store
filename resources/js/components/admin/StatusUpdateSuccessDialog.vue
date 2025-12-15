<script setup lang="ts">
/**
 * StatusUpdateSuccessDialog Component
 * Dialog sukses setelah update status pesanan dengan opsi kirim WhatsApp
 * menggunakan iOS-style design dengan animasi dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Motion, AnimatePresence } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'
import {
    CheckCircle2,
    MessageSquare,
    X,
} from 'lucide-vue-next'

interface Props {
    /** State untuk membuka/menutup dialog */
    open: boolean
    /** Nama status yang baru */
    newStatus: string
    /** Label status yang baru (dalam Bahasa Indonesia) */
    newStatusLabel: string
    /** Nomor pesanan untuk konteks */
    orderNumber: string
    /** Nama customer */
    customerName: string
    /** URL WhatsApp dengan template message */
    whatsappUrl?: string
}

const props = withDefaults(defineProps<Props>(), {
    whatsappUrl: '',
})

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'close'): void
    (e: 'sendWhatsApp'): void
}>()

const haptic = useHapticFeedback()

/**
 * Computed untuk mendapatkan warna berdasarkan status
 */
const statusColor = computed(() => {
    switch (props.newStatus) {
        case 'confirmed':
            return {
                bg: 'bg-blue-100 dark:bg-blue-900/40',
                text: 'text-blue-600 dark:text-blue-400',
                ring: 'ring-blue-500/20',
            }
        case 'preparing':
            return {
                bg: 'bg-purple-100 dark:bg-purple-900/40',
                text: 'text-purple-600 dark:text-purple-400',
                ring: 'ring-purple-500/20',
            }
        case 'ready':
            return {
                bg: 'bg-cyan-100 dark:bg-cyan-900/40',
                text: 'text-cyan-600 dark:text-cyan-400',
                ring: 'ring-cyan-500/20',
            }
        case 'delivered':
            return {
                bg: 'bg-green-100 dark:bg-green-900/40',
                text: 'text-green-600 dark:text-green-400',
                ring: 'ring-green-500/20',
            }
        case 'cancelled':
            return {
                bg: 'bg-red-100 dark:bg-red-900/40',
                text: 'text-red-600 dark:text-red-400',
                ring: 'ring-red-500/20',
            }
        default:
            return {
                bg: 'bg-green-100 dark:bg-green-900/40',
                text: 'text-green-600 dark:text-green-400',
                ring: 'ring-green-500/20',
            }
    }
})

/**
 * Computed untuk pesan sukses berdasarkan status
 */
const successMessage = computed(() => {
    switch (props.newStatus) {
        case 'confirmed':
            return 'Pesanan telah dikonfirmasi dan siap diproses.'
        case 'preparing':
            return 'Pesanan sedang dalam proses pembuatan.'
        case 'ready':
            return 'Pesanan siap untuk dikirim ke customer.'
        case 'delivered':
            return 'Pesanan telah berhasil dikirim ke customer.'
        case 'cancelled':
            return 'Pesanan telah dibatalkan.'
        default:
            return 'Status pesanan berhasil diperbarui.'
    }
})

/**
 * Watch untuk trigger haptic saat dialog dibuka
 */
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        haptic.success()
    }
})

/**
 * Handle close dialog
 */
function handleClose(): void {
    haptic.light()
    emit('close')
    emit('update:open', false)
}

/**
 * Handle kirim WhatsApp
 */
function handleSendWhatsApp(): void {
    haptic.medium()
    emit('sendWhatsApp')
    // Jangan tutup dialog agar user bisa melihat konfirmasi
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader class="text-center">
                <!-- Success Icon dengan animasi -->
                <Motion
                    :initial="{ scale: 0, rotate: -180 }"
                    :animate="{ scale: 1, rotate: 0 }"
                    :transition="springPresets.bouncy"
                    class="mx-auto mb-4"
                >
                    <div
                        :class="[
                            'flex h-16 w-16 items-center justify-center rounded-full ring-4',
                            statusColor.bg,
                            statusColor.ring,
                        ]"
                    >
                        <CheckCircle2 :class="['h-8 w-8', statusColor.text]" />
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: 0.1 }"
                >
                    <DialogTitle class="text-xl">
                        Status Berhasil Diperbarui!
                    </DialogTitle>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: 0.15 }"
                >
                    <DialogDescription class="text-center">
                        {{ successMessage }}
                    </DialogDescription>
                </Motion>
            </DialogHeader>

            <!-- Order Info Summary -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springPresets.ios, delay: 0.2 }"
                class="rounded-xl border bg-muted/30 p-4"
            >
                <div class="flex flex-col gap-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Nomor Pesanan</span>
                        <span class="font-mono font-semibold">{{ orderNumber }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Customer</span>
                        <span class="font-medium">{{ customerName }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Status Baru</span>
                        <span
                            :class="[
                                'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                statusColor.bg,
                                statusColor.text,
                            ]"
                        >
                            {{ newStatusLabel }}
                        </span>
                    </div>
                </div>
            </Motion>

            <DialogFooter class="flex-col gap-2 pt-2 sm:flex-col">
                <!-- WhatsApp Button - Primary action -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: 0.25 }"
                    class="w-full"
                >
                    <Button
                        v-if="whatsappUrl && newStatus !== 'cancelled'"
                        class="ios-button w-full gap-2 bg-green-600 text-white hover:bg-green-700"
                        @click="handleSendWhatsApp"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Kirim via WhatsApp
                    </Button>
                </Motion>

                <!-- Close Button -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: 0.3 }"
                    class="w-full"
                >
                    <Button
                        variant="outline"
                        class="ios-button w-full"
                        @click="handleClose"
                    >
                        Tutup
                    </Button>
                </Motion>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>


