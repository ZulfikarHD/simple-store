<script setup lang="ts">
/**
 * CategoryFilter Component
 * Menampilkan filter kategori dalam bentuk tabs horizontal
 * dengan iOS-like animations dan Brand Colors styling
 *
 * @author Zulfikar Hidayatullah
 */
import { ref } from 'vue'
import { Motion } from 'motion-v'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'

/**
 * Props definition untuk CategoryFilter
 */
interface Category {
    id: number
    name: string
    slug: string
    products_count?: number
}

interface Props {
    categories: Category[]
    /** ID kategori yang sedang aktif, null untuk semua */
    activeCategory?: number | null
}

const props = withDefaults(defineProps<Props>(), {
    activeCategory: null,
})

/**
 * Emit event ketika kategori dipilih
 */
const emit = defineEmits<{
    select: [categoryId: number | null]
}>()

/**
 * Haptic feedback
 */
const haptic = useHapticFeedback()

/**
 * Press state untuk iOS-like feedback
 */
const pressedId = ref<number | null | 'all'>(null)

/**
 * Check apakah kategori sedang aktif
 */
function isActive(categoryId: number | null): boolean {
    return props.activeCategory === categoryId
}

/**
 * Handle category selection dengan haptic
 */
function handleSelect(categoryId: number | null) {
    haptic.selection()
    emit('select', categoryId)
}

/**
 * Spring transition untuk animations
 */
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <div class="ios-snap-scroll flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <!-- All Categories Button -->
        <Motion
            :animate="{ scale: pressedId === 'all' ? 0.95 : 1 }"
            :transition="snappyTransition"
        >
            <button
                type="button"
                class="ios-button shrink-0 rounded-full px-4 py-2.5 text-sm font-medium shadow-sm transition-all"
                :class="
                    isActive(null)
                        ? 'bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 text-white shadow-brand-blue-500/25'
                        : 'border border-brand-blue-200 bg-white text-brand-blue-600 hover:bg-brand-blue-50 dark:border-brand-blue-700 dark:bg-brand-blue-900/20 dark:text-brand-blue-300 dark:hover:bg-brand-blue-900/40'
                "
                @click="handleSelect(null)"
                @mousedown="pressedId = 'all'"
                @mouseup="pressedId = null"
                @mouseleave="pressedId = null"
                @touchstart.passive="pressedId = 'all'"
                @touchend="pressedId = null"
            >
                Semua
            </button>
        </Motion>

        <!-- Category Buttons -->
        <Motion
            v-for="category in categories"
            :key="category.id"
            :animate="{ scale: pressedId === category.id ? 0.95 : 1 }"
            :transition="snappyTransition"
        >
            <button
                type="button"
                class="ios-button shrink-0 rounded-full px-4 py-2.5 text-sm font-medium shadow-sm transition-all"
                :class="
                    isActive(category.id)
                        ? 'bg-gradient-to-r from-brand-blue-500 to-brand-blue-600 text-white shadow-brand-blue-500/25'
                        : 'border border-brand-blue-200 bg-white text-brand-blue-600 hover:bg-brand-blue-50 dark:border-brand-blue-700 dark:bg-brand-blue-900/20 dark:text-brand-blue-300 dark:hover:bg-brand-blue-900/40'
                "
                @click="handleSelect(category.id)"
                @mousedown="pressedId = category.id"
                @mouseup="pressedId = null"
                @mouseleave="pressedId = null"
                @touchstart.passive="pressedId = category.id"
                @touchend="pressedId = null"
            >
                {{ category.name }}
                <span
                    v-if="category.products_count !== undefined"
                    class="ml-1.5 rounded-full bg-white/20 px-1.5 py-0.5 text-xs"
                    :class="isActive(category.id) ? 'bg-white/20' : 'bg-brand-blue-100 dark:bg-brand-blue-800'"
                >
                    {{ category.products_count }}
                </span>
            </button>
        </Motion>
    </div>
</template>

<style scoped>
/* Hide scrollbar but keep functionality */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

