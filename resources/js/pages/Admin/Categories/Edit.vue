<script setup lang="ts">
/**
 * Admin Categories Edit Page
 * Form untuk mengedit kategori yang sudah ada dengan fitur, yaitu:
 * - Pre-filled data dari kategori yang dipilih
 * - Image upload dengan preview (bisa ganti atau retain existing)
 * - Toggle status aktif
 * - iOS-like design dengan spring animations dan haptic feedback
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import InputError from '@/components/InputError.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as categoriesIndex, update } from '@/routes/admin/categories'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    FolderTree,
    Upload,
    X,
    ArrowLeft,
    Save,
} from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

interface Category {
    id: number
    name: string
    slug: string
    description: string | null
    image: string | null
    is_active: boolean
    sort_order: number
    products_count?: number
}

interface Props {
    category: Category
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Kategori', href: categoriesIndex().url },
    { title: 'Edit Kategori', href: '#' },
]

// Form state - pre-filled dengan data kategori
const form = ref({
    name: props.category.name,
    description: props.category.description || '',
    image: null as File | null,
    is_active: props.category.is_active,
    sort_order: props.category.sort_order.toString(),
})

// Errors
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

// Image preview
const imagePreview = ref<string | null>(null)
const hasExistingImage = computed(() => !!props.category.image)
const showExistingImage = ref(true)

/**
 * Get existing image URL
 */
const existingImageUrl = computed(() => {
    if (props.category.image) {
        return `/storage/${props.category.image}`
    }
    return null
})

/**
 * Handle image file selection
 */
function handleImageChange(event: Event) {
    haptic.selection()
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (file) {
        form.value.image = file
        imagePreview.value = URL.createObjectURL(file)
        showExistingImage.value = false
    }
}

/**
 * Remove selected new image
 */
function removeNewImage() {
    haptic.light()
    form.value.image = null
    imagePreview.value = null
    showExistingImage.value = hasExistingImage.value
}

/**
 * Submit form untuk update kategori
 */
function submitForm() {
    haptic.medium()
    isSubmitting.value = true
    errors.value = {}

    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('name', form.value.name)
    formData.append('description', form.value.description || '')
    formData.append('is_active', form.value.is_active ? '1' : '0')
    if (form.value.sort_order) {
        formData.append('sort_order', form.value.sort_order)
    }

    if (form.value.image) {
        formData.append('image', form.value.image)
    }

    router.post(update(props.category.id).url, formData, {
        forceFormData: true,
        onSuccess: () => {
            haptic.success()
        },
        onError: (errs) => {
            haptic.error()
            errors.value = errs as Record<string, string>
        },
        onFinish: () => {
            isSubmitting.value = false
        },
    })
}

/**
 * Cancel dan kembali ke daftar kategori
 */
function cancel() {
    haptic.light()
    router.get(categoriesIndex().url)
}
</script>

