<script setup lang="ts">
/**
 * SortableHeader Component
 * iOS-style sortable table header dengan animated indicators
 * yang memberikan visual feedback untuk kolom yang dapat di-sort
 *
 * @author Zulfikar Hidayatullah
 */
import { ChevronUp, ChevronDown, ChevronsUpDown } from 'lucide-vue-next'
import { computed } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

interface Props {
    column: string
    label: string
    currentSort: string | null
    currentDirection: 'asc' | 'desc'
    align?: 'left' | 'center' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
    align: 'left',
})

const emit = defineEmits<{
    sort: [column: string]
}>()

const haptic = useHapticFeedback()

/**
 * Computed untuk cek apakah kolom ini sedang aktif di-sort
 */
const isActive = computed(() => props.currentSort === props.column)

/**
 * Computed untuk alignment class
 */
const alignClass = computed(() => {
    switch (props.align) {
        case 'center':
            return 'justify-center'
        case 'right':
            return 'justify-end'
        default:
            return 'justify-start'
    }
})

/**
 * Handler untuk sort action
 */
function handleSort() {
    haptic.selection()
    emit('sort', props.column)
}
</script>

<template>
    <th
        class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground"
        :class="[alignClass]"
    >
        <button
            type="button"
            class="group -mx-2 -my-1 inline-flex items-center gap-1.5 rounded-lg px-2 py-1 transition-all duration-150 hover:bg-muted/50 hover:text-foreground active:scale-[0.97] active:bg-muted"
            :class="{ 'text-foreground': isActive }"
            @click="handleSort"
        >
            <span>{{ label }}</span>
            <span class="relative flex h-4 w-4 items-center justify-center">
                <!-- Inactive state: double chevron -->
                <ChevronsUpDown
                    v-if="!isActive"
                    class="h-3.5 w-3.5 text-muted-foreground/50 transition-all group-hover:text-muted-foreground"
                />
                <!-- Active ascending -->
                <ChevronUp
                    v-else-if="currentDirection === 'asc'"
                    class="h-4 w-4 text-primary animate-ios-pop"
                />
                <!-- Active descending -->
                <ChevronDown
                    v-else
                    class="h-4 w-4 text-primary animate-ios-pop"
                />
            </span>
        </button>
    </th>
</template>

