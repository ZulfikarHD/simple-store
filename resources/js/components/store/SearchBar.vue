<script setup lang="ts">
/**
 * SearchBar Component
 * Input pencarian dengan icon dan clear button
 * terintegrasi dengan Airbnb-style design
 */
import { ref, watch } from 'vue'
import { Search, X } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useDebounceFn } from '@vueuse/core'

interface Props {
    modelValue?: string
    placeholder?: string
    /** Debounce delay dalam ms */
    debounce?: number
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Cari produk...',
    debounce: 300,
})

const emit = defineEmits<{
    'update:modelValue': [value: string]
    search: [value: string]
}>()

const localValue = ref(props.modelValue)

/**
 * Debounced search untuk optimasi performa
 * mencegah request berlebihan saat user mengetik
 */
const debouncedSearch = useDebounceFn((value: string) => {
    emit('search', value)
}, props.debounce)

/**
 * Watch perubahan local value dan emit ke parent
 */
watch(localValue, (value) => {
    emit('update:modelValue', value)
    debouncedSearch(value)
})

/**
 * Watch perubahan dari parent
 */
watch(
    () => props.modelValue,
    (value) => {
        localValue.value = value
    },
)

/**
 * Clear search input
 */
function clearSearch() {
    localValue.value = ''
    emit('search', '')
}
</script>

<template>
    <div class="relative">
        <!-- Search Icon -->
        <Search
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
        />

        <!-- Search Input -->
        <Input
            v-model="localValue"
            type="search"
            :placeholder="placeholder"
            class="h-10 rounded-full border-muted bg-secondary/50 pl-10 pr-10 focus:bg-background"
        />

        <!-- Clear Button -->
        <Button
            v-if="localValue"
            variant="ghost"
            size="icon"
            class="absolute right-1 top-1/2 h-8 w-8 -translate-y-1/2 rounded-full"
            @click="clearSearch"
        >
            <X class="h-4 w-4" />
        </Button>
    </div>
</template>

