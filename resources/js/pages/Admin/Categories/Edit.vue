<script setup lang="ts">
/**
 * Admin Categories Edit Page
 * Form premium iOS-style untuk mengedit kategori, yaitu:
 * - Pre-filled data dengan premium form sections
 * - iOS-style image upload dengan existing preview
 * - Elegant toggle switches untuk status
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
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
    ImagePlus,
    CheckCircle,
    Hash,
    Image,
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

// Form state - pre-filled
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
 * Submit form
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
 * Cancel dan kembali
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
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="ios-button h-10 w-10 shrink-0"
                            @click="cancel"
                        >
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                        <div class="flex flex-col gap-1">
                            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                                Edit Kategori
                            </h1>
                            <p class="text-muted-foreground">
                                Ubah informasi "{{ category.name }}"
                            </p>
                        </div>
                    </div>
                </Motion>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <!-- Basic Info -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <FolderTree />
                                        Informasi Kategori
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Name -->
                                        <div class="admin-input-group">
                                            <Label for="name">Nama Kategori *</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                placeholder="Masukkan nama kategori"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.name }"
                                            />
                                            <InputError :message="errors.name" />
                                        </div>

                                        <!-- Description -->
                                        <div class="admin-input-group">
                                            <Label for="description">Deskripsi</Label>
                                            <textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="4"
                                                placeholder="Masukkan deskripsi kategori"
                                                class="admin-textarea"
                                                :class="{ 'border-destructive': errors.description }"
                                            />
                                            <InputError :message="errors.description" />
                                        </div>

                                        <!-- Sort Order -->
                                        <div class="admin-input-group">
                                            <Label for="sort_order" class="flex items-center gap-2">
                                                <Hash class="h-4 w-4 text-primary" />
                                                Urutan
                                            </Label>
                                            <Input
                                                id="sort_order"
                                                v-model="form.sort_order"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.sort_order }"
                                            />
                                            <p class="hint">Angka lebih kecil akan ditampilkan lebih dulu</p>
                                            <InputError :message="errors.sort_order" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Image Upload -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <ImagePlus />
                                        Gambar Kategori
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-4">
                                        <!-- New Image Preview -->
                                        <Motion
                                            v-if="imagePreview"
                                            :initial="{ opacity: 0, scale: 0.9 }"
                                            :animate="{ opacity: 1, scale: 1 }"
                                            :transition="springPresets.bouncy"
                                            class="flex flex-col gap-2"
                                        >
                                            <p class="text-sm font-medium text-green-600 dark:text-green-400">
                                                Gambar Baru (akan menggantikan gambar lama)
                                            </p>
                                            <div class="admin-image-preview">
                                                <img :src="imagePreview" alt="Preview" />
                                                <button
                                                    type="button"
                                                    class="remove-btn ios-button"
                                                    @click="removeNewImage"
                                                >
                                                    <X />
                                                </button>
                                            </div>
                                        </Motion>

                                        <!-- Existing Image -->
                                        <Motion
                                            v-else-if="showExistingImage && existingImageUrl"
                                            :initial="{ opacity: 0 }"
                                            :animate="{ opacity: 1 }"
                                            :transition="springPresets.ios"
                                            class="flex flex-col gap-2"
                                        >
                                            <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                                <Image class="h-4 w-4" />
                                                Gambar Saat Ini
                                            </div>
                                            <div class="admin-image-preview">
                                                <img :src="existingImageUrl" :alt="category.name" />
                                            </div>
                                        </Motion>

                                        <!-- Upload Area -->
                                        <div class="admin-upload-area">
                                            <div class="upload-icon">
                                                <Upload />
                                            </div>
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
                                </div>
                            </div>
                        </Motion>
                    </div>

                    <!-- Sidebar -->
                    <div class="flex flex-col gap-6">
                        <!-- Status -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <CheckCircle />
                                        Status
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex items-start gap-3 rounded-xl border border-border/50 bg-muted/20 p-4">
                                        <Checkbox
                                            id="is_active"
                                            :checked="form.is_active"
                                            @update:checked="form.is_active = $event"
                                        />
                                        <div class="flex flex-col">
                                            <Label for="is_active" class="cursor-pointer font-medium">
                                                Kategori Aktif
                                            </Label>
                                            <p class="text-xs text-muted-foreground">
                                                Kategori akan ditampilkan di toko
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Actions -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-content flex flex-col gap-3">
                                    <Button
                                        type="submit"
                                        class="admin-btn-primary gap-2"
                                        :disabled="isSubmitting"
                                    >
                                        <Save class="h-4 w-4" />
                                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="admin-btn-secondary"
                                        @click="cancel"
                                    >
                                        Batal
                                    </Button>
                                </div>
                            </div>
                        </Motion>
                    </div>
                </form>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-20 md:hidden" />
            </div>
        </PullToRefresh>
    </AppLayout>
</template>
