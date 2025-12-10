/**
 * useMotionV Composable
 * Menyediakan spring physics animations untuk iOS-like interactions
 * menggunakan motion-v library dengan konfigurasi presets
 * yang mengikuti design guideline Apple
 *
 * @author Zulfikar Hidayatullah
 */
import type { Ref } from 'vue'
import { computed, ref } from 'vue'

/**
 * iOS Spring Presets
 * Konfigurasi spring physics yang mengikuti karakteristik animasi iOS native
 * dengan nilai stiffness, damping, dan mass yang optimal
 */
export const springPresets = {
    /**
     * Default - Balanced spring untuk general use
     * Cocok untuk sebagian besar transisi UI
     */
    default: { stiffness: 300, damping: 30, mass: 1 },

    /**
     * Bouncy - Spring dengan bounce effect yang lebih pronounced
     * Ideal untuk badges, notifications, dan playful elements
     */
    bouncy: { stiffness: 400, damping: 15, mass: 0.8 },

    /**
     * Snappy - Respons cepat untuk micro-interactions
     * Perfect untuk button press, toggle states
     */
    snappy: { stiffness: 500, damping: 40, mass: 0.5 },

    /**
     * Smooth - Transisi halus dan elegant
     * Cocok untuk page transitions, modals
     */
    smooth: { stiffness: 200, damping: 25, mass: 1.2 },

    /**
     * Stiff - Minimal bounce, lebih precise
     * Untuk form validation, error states
     */
    stiff: { stiffness: 600, damping: 50, mass: 0.5 },

    /**
     * Gentle - Slow dan smooth untuk large elements
     * Ideal untuk hero sections, image galleries
     */
    gentle: { stiffness: 150, damping: 20, mass: 1.5 },

    /**
     * iOS Native - Mengikuti karakteristik iOS system animations
     */
    ios: { stiffness: 300, damping: 25, mass: 1 },
} as const

export type SpringPreset = keyof typeof springPresets

/**
 * iOS Animation Variants
 * Pre-configured animation variants untuk berbagai use cases
 */
export const animationVariants = {
    /**
     * Fade In - Simple opacity transition
     */
    fadeIn: {
        initial: { opacity: 0 },
        animate: { opacity: 1 },
        exit: { opacity: 0 },
    },

    /**
     * Scale In - Scale from small to normal with spring
     */
    scaleIn: {
        initial: { opacity: 0, scale: 0.9 },
        animate: { opacity: 1, scale: 1 },
        exit: { opacity: 0, scale: 0.9 },
    },

    /**
     * Slide Up - Slide from bottom with fade
     */
    slideUp: {
        initial: { opacity: 0, y: 20 },
        animate: { opacity: 1, y: 0 },
        exit: { opacity: 0, y: 20 },
    },

    /**
     * Slide Down - Slide from top with fade
     */
    slideDown: {
        initial: { opacity: 0, y: -20 },
        animate: { opacity: 1, y: 0 },
        exit: { opacity: 0, y: -20 },
    },

    /**
     * Slide Left - Slide from right with fade
     */
    slideLeft: {
        initial: { opacity: 0, x: 20 },
        animate: { opacity: 1, x: 0 },
        exit: { opacity: 0, x: 20 },
    },

    /**
     * Slide Right - Slide from left with fade
     */
    slideRight: {
        initial: { opacity: 0, x: -20 },
        animate: { opacity: 1, x: 0 },
        exit: { opacity: 0, x: -20 },
    },

    /**
     * Pop - Bouncy scale animation for badges/buttons
     */
    pop: {
        initial: { opacity: 0, scale: 0 },
        animate: { opacity: 1, scale: 1 },
        exit: { opacity: 0, scale: 0 },
    },

    /**
     * Bounce - Scale dengan bounce effect untuk success states
     */
    bounce: {
        initial: { scale: 0 },
        animate: { scale: [0, 1.2, 1] },
        exit: { scale: 0 },
    },
} as const

export type AnimationVariant = keyof typeof animationVariants

/**
 * Get spring transition config untuk motion-v
 * @param preset - Spring preset name
 * @param delay - Optional delay dalam milliseconds
 */
export function getSpringTransition(
    preset: SpringPreset = 'default',
    delay: number = 0
) {
    const spring = springPresets[preset]
    return {
        type: 'spring' as const,
        ...spring,
        delay: delay / 1000, // motion-v menggunakan seconds
    }
}

/**
 * Get stagger delay untuk animasi berurutan
 * @param index - Index item dalam list
 * @param baseDelay - Base delay dalam ms (default 0)
 * @param staggerDelay - Delay per item dalam ms (default 50)
 */
export function getStaggerDelay(
    index: number,
    baseDelay: number = 0,
    staggerDelay: number = 50
): number {
    return (baseDelay + index * staggerDelay) / 1000
}

/**
 * Generate animation props untuk staggered list items
 * @param index - Index item dalam list
 * @param variant - Animation variant
 * @param preset - Spring preset
 */
export function getStaggeredProps(
    index: number,
    variant: AnimationVariant = 'slideUp',
    preset: SpringPreset = 'smooth'
) {
    const v = animationVariants[variant]
    return {
        initial: v.initial,
        animate: v.animate,
        exit: v.exit,
        transition: getSpringTransition(preset, index * 50),
    }
}

/**
 * Composable untuk press animation states
 * Memberikan visual feedback saat element di-tap/click
 */
export function usePressState() {
    const isPressed = ref(false)

    const pressHandlers = {
        onMousedown: () => { isPressed.value = true },
        onMouseup: () => { isPressed.value = false },
        onMouseleave: () => { isPressed.value = false },
        onTouchstart: () => { isPressed.value = true },
        onTouchend: () => { isPressed.value = false },
    }

    /**
     * Computed scale berdasarkan press state
     * iOS standard press scale adalah 0.97
     */
    const pressScale = computed(() => isPressed.value ? 0.97 : 1)

    /**
     * Animation props untuk press effect
     */
    const pressAnimation = computed(() => ({
        animate: { scale: pressScale.value },
        transition: { type: 'spring' as const, ...springPresets.snappy },
    }))

    return {
        isPressed,
        pressHandlers,
        pressScale,
        pressAnimation,
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

/**
 * Composable untuk bounce animation (badges, counters)
 * @param trigger - Reactive value yang trigger animasi
 */
export function useBounceAnimation(trigger: Ref<boolean | number>) {
    const shouldBounce = ref(false)
    let prevValue = trigger.value

    // Manual watch implementation
    const checkBounce = () => {
        if (trigger.value !== prevValue) {
            shouldBounce.value = true
            setTimeout(() => {
                shouldBounce.value = false
            }, 300)
            prevValue = trigger.value
        }
    }

    return {
        shouldBounce,
        checkBounce,
    }
}

/**
 * Simple stagger delay helper untuk Motion components
 * @param index - Index item dalam list
 * @param baseDelay - Base delay dalam seconds (default 0.05)
 */
export function staggerDelay(index: number, baseDelay: number = 0.05): number {
    return index * baseDelay
}

/**
 * Export semua utilities untuk easy import
 */
export {
    springPresets as iosSpring,
    animationVariants as iosVariants,
}

