<script setup lang="ts">
/**
 * PullToRefresh Component
 * iOS-style pull-to-refresh gesture dengan spring animations
 * dan loading spinner untuk mobile refresh experience
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Loader2 } from 'lucide-vue-next'

/**
 * Props untuk PullToRefresh
 */
interface Props {
    /** Threshold untuk trigger refresh (dalam px) */
    threshold?: number
    /** Resistance factor untuk drag (0-1) */
    resistance?: number
    /** Custom refresh handler */
    onRefresh?: () => Promise<void>
    /** Disable pull-to-refresh */
    disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    threshold: 80,
    resistance: 0.5,
    onRefresh: undefined,
    disabled: false,
})

/**
 * Haptic feedback
 */
const haptic = useHapticFeedback()

/**
 * Refs untuk state management
 */
const containerRef = ref<HTMLElement | null>(null)
const isPulling = ref(false)
const isRefreshing = ref(false)
const pullDistance = ref(0)
const startY = ref(0)
const canPull = ref(false)

/**
 * Computed untuk progress indikator (0-1)
 */
const pullProgress = computed(() => {
    return Math.min(1, pullDistance.value / props.threshold)
})

/**
 * Computed untuk rotation indikator
 */
const indicatorRotation = computed(() => {
    return pullProgress.value * 360
})

/**
 * Computed untuk opacity indikator
 */
const indicatorOpacity = computed(() => {
    return Math.min(1, pullProgress.value * 1.5)
})

/**
 * Computed untuk transform style
 */
const pullStyle = computed(() => ({
    transform: `translateY(${pullDistance.value}px)`,
    transition: isPulling.value ? 'none' : 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
}))

/**
 * Computed untuk indicator style
 */
const indicatorStyle = computed(() => ({
    transform: `translateY(${Math.min(pullDistance.value, props.threshold + 20)}px) rotate(${indicatorRotation.value}deg)`,
    opacity: indicatorOpacity.value,
    transition: isPulling.value ? 'none' : 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
}))

/**
 * Check apakah bisa mulai pull (scroll di top)
 */
function checkCanPull(): boolean {
    if (props.disabled) return false

    // Check scroll position
    const scrollTop = window.scrollY || document.documentElement.scrollTop
    return scrollTop <= 0
}

/**
 * Handle touch start
 */
function handleTouchStart(e: TouchEvent) {
    if (isRefreshing.value) return

    canPull.value = checkCanPull()
    if (!canPull.value) return

    startY.value = e.touches[0].clientY
    isPulling.value = true
}

/**
 * Handle touch move
 */
function handleTouchMove(e: TouchEvent) {
    if (!isPulling.value || !canPull.value || isRefreshing.value) return

    const currentY = e.touches[0].clientY
    const deltaY = currentY - startY.value

    // Hanya pull ke bawah
    if (deltaY <= 0) {
        pullDistance.value = 0
        return
    }

    // Apply resistance
    const resistedDelta = deltaY * props.resistance
    pullDistance.value = resistedDelta

    // Haptic feedback saat mencapai threshold
    if (resistedDelta >= props.threshold && pullDistance.value < props.threshold) {
        haptic.medium()
    }

    // Prevent default scroll ketika pulling
    if (deltaY > 0) {
        e.preventDefault()
    }
}

/**
 * Handle touch end
 */
async function handleTouchEnd() {
    if (!isPulling.value) return

    isPulling.value = false

    // Check apakah mencapai threshold
    if (pullDistance.value >= props.threshold && !isRefreshing.value) {
        await triggerRefresh()
    } else {
        // Reset pull distance dengan spring animation
        pullDistance.value = 0
    }
}

/**
 * Trigger refresh action
 */
async function triggerRefresh() {
    isRefreshing.value = true
    pullDistance.value = props.threshold // Keep indicator visible
    haptic.success()

    try {
        if (props.onRefresh) {
            // Custom refresh handler
            await props.onRefresh()
        } else {
            // Default: reload current page via Inertia
            await new Promise<void>((resolve) => {
                router.reload({
                    onFinish: () => resolve(),
                })
            })
        }
    } finally {
        isRefreshing.value = false
        pullDistance.value = 0
    }
}

/**
 * Setup event listeners
 */
onMounted(() => {
    const container = containerRef.value
    if (container) {
        container.addEventListener('touchstart', handleTouchStart, { passive: true })
        container.addEventListener('touchmove', handleTouchMove, { passive: false })
        container.addEventListener('touchend', handleTouchEnd, { passive: true })
    }
})

onUnmounted(() => {
    const container = containerRef.value
    if (container) {
        container.removeEventListener('touchstart', handleTouchStart)
        container.removeEventListener('touchmove', handleTouchMove)
        container.removeEventListener('touchend', handleTouchEnd)
    }
})
</script>

<template>
    <div
        ref="containerRef"
        class="ios-ptr-container relative min-h-full"
    >
        <!-- Pull indicator -->
        <div
            class="ios-ptr-indicator pointer-events-none absolute left-1/2 top-0 z-50 -translate-x-1/2"
            :style="indicatorStyle"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-background shadow-lg ring-1 ring-border"
            >
                <Loader2
                    v-if="isRefreshing"
                    class="h-5 w-5 animate-spin text-primary"
                />
                <svg
                    v-else
                    class="h-5 w-5 text-primary transition-transform"
                    :class="{ 'rotate-180': pullProgress >= 1 }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                    />
                </svg>
            </div>
        </div>

        <!-- Content wrapper dengan pull transform -->
        <div :style="pullStyle">
            <slot />
        </div>
    </div>
</template>

