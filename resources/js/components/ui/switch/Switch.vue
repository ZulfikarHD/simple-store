<script setup lang="ts">
/**
 * iOS-style Toggle Switch Component
 * Replika dari iOS switch dengan animasi spring dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'

interface Props {
    modelValue?: boolean
    disabled?: boolean
    id?: string
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
}>()

const haptic = useHapticFeedback()

const isChecked = computed({
    get: () => props.modelValue,
    set: (value: boolean) => emit('update:modelValue', value),
})

function toggle() {
    if (props.disabled) return
    haptic.selection()
    isChecked.value = !isChecked.value
}
</script>

<template>
    <button
        type="button"
        role="switch"
        :id="id"
        :aria-checked="isChecked"
        :disabled="disabled"
        :class="[
            'ios-switch relative inline-flex h-8 w-[52px] shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2',
            'disabled:cursor-not-allowed disabled:opacity-50',
            isChecked
                ? 'bg-green-500 dark:bg-green-600'
                : 'bg-gray-200 dark:bg-gray-700',
        ]"
        @click="toggle"
    >
        <span
            :class="[
                'pointer-events-none block h-[26px] w-[26px] rounded-full bg-white shadow-lg ring-0 transition-transform duration-200',
                'ease-[cubic-bezier(0.175,0.885,0.32,1.275)]',
                isChecked ? 'translate-x-[23px]' : 'translate-x-[3px]',
            ]"
        />
    </button>
</template>