<template>
    <Head :title="`Edit ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header dengan spring animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            Edit Kategori
                        </h1>
                        <p class="text-muted-foreground">
                            Ubah informasi kategori "{{ category.name }}"
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        class="ios-button gap-2"
                        @click="cancel"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Button>
                </Motion>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <!-- Basic Info Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <FolderTree class="h-5 w-5" />
                                    Informasi Kategori
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="flex flex-col gap-4">
                                <!-- Name -->
                                <div class="flex flex-col gap-2">
                                    <Label for="name">Nama Kategori *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Masukkan nama kategori"
                                        class="ios-input"
                                        :class="{ 'border-destructive': errors.name }"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <!-- Description -->
                                <div class="flex flex-col gap-2">
                                    <Label for="description">Deskripsi</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        placeholder="Masukkan deskripsi kategori"
                                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="{ 'border-destructive': errors.description }"
                                    ></textarea>
                                    <InputError :message="errors.description" />
                                </div>

                                <!-- Sort Order -->
                                <div class="flex flex-col gap-2">
                                    <Label for="sort_order">Urutan</Label>
                                    <Input
                                        id="sort_order"
                                        v-model="form.sort_order"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="ios-input"
                                        :class="{ 'border-destructive': errors.sort_order }"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Angka lebih kecil akan ditampilkan lebih dulu
                                    </p>
                                    <InputError :message="errors.sort_order" />
                                </div>
                            </CardContent>
                            </Card>
                        </Motion>

                        <!-- Image Upload Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Upload class="h-5 w-5" />
                                    Gambar Kategori
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-col gap-4">
                                    <!-- New Image Preview -->
                                    <Motion
                                        v-if="imagePreview"
                                        :initial="{ opacity: 0, scale: 0.9 }"
                                        :animate="{ opacity: 1, scale: 1 }"
                                        :transition="springPresets.bouncy"
                                        class="relative inline-block"
                                    >
                                        <img
                                            :src="imagePreview"
                                            alt="Preview"
                                            class="h-48 w-48 rounded-xl object-cover"
                                        />
                                        <button
                                            type="button"
                                            class="ios-button absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-white shadow"
                                            @click="removeNewImage"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                        <p class="mt-2 text-xs text-muted-foreground">
                                            Gambar baru (akan menggantikan gambar lama)
                                        </p>
                                    </Motion>

                                    <!-- Existing Image -->
                                    <Motion
                                        v-else-if="showExistingImage && existingImageUrl"
                                        :initial="{ opacity: 0 }"
                                        :animate="{ opacity: 1 }"
                                        :transition="springPresets.ios"
                                        class="flex flex-col gap-2"
                                    >
                                        <p class="text-sm font-medium">Gambar Saat Ini</p>
                                        <img
                                            :src="existingImageUrl"
                                            :alt="category.name"
                                            class="h-48 w-48 rounded-xl object-cover"
                                        />
                                    </Motion>

                                    <!-- Upload Input -->
                                    <div
                                        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-muted-foreground/50"
                                    >
                                        <Upload class="mb-4 h-10 w-10 text-muted-foreground" />
                                        <p class="mb-2 text-sm font-medium">
                                            {{ hasExistingImage ? 'Klik untuk mengganti gambar' : 'Klik atau drag file untuk upload' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            PNG, JPG, JPEG atau WEBP (maks. 2MB)
                                        </p>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            class="absolute inset-0 cursor-pointer opacity-0"
                                            @change="handleImageChange"
                                        />
                                    </div>
                                    <InputError :message="errors.image" />
                                </div>
                            </CardContent>
                            </Card>
                        </Motion>
                    </div>

                    <!-- Sidebar -->
                    <div class="flex flex-col gap-6">
                        <!-- Status Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                        >
                            <Card class="ios-card">
                            <CardHeader>
                                <CardTitle>Status Kategori</CardTitle>
                            </CardHeader>
                            <CardContent class="flex flex-col gap-4">
                                <!-- Is Active -->
                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        id="is_active"
                                        :checked="form.is_active"
                                        @update:checked="form.is_active = $event"
                                    />
                                    <div class="flex flex-col">
                                        <Label for="is_active" class="cursor-pointer">
                                            Kategori Aktif
                                        </Label>
                                        <p class="text-xs text-muted-foreground">
                                            Kategori akan ditampilkan di toko
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                            </Card>
                        </Motion>

                        <!-- Actions Card -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                        >
                            <Card class="ios-card">
                                <CardContent class="flex flex-col gap-3 pt-6">
                                    <Button
                                        type="submit"
                                        class="ios-button w-full gap-2"
                                        :disabled="isSubmitting"
                                    >
                                        <Save class="h-4 w-4" />
                                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="ios-button w-full"
                                        @click="cancel"
                                    >
                                        Batal
                                    </Button>
                                </CardContent>
                            </Card>
                        </Motion>
                    </div>
                </form>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>

    </AppLayout>
</template>
