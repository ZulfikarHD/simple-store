/**
 * useOrderNotifications Composable
 * Mengelola browser notifications untuk pesanan baru
 * dengan polling dan permission management
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * State untuk notification permission
 */
const notificationPermission = ref<NotificationPermission>('default')
const isSupported = ref(false)

/**
 * Composable untuk mengelola order notifications
 */
export function useOrderNotifications() {
    const page = usePage()

    /**
     * Cek apakah browser support notifications
     */
    function checkSupport(): boolean {
        isSupported.value = 'Notification' in window
        return isSupported.value
    }

    /**
     * Request permission untuk browser notifications
     */
    async function requestPermission(): Promise<boolean> {
        if (!checkSupport()) {
            return false
        }

        try {
            const permission = await Notification.requestPermission()
            notificationPermission.value = permission
            return permission === 'granted'
        } catch {
            return false
        }
    }

    /**
     * Menampilkan browser notification untuk pesanan baru
     */
    function showNewOrderNotification(count: number): void {
        if (!isSupported.value || notificationPermission.value !== 'granted') {
            return
        }

        const notification = new Notification('Pesanan Baru!', {
            body: `Ada ${count} pesanan baru yang perlu diproses.`,
            icon: '/favicon.ico',
            tag: 'new-order',
            requireInteraction: true,
        })

        notification.onclick = () => {
            window.focus()
            notification.close()
        }

        // Auto close after 10 seconds
        setTimeout(() => {
            notification.close()
        }, 10000)
    }

    /**
     * Watch untuk perubahan pending orders count
     * dan trigger notification jika ada pesanan baru
     */
    function watchPendingOrders(): void {
        watch(
            () => (page.props as { pending_orders_count?: number }).pending_orders_count,
            (newCount, oldCount) => {
                if (
                    newCount !== undefined &&
                    oldCount !== undefined &&
                    newCount > oldCount &&
                    notificationPermission.value === 'granted'
                ) {
                    const newOrders = newCount - oldCount
                    showNewOrderNotification(newOrders)
                }
            }
        )
    }

    /**
     * Initialize notification permission
     */
    onMounted(() => {
        checkSupport()
        if (isSupported.value) {
            notificationPermission.value = Notification.permission
        }
    })

    return {
        isSupported,
        notificationPermission,
        requestPermission,
        showNewOrderNotification,
        watchPendingOrders,
    }
}

