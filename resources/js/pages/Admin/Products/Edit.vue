<script setup lang="ts">
/**
 * Admin Products Edit Page
 * Form premium iOS-style untuk mengedit produk, yaitu:
 * - Pre-filled data dengan premium form sections
 * - iOS-style image upload dengan existing preview
 * - Elegant toggle switches untuk status
 * - Premium action buttons dengan gradient
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
import { index as productsIndex, update } from '@/routes/admin/products'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    Package,
    Upload,
    X,
    ArrowLeft,
    Save,
    ImagePlus,
    DollarSign,
    Boxes,
    Star,
    CheckCircle,
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
}

interface Product {
    id: number
    name: string
    slug: string
    description: string | null
    price: number
    stock: number
    image: string | null
    is_active: boolean
    is_featured: boolean
    category_id: number
    category: Category | null
}

interface Props {
    product: Product
    categories: Category[]
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Produk', href: productsIndex().url },
    { title: 'Edit Produk', href: '#' },
]

// Form state - pre-filled
const form = ref({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price.toString(),
    stock: props.product.stock.toString(),
    category_id: props.product.category_id.toString(),
    image: null as File | null,
    is_active: props.product.is_active,
    is_featured: props.product.is_featured,
})

// Errors
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

// Image preview
const imagePreview = ref<string | null>(null)
const hasExistingImage = computed(() => !!props.product.image)
const showExistingImage = ref(true)

/**
 * Get existing image URL
 */
const existingImageUrl = computed(() => {
    if (props.product.image) {
        return `/storage/${props.product.image}`
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
    formData.append('price', form.value.price)
    formData.append('stock', form.value.stock)
    formData.append('category_id', form.value.category_id)
    formData.append('is_active', form.value.is_active ? '1' : '0')
    formData.append('is_featured', form.value.is_featured ? '1' : '0')

    if (form.value.image) {
        formData.append('image', form.value.image)
    }

    router.post(update(props.product.id).url, formData, {
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
    router.get(productsIndex().url)
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

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
                                Edit Produk
                            </h1>
                            <p class="text-muted-foreground">
                                Ubah informasi "{{ product.name }}"
                            </p>
                        </div>
                    </div>
                </Motion>

                <!-- Form -->
                <form id="product-form" @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
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
                                        <Package />
                                        Informasi Produk
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Name -->
                                        <div class="admin-input-group">
                                            <Label for="name">Nama Produk *</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                placeholder="Masukkan nama produk"
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
                                                placeholder="Masukkan deskripsi produk"
                                                class="admin-textarea"
                                                :class="{ 'border-destructive': errors.description }"
                                            />
                                            <InputError :message="errors.description" />
                                        </div>

                                        <!-- Price & Stock -->
                                        <div class="grid gap-5 sm:grid-cols-2">
                                            <div class="admin-input-group">
                                                <Label for="price" class="flex items-center gap-2">
                                                    <DollarSign class="h-4 w-4 text-primary" />
                                                    Harga (Rp) *
                                                </Label>
                                                <Input
                                                    id="price"
                                                    v-model="form.price"
                                                    type="number"
                                                    min="0"
                                                    placeholder="0"
                                                    class="admin-input"
                                                    :class="{ 'border-destructive': errors.price }"
                                                />
                                                <InputError :message="errors.price" />
                                            </div>
                                            <div class="admin-input-group">
                                                <Label for="stock" class="flex items-center gap-2">
                                                    <Boxes class="h-4 w-4 text-primary" />
                                                    Stok *
                                                </Label>
                                                <Input
                                                    id="stock"
                                                    v-model="form.stock"
                                                    type="number"
                                                    min="0"
                                                    placeholder="0"
                                                    class="admin-input"
                                                    :class="{ 'border-destructive': errors.stock }"
                                                />
                                                <InputError :message="errors.stock" />
                                            </div>
                                        </div>

                                        <!-- Category -->
                                        <div class="admin-input-group">
                                            <Label for="category_id">Kategori *</Label>
                                            <select
                                                id="category_id"
                                                v-model="form.category_id"
                                                class="admin-select"
                                                :class="{ 'border-destructive': errors.category_id }"
                                            >
                                                <option value="" disabled>Pilih kategori</option>
                                                <option
                                                    v-for="category in categories"
                                                    :key="category.id"
                                                    :value="category.id"
                                                >
                                                    {{ category.name }}
                                                </option>
                                            </select>
                                            <InputError :message="errors.category_id" />
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
                                        Gambar Produk
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
                                                <img :src="existingImageUrl" :alt="product.name" />
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
                                        Status Produk
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-4">
                                        <!-- Is Active -->
                                        <div class="flex items-start gap-3 rounded-xl border border-border/50 bg-muted/20 p-4">
                                            <Checkbox
                                                id="is_active"
                                                :checked="form.is_active"
                                                @update:checked="form.is_active = $event"
                                            />
                                            <div class="flex flex-col">
                                                <Label for="is_active" class="cursor-pointer font-medium">
                                                    Produk Aktif
                                                </Label>
                                                <p class="text-xs text-muted-foreground">
                                                    Produk akan ditampilkan di toko
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Is Featured -->
                                        <div class="flex items-start gap-3 rounded-xl border border-border/50 bg-muted/20 p-4">
                                            <Checkbox
                                                id="is_featured"
                                                :checked="form.is_featured"
                                                @update:checked="form.is_featured = $event"
                                            />
                                            <div class="flex flex-col">
                                                <Label for="is_featured" class="flex cursor-pointer items-center gap-2 font-medium">
                                                    <Star class="h-4 w-4 text-amber-500" />
                                                    Produk Unggulan
                                                </Label>
                                                <p class="text-xs text-muted-foreground">
                                                    Tampilkan di bagian featured
                                                </p>
                                            </div>
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

                <!-- Bottom padding untuk mobile nav + sticky bar -->
                <div class="h-36 md:hidden" />
            </div>
        </PullToRefresh>

        <!-- Mobile Sticky Submit Bar -->
        <div
            class="fixed inset-x-0 bottom-12 z-40 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 md:hidden"
            style="padding-bottom: env(safe-area-inset-bottom)"
        >
            <div class="flex gap-3 p-4">
                <Button
                    type="button"
                    variant="outline"
                    class="h-12 flex-1"
                    @click="cancel"
                >
                    Batal
                </Button>
                <Button
                    type="submit"
                    form="product-form"
                    class="h-12 flex-1 gap-2"
                    :disabled="isSubmitting"
                    @click="submitForm"
                >
                    <Save class="h-4 w-4" />
                    {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
