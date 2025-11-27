<script setup lang="ts">
/**
 * PageTransition Component
 * Menyediakan smooth page transitions dengan iOS-like spring animations
 * untuk navigasi antar halaman menggunakan Inertia router events
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Props untuk konfigurasi transisi
 */
interface Props {
    /** Durasi transisi dalam ms */
    duration?: number
    /** Mode transisi: fade, slide, atau scale */
    mode?: 'fade' | 'slide' | 'scale' | 'slide-up'
}

const props = withDefaults(defineProps<Props>(), {
    duration: 300,
    mode: 'fade',
})

/**
 * State untuk mengontrol animasi
 */
const isNavigating = ref(false)
const isEntering = ref(true)

/**
 * Computed classes untuk transisi berdasarkan mode
 */
const transitionClasses = {
    fade: {
        enter: 'opacity-100',
        leave: 'opacity-0',
        base: 'transition-opacity duration-300 ease-[var(--ios-spring-smooth)]',
    },
    slide: {
        enter: 'translate-x-0 opacity-100',
        leave: 'translate-x-8 opacity-0',
        base: 'transition-all duration-300 ease-[var(--ios-spring-smooth)]',
    },
    'slide-up': {
        enter: 'translate-y-0 opacity-100',
        leave: 'translate-y-4 opacity-0',
        base: 'transition-all duration-300 ease-[var(--ios-spring-smooth)]',
    },
    scale: {
        enter: 'scale-100 opacity-100',
        leave: 'scale-95 opacity-0',
        base: 'transition-all duration-300 ease-[var(--ios-spring-smooth)]',
    },
}

/**
 * Setup Inertia router event listeners
 */
let removeStartListener: (() => void) | null = null
let removeFinishListener: (() => void) | null = null

onMounted(() => {
    // Listen untuk navigation start
    removeStartListener = router.on('start', () => {
        isNavigating.value = true
        isEntering.value = false
    })

    // Listen untuk navigation finish
    removeFinishListener = router.on('finish', () => {
        // Small delay untuk smooth transition
        setTimeout(() => {
            isEntering.value = true
            isNavigating.value = false
        }, 50)
    })
})

onUnmounted(() => {
    removeStartListener?.()
    removeFinishListener?.()
})
</script>

<template>
    <div
        :class="[
            transitionClasses[mode].base,
            isEntering ? transitionClasses[mode].enter : transitionClasses[mode].leave,
        ]"
    >
        <slot />
    </div>
</template>

