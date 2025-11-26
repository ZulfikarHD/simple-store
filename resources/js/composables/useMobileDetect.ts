/**
 * useMobileDetect Composable
 * Mendeteksi apakah user menggunakan mobile device
 * dengan reactive state yang update saat window resize
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, onMounted, onUnmounted, computed } from 'vue'

/**
 * Breakpoints sesuai Tailwind CSS defaults
 */
export const BREAKPOINTS = {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1536,
} as const

/**
 * State untuk window width
 */
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

/**
 * Flag untuk track apakah listener sudah di-setup
 */
let isListenerSetup = false
let listenerCount = 0

/**
 * Handler untuk window resize event
 */
function handleResize(): void {
    windowWidth.value = window.innerWidth
}

/**
 * Composable untuk mendeteksi mobile device dan screen size
 */
export function useMobileDetect() {
    /**
     * Setup resize listener saat mounted
     */
    onMounted(() => {
        if (typeof window === 'undefined') return

        // Update initial value
        windowWidth.value = window.innerWidth

        // Setup listener jika belum ada
        if (!isListenerSetup) {
            window.addEventListener('resize', handleResize, { passive: true })
            isListenerSetup = true
        }
        listenerCount++
    })

    /**
     * Cleanup listener saat unmounted
     */
    onUnmounted(() => {
        listenerCount--
        // Remove listener jika tidak ada component yang menggunakan
        if (listenerCount === 0 && isListenerSetup) {
            window.removeEventListener('resize', handleResize)
            isListenerSetup = false
        }
    })

    /**
     * Computed untuk cek apakah mobile (< md breakpoint)
     */
    const isMobile = computed(() => windowWidth.value < BREAKPOINTS.md)

    /**
     * Computed untuk cek apakah tablet (>= md && < lg)
     */
    const isTablet = computed(() =>
        windowWidth.value >= BREAKPOINTS.md && windowWidth.value < BREAKPOINTS.lg
    )

    /**
     * Computed untuk cek apakah desktop (>= lg)
     */
    const isDesktop = computed(() => windowWidth.value >= BREAKPOINTS.lg)

    /**
     * Computed untuk cek apakah small mobile (< sm)
     */
    const isSmallMobile = computed(() => windowWidth.value < BREAKPOINTS.sm)

    /**
     * Computed untuk current breakpoint name
     */
    const currentBreakpoint = computed(() => {
        if (windowWidth.value < BREAKPOINTS.sm) return 'xs'
        if (windowWidth.value < BREAKPOINTS.md) return 'sm'
        if (windowWidth.value < BREAKPOINTS.lg) return 'md'
        if (windowWidth.value < BREAKPOINTS.xl) return 'lg'
        if (windowWidth.value < BREAKPOINTS['2xl']) return 'xl'
        return '2xl'
    })

    /**
     * Helper function untuk check breakpoint
     */
    function isBreakpoint(breakpoint: keyof typeof BREAKPOINTS): boolean {
        return windowWidth.value >= BREAKPOINTS[breakpoint]
    }

    /**
     * Helper function untuk check jika width kurang dari breakpoint
     */
    function isBelow(breakpoint: keyof typeof BREAKPOINTS): boolean {
        return windowWidth.value < BREAKPOINTS[breakpoint]
    }

    return {
        windowWidth,
        isMobile,
        isTablet,
        isDesktop,
        isSmallMobile,
        currentBreakpoint,
        isBreakpoint,
        isBelow,
        BREAKPOINTS,
    }
}

/**
 * Export default untuk convenience
 */
export default useMobileDetect

