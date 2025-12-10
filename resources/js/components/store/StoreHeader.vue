<script setup lang="ts">
/**
 * StoreHeader Component
 * Header navigation untuk halaman user dengan logo dan nama toko dinamis
 * menggunakan store branding dari shared props
 *
 * @author Zulfikar Hidayatullah
 */
import { Link, usePage } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { computed, ref } from 'vue'
import { ShoppingBag } from 'lucide-vue-next'
import { springPresets } from '@/composables/useMotionV'
import { home } from '@/routes'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

/**
 * Interface untuk store branding dari shared props
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

const page = usePage()
const haptic = useHapticFeedback()

/**
 * Computed untuk mendapatkan data store dari shared props
 */
const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})

/**
 * Press state untuk iOS-like feedback
 */
const isPressed = ref(false)

function handlePress() {
    isPressed.value = true
    haptic.light()
}

/**
 * Spring transition untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Motion
        :initial="{ opacity: 0, x: -20 }"
        :animate="{ opacity: 1, x: 0 }"
        :transition="springTransition"
    >
        <Motion
            :animate="{ scale: isPressed ? 0.97 : 1 }"
            :transition="snappyTransition"
        >
            <Link
                :href="home()"
                class="flex items-center gap-2 transition-transform sm:gap-3"
                @mousedown="handlePress"
                @mouseup="isPressed = false"
                @mouseleave="isPressed = false"
                @touchstart.passive="handlePress"
                @touchend="isPressed = false"
            >
                <div class="h-9 w-9 sm:h-10 sm:w-10">
                    <img
                        v-if="store.logo"
                        :src="`/storage/${store.logo}`"
                        :alt="store.name"
                        class="h-full w-full rounded-xl object-contain"
                    />
                    <ShoppingBag v-else class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-bold text-foreground sm:text-xl">{{ store.name }}</span>
                    <span class="hidden text-[10px] font-medium text-brand-gold sm:block">{{ store.tagline }}</span>
                </div>
            </Link>
        </Motion>
    </Motion>
</template>

