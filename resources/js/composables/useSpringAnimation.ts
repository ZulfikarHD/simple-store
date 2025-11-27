/**
 * useSpringAnimation Composable
 * Menyediakan spring physics animations untuk iOS-like interactions
 * dengan konfigurasi yang mudah digunakan dan performa optimal
 *
 * @author Zulfikar Hidayatullah
 */
import { useMotion } from '@vueuse/motion'
import type { MaybeRef, Ref } from 'vue'
import { computed, ref, unref, watch } from 'vue'

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
 * Composable untuk animasi spring pada element
 * dengan preset dan konfigurasi yang mudah
 *
 * @param target - Element ref yang akan dianimasi
 * @param preset - Preset spring yang digunakan
 */
export function useSpringAnimation<T extends HTMLElement>(
    target: MaybeRef<T | null>,
    preset: SpringPreset = 'default'
) {
    const springConfig = springPresets[preset]

    const motionInstance = useMotion(target, {
        initial: {
            scale: 1,
            opacity: 1,
            x: 0,
            y: 0,
        },
        enter: {
            scale: 1,
            opacity: 1,
            x: 0,
            y: 0,
            transition: springConfig,
        },
    })

    /**
     * Animasi scale dengan spring physics
     */
    function animateScale(scale: number) {
        motionInstance.apply({
            scale,
            transition: springConfig,
        })
    }

    /**
     * Animasi posisi dengan spring physics
     */
    function animatePosition(x: number, y: number) {
        motionInstance.apply({
            x,
            y,
            transition: springConfig,
        })
    }

    /**
     * Animasi opacity dengan spring physics
     */
    function animateOpacity(opacity: number) {
        motionInstance.apply({
            opacity,
            transition: springConfig,
        })
    }

    /**
     * Animasi custom dengan multiple properties
     */
    function animate(config: AnimationConfig) {
        const { delay, ...animationProps } = config
        motionInstance.apply({
            ...animationProps,
            transition: {
                ...springConfig,
                delay: delay ?? 0,
            },
        })
    }

    /**
     * Reset ke state awal
     */
    function reset() {
        motionInstance.apply({
            scale: 1,
            opacity: 1,
            x: 0,
            y: 0,
            transition: springConfig,
        })
    }

    return {
        motionInstance,
        animateScale,
        animatePosition,
        animateOpacity,
        animate,
        reset,
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

    const motionConfig = computed(() => ({
        initial: { scale: 1 },
        press: {
            scale: isPressed.value ? scale : 1,
            transition: springPresets[preset],
        },
    }))

    return {
        isPressed,
        pressHandlers,
        motionConfig,
        pressScale: scale,
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
    itemCount: MaybeRef<number>,
    options: {
        baseDelay?: number
        staggerDelay?: number
        preset?: SpringPreset
    } = {}
) {
    const { baseDelay = 0, staggerDelay = 50, preset = 'smooth' } = options

    /**
     * Generate delay untuk item berdasarkan index
     */
    function getDelay(index: number): number {
        return baseDelay + index * staggerDelay
    }

    /**
     * Generate motion config untuk item
     */
    function getItemConfig(index: number) {
        return {
            initial: {
                opacity: 0,
                y: 20,
            },
            enter: {
                opacity: 1,
                y: 0,
                transition: {
                    ...springPresets[preset],
                    delay: getDelay(index),
                },
            },
        }
    }

    /**
     * Generate CSS animation-delay style
     */
    function getDelayStyle(index: number): { animationDelay: string } {
        return {
            animationDelay: `${getDelay(index)}ms`,
        }
    }

    return {
        getDelay,
        getItemConfig,
        getDelayStyle,
    }
}

/**
 * Composable untuk page transition animations
 * Menggunakan spring physics untuk transisi halaman
 *
 * @param direction - Arah transisi (forward/backward)
 */
export function usePageTransition(direction: MaybeRef<'forward' | 'backward'> = 'forward') {
    const currentDirection = computed(() => unref(direction))

    const enterConfig = computed(() => ({
        initial: {
            opacity: 0,
            x: currentDirection.value === 'forward' ? 30 : -30,
        },
        enter: {
            opacity: 1,
            x: 0,
            transition: springPresets.smooth,
        },
    }))

    const leaveConfig = computed(() => ({
        leave: {
            opacity: 0,
            x: currentDirection.value === 'forward' ? -30 : 30,
            transition: springPresets.snappy,
        },
    }))

    return {
        enterConfig,
        leaveConfig,
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

    const bounceConfig = computed(() => ({
        initial: { scale: 1 },
        bounce: shouldBounce.value
            ? {
                  scale: [1, 1.2, 1],
                  transition: springPresets.bouncy,
              }
            : { scale: 1 },
    }))

    return {
        shouldBounce,
        bounceConfig,
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

