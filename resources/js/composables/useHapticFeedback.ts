/**
 * useHapticFeedback Composable
 * Menyediakan haptic feedback untuk iOS-like tactile experience
 * menggunakan Vibration API dengan fallback yang graceful
 *
 * @author Zulfikar Hidayatullah
 */
import { computed, ref } from 'vue'

/**
 * Type untuk berbagai jenis haptic feedback
 * mengikuti pattern dari iOS UIFeedbackGenerator
 */
export type HapticType =
    | 'light'
    | 'medium'
    | 'heavy'
    | 'selection'
    | 'success'
    | 'warning'
    | 'error'

/**
 * Pattern vibration untuk setiap type haptic
 * dalam milliseconds - mengikuti karakteristik iOS
 */
const hapticPatterns: Record<HapticType, number | number[]> = {
    /**
     * Light impact - untuk subtle selections
     */
    light: 10,
    /**
     * Medium impact - untuk standard interactions
     */
    medium: 20,
    /**
     * Heavy impact - untuk significant actions
     */
    heavy: 30,
    /**
     * Selection feedback - untuk picker/selection changes
     */
    selection: 5,
    /**
     * Success notification - double pulse
     */
    success: [10, 30, 10],
    /**
     * Warning notification - single medium pulse
     */
    warning: [20, 50, 20],
    /**
     * Error notification - triple pulse pattern
     */
    error: [30, 30, 30, 30, 30],
}

/**
 * Cek apakah Vibration API tersedia
 */
function isVibrationSupported(): boolean {
    return typeof navigator !== 'undefined' && 'vibrate' in navigator
}

/**
 * Composable untuk haptic feedback
 * Memberikan tactile response pada interaksi user
 */
export function useHapticFeedback() {
    const isSupported = ref(isVibrationSupported())
    const isEnabled = ref(true)

    /**
     * Trigger haptic feedback dengan type tertentu
     *
     * @param type - Jenis haptic feedback
     */
    function trigger(type: HapticType = 'medium') {
        if (!isSupported.value || !isEnabled.value) {
            return
        }

        try {
            const pattern = hapticPatterns[type]
            navigator.vibrate(pattern)
        } catch {
            // Silently fail jika vibration gagal
            console.debug('Haptic feedback failed')
        }
    }

    /**
     * Light impact feedback
     * untuk subtle UI changes
     */
    function light() {
        trigger('light')
    }

    /**
     * Medium impact feedback
     * untuk standard button taps
     */
    function medium() {
        trigger('medium')
    }

    /**
     * Heavy impact feedback
     * untuk significant actions
     */
    function heavy() {
        trigger('heavy')
    }

    /**
     * Selection change feedback
     * untuk picker/scroll selections
     */
    function selection() {
        trigger('selection')
    }

    /**
     * Success notification feedback
     * untuk completed actions
     */
    function success() {
        trigger('success')
    }

    /**
     * Warning notification feedback
     * untuk attention-needed states
     */
    function warning() {
        trigger('warning')
    }

    /**
     * Error notification feedback
     * untuk failed actions
     */
    function error() {
        trigger('error')
    }

    /**
     * Stop any ongoing vibration
     */
    function stop() {
        if (isSupported.value) {
            navigator.vibrate(0)
        }
    }

    /**
     * Enable/disable haptic feedback
     */
    function setEnabled(enabled: boolean) {
        isEnabled.value = enabled
    }

    return {
        isSupported,
        isEnabled,
        trigger,
        light,
        medium,
        heavy,
        selection,
        success,
        warning,
        error,
        stop,
        setEnabled,
    }
}

/**
 * Composable untuk menggabungkan haptic dengan event handler
 * Memudahkan penambahan haptic pada click/tap events
 */
export function useHapticHandler() {
    const haptic = useHapticFeedback()

    /**
     * Wrap handler dengan haptic feedback
     *
     * @param handler - Original event handler
     * @param type - Jenis haptic feedback
     */
    function withHaptic<T extends (...args: unknown[]) => void>(
        handler: T,
        type: HapticType = 'medium'
    ): T {
        return ((...args: Parameters<T>) => {
            haptic.trigger(type)
            return handler(...args)
        }) as T
    }

    /**
     * Create click handler dengan light haptic
     */
    function onClickLight<T extends (...args: unknown[]) => void>(handler: T): T {
        return withHaptic(handler, 'light')
    }

    /**
     * Create click handler dengan medium haptic
     */
    function onClickMedium<T extends (...args: unknown[]) => void>(handler: T): T {
        return withHaptic(handler, 'medium')
    }

    /**
     * Create click handler dengan heavy haptic
     */
    function onClickHeavy<T extends (...args: unknown[]) => void>(handler: T): T {
        return withHaptic(handler, 'heavy')
    }

    return {
        ...haptic,
        withHaptic,
        onClickLight,
        onClickMedium,
        onClickHeavy,
    }
}

/**
 * Directive-style haptic untuk template usage
 * Usage: v-haptic atau v-haptic="'heavy'"
 */
export const hapticDirective = {
    mounted(el: HTMLElement, binding: { value?: HapticType }) {
        const haptic = useHapticFeedback()
        const type = binding.value || 'medium'

        el.addEventListener('touchstart', () => haptic.trigger(type), { passive: true })
        el.addEventListener('mousedown', () => haptic.trigger(type))
    },
}

