<script setup lang="ts">
/**
 * Admin Products Create Page
 * Form untuk menambahkan produk baru dengan fitur, yaitu:
 * - Input nama, deskripsi, harga, stok
 * - Dropdown pemilihan kategori
 * - Image upload dengan preview
 * - Toggle status aktif dan featured
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
import { index as productsIndex, store } from '@/routes/admin/products'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    Package,
    Upload,
    X,
    ArrowLeft,
    Save,
} from 'lucide-vue-next'
import { ref } from 'vue'
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

interface Props {
    categories: Category[]
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Produk', href: productsIndex().url },
    { title: 'Tambah Produk', href: '#' },
]

// Form state
const form = ref({
    name: '',
    description: '',
    price: '',
    stock: '',
    category_id: '',
    image: null as File | null,
    is_active: true,
    is_featured: false,
})

// Errors
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

// Image preview
const imagePreview = ref<string | null>(null)

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
    }
}

/**
 * Remove selected image
 */
function removeImage() {
    haptic.light()
    form.value.image = null
    imagePreview.value = null
}

/**
 * Submit form untuk membuat produk baru
 */
function submitForm() {
    haptic.medium()
    isSubmitting.value = true
    errors.value = {}

    const formData = new FormData()
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

    router.post(store().url, formData, {
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
 * Cancel dan kembali ke daftar produk
 */
function cancel() {
    haptic.light()
    router.get(productsIndex().url)
}
</script>

<template>
    <Head title="Tambah Produk" />

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
                            Tambah Produk Baru
                        </h1>
                        <p class="text-muted-foreground">
                            Isi informasi produk yang akan ditambahkan
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
                                    <Package class="h-5 w-5" />
                                    Informasi Produk
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="flex flex-col gap-4">
                                <!-- Name -->
                                <div class="flex flex-col gap-2">
                                    <Label for="name">Nama Produk *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Masukkan nama produk"
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
                                        placeholder="Masukkan deskripsi produk"
                                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="{ 'border-destructive': errors.description }"
                                    ></textarea>
                                    <InputError :message="errors.description" />
                                </div>

                                <!-- Price & Stock -->
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <Label for="price">Harga (Rp) *</Label>
                                        <Input
                                            id="price"
                                            v-model="form.price"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                            class="ios-input"
                                            :class="{ 'border-destructive': errors.price }"
                                        />
                                        <InputError :message="errors.price" />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label for="stock">Stok *</Label>
                                        <Input
                                            id="stock"
                                            v-model="form.stock"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                            class="ios-input"
                                            :class="{ 'border-destructive': errors.stock }"
                                        />
                                        <InputError :message="errors.stock" />
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="flex flex-col gap-2">
                                    <Label for="category_id">Kategori *</Label>
                                    <select
                                        id="category_id"
                                        v-model="form.category_id"
                                        class="h-10 w-full rounded-xl border border-input bg-background px-3 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring"
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
                                    Gambar Produk
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-col gap-4">
                                    <!-- Image Preview -->
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
                                            @click="removeImage"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </Motion>

                                    <!-- Upload Input -->
                                    <div
                                        v-else
                                        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-muted-foreground/50"
                                    >
                                        <Upload class="mb-4 h-10 w-10 text-muted-foreground" />
                                        <p class="mb-2 text-sm font-medium">
                                            Klik atau drag file untuk upload
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
                                <CardTitle>Status Produk</CardTitle>
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
                                            Produk Aktif
                                        </Label>
                                        <p class="text-xs text-muted-foreground">
                                            Produk akan ditampilkan di toko
                                        </p>
                                    </div>
                                </div>

                                <!-- Is Featured -->
                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        id="is_featured"
                                        :checked="form.is_featured"
                                        @update:checked="form.is_featured = $event"
                                    />
                                    <div class="flex flex-col">
                                        <Label for="is_featured" class="cursor-pointer">
                                            Produk Unggulan
                                        </Label>
                                        <p class="text-xs text-muted-foreground">
                                            Tampilkan di bagian featured
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
                                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Produk' }}
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
