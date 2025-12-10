<script setup lang="ts">
/**
 * CategoryFilter Component
 * Menampilkan filter kategori dalam bentuk compact chips
 * dengan iOS-like animations dan clean mobile-first design
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
    <div class="ios-snap-scroll -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:gap-2 sm:px-0 scrollbar-hide">
        <!-- All Categories Chip -->
        <Motion
            :animate="{ scale: pressedId === 'all' ? 0.93 : 1 }"
            :transition="snappyTransition"
        >
            <button
                type="button"
                class="ios-chip relative shrink-0 rounded-full px-3 py-1.5 text-[13px] font-medium transition-all sm:px-4 sm:py-2 sm:text-sm"
                :class="
                    isActive(null)
                        ? 'bg-brand-blue-500 text-white shadow-sm'
                        : 'bg-muted/60 text-muted-foreground hover:bg-muted dark:bg-muted/40'
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

        <!-- Category Chips - Compact tanpa count untuk mobile cleaner look -->
        <Motion
            v-for="category in categories"
            :key="category.id"
            :animate="{ scale: pressedId === category.id ? 0.93 : 1 }"
            :transition="snappyTransition"
        >
            <button
                type="button"
                class="ios-chip relative shrink-0 rounded-full px-3 py-1.5 text-[13px] font-medium transition-all sm:px-4 sm:py-2 sm:text-sm"
                :class="
                    isActive(category.id)
                        ? 'bg-brand-blue-500 text-white shadow-sm'
                        : 'bg-muted/60 text-muted-foreground hover:bg-muted dark:bg-muted/40'
                "
                @click="handleSelect(category.id)"
                @mousedown="pressedId = category.id"
                @mouseup="pressedId = null"
                @mouseleave="pressedId = null"
                @touchstart.passive="pressedId = category.id"
                @touchend="pressedId = null"
            >
                {{ category.name }}
                <!-- Count badge - subtle, only on desktop or when active -->
                <span
                    v-if="category.products_count !== undefined && category.products_count > 0"
                    class="ml-1 text-[11px] sm:text-xs"
                    :class="isActive(category.id) ? 'text-white/70' : 'text-muted-foreground/60'"
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

/* iOS-like chip styling */
.ios-chip {
    /* Smooth press feedback */
    transition: transform 0.1s ease, background-color 0.15s ease;
}
</style>

