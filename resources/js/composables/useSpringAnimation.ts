/**
 * useSpringAnimation Composable
 * Menyediakan spring physics animations untuk iOS-like interactions
 * menggunakan motion-v library dengan konfigurasi presets yang mudah digunakan
 *
 * @author Zulfikar Hidayatullah
 */
import type { Ref } from 'vue'
import { computed, ref, watch } from 'vue'

/**
 * Preset konfigurasi spring untuk berbagai use case
 * mengikuti karakteristik animasi iOS native
 */
export const springPresets = {
    /**
     * Default spring - balanced untuk general use
     */
    default: {
        type: 'spring' as const,
        stiffness: 300,
        damping: 30,
        mass: 1,
    },
    /**
     * Bouncy spring - untuk elemen yang membutuhkan feedback playful
     */
    bouncy: {
        type: 'spring' as const,
        stiffness: 400,
        damping: 15,
        mass: 0.8,
    },
    /**
     * Snappy spring - respons cepat untuk micro-interactions
     */
    snappy: {
        type: 'spring' as const,
        stiffness: 500,
        damping: 40,
        mass: 0.5,
    },
    /**
     * Smooth spring - untuk transisi yang halus dan elegant
     */
    smooth: {
        type: 'spring' as const,
        stiffness: 200,
        damping: 25,
        mass: 1.2,
    },
    /**
     * Stiff spring - minimal bounce, lebih precise
     */
    stiff: {
        type: 'spring' as const,
        stiffness: 600,
        damping: 50,
        mass: 0.5,
    },
    /**
     * Gentle spring - slow dan smooth untuk large elements
     */
    gentle: {
        type: 'spring' as const,
        stiffness: 150,
        damping: 20,
        mass: 1.5,
    },
    /**
     * iOS - Native iOS-like spring
     */
    ios: {
        type: 'spring' as const,
        stiffness: 300,
        damping: 25,
        mass: 1,
    },
} as const

export type SpringPreset = keyof typeof springPresets

/**
 * Interface untuk konfigurasi animasi
 */
export interface AnimationConfig {
    scale?: number
    opacity?: number
    x?: number
    y?: number
    rotate?: number
    delay?: number
}

/**
 * Interface untuk state animasi press/tap
 */
export interface PressState {
    isPressed: Ref<boolean>
    pressHandlers: {
        onMousedown: () => void
        onMouseup: () => void
        onMouseleave: () => void
        onTouchstart: () => void
        onTouchend: () => void
    }
}

/**
 * Get spring transition untuk motion-v
 * @param preset - Spring preset name
 * @param delay - Optional delay dalam seconds
 */
export function getSpringTransition(
    preset: SpringPreset = 'default',
    delay: number = 0
) {
    return {
        ...springPresets[preset],
        delay,
    }
}

/**
 * Composable untuk iOS-like press effect
 * Memberikan visual feedback saat element di-tap/click
 *
 * @param options - Konfigurasi press effect
 */
export function usePressAnimation(options: {
    scale?: number
    preset?: SpringPreset
} = {}) {
    const { scale = 0.97, preset = 'snappy' } = options
    const isPressed = ref(false)

    const pressHandlers = {
        onMousedown: () => {
            isPressed.value = true
        },
        onMouseup: () => {
            isPressed.value = false
        },
        onMouseleave: () => {
            isPressed.value = false
        },
        onTouchstart: () => {
            isPressed.value = true
        },
        onTouchend: () => {
            isPressed.value = false
        },
    }

    const pressScale = computed(() => isPressed.value ? scale : 1)
    const pressTransition = springPresets[preset]

    return {
        isPressed,
        pressHandlers,
        pressScale,
        pressTransition,
    }
}

/**
 * Composable untuk staggered animations
 * Menampilkan list items dengan delay yang berurutan
 *
 * @param itemCount - Jumlah items yang akan dianimasi
 * @param options - Konfigurasi stagger
 */
export function useStaggerAnimation(
    options: {
        baseDelay?: number
        staggerDelay?: number
        preset?: SpringPreset
    } = {}
) {
    const { baseDelay = 0, staggerDelay = 50, preset = 'smooth' } = options

    /**
     * Generate delay untuk item berdasarkan index (dalam seconds untuk motion-v)
     */
    function getDelay(index: number): number {
        return (baseDelay + index * staggerDelay) / 1000
    }

    /**
     * Generate transition untuk item
     */
    function getItemTransition(index: number) {
        return {
                    ...springPresets[preset],
                    delay: getDelay(index),
        }
    }

    /**
     * Generate CSS animation-delay style
     */
    function getDelayStyle(index: number): { animationDelay: string } {
        return {
            animationDelay: `${baseDelay + index * staggerDelay}ms`,
        }
    }

    return {
        getDelay,
        getItemTransition,
        getDelayStyle,
    }
}

/**
 * Composable untuk page transition animations
 * Menggunakan spring physics untuk transisi halaman
 *
 * @param direction - Arah transisi (forward/backward)
 */
export function usePageTransition(direction: Ref<'forward' | 'backward'> | 'forward' | 'backward' = 'forward') {
    const currentDirection = computed(() =>
        typeof direction === 'string' ? direction : direction.value
    )

    const enterAnimation = computed(() => ({
        initial: {
            opacity: 0,
            x: currentDirection.value === 'forward' ? 30 : -30,
        },
        animate: {
            opacity: 1,
            x: 0,
        },
        transition: springPresets.smooth,
    }))

    const leaveAnimation = computed(() => ({
        exit: {
            opacity: 0,
            x: currentDirection.value === 'forward' ? -30 : 30,
        },
        transition: springPresets.snappy,
    }))

    return {
        enterAnimation,
        leaveAnimation,
        currentDirection,
    }
}

/**
 * Composable untuk bounce animation (badges, notifications)
 *
 * @param trigger - Reactive value yang trigger animasi
 */
export function useBounceAnimation(trigger: Ref<boolean | number>) {
    const shouldBounce = ref(false)

    watch(trigger, (newVal, oldVal) => {
        if (newVal !== oldVal) {
            shouldBounce.value = true
            setTimeout(() => {
                shouldBounce.value = false
            }, 300)
        }
    })

    const bounceAnimation = computed(() => ({
        animate: shouldBounce.value
            ? { scale: [1, 1.2, 1] }
            : { scale: 1 },
        transition: springPresets.bouncy,
    }))

    return {
        shouldBounce,
        bounceAnimation,
    }
}

/**
 * Composable untuk shake animation (error states)
 */
export function useShakeAnimation() {
    const isShaking = ref(false)

    function shake() {
        isShaking.value = true
        setTimeout(() => {
            isShaking.value = false
        }, 500)
    }

    const shakeClass = computed(() => (isShaking.value ? 'animate-ios-shake' : ''))

    return {
        isShaking,
        shake,
        shakeClass,
    }
}
