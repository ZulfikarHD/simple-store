<script setup lang="ts">
/**
 * EmptyState Component
 * Menampilkan state kosong dengan ilustrasi dan pesan
 * digunakan untuk keranjang kosong, pencarian tidak ditemukan, dll
 */
import { Button } from '@/components/ui/button'

interface Props {
    /** Icon atau emoji untuk ditampilkan */
    icon?: string
    /** Judul empty state */
    title: string
    /** Deskripsi atau pesan tambahan */
    description?: string
    /** Label untuk action button */
    actionLabel?: string
    /** URL untuk action button */
    actionUrl?: string
}

withDefaults(defineProps<Props>(), {
    icon: '📦',
})

const emit = defineEmits<{
    action: []
}>()
</script>

<template>
    <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
        <!-- Icon/Illustration -->
        <div class="mb-4 text-6xl">
            {{ icon }}
        </div>

        <!-- Title -->
        <h3 class="mb-2 text-lg font-semibold text-foreground">
            {{ title }}
        </h3>

        <!-- Description -->
        <p
            v-if="description"
            class="mb-6 max-w-sm text-sm text-muted-foreground"
        >
            {{ description }}
        </p>

        <!-- Action Button -->
        <Button
            v-if="actionLabel"
            :as="actionUrl ? 'a' : 'button'"
            :href="actionUrl"
            @click="!actionUrl && emit('action')"
        >
            {{ actionLabel }}
        </Button>
    </div>
</template>

