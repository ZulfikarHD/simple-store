<script setup lang="ts">
/**
 * Admin Products Edit Page
 * Form untuk mengedit produk yang sudah ada dengan fitur, yaitu:
 * - Pre-filled data dari produk yang dipilih
 * - Image upload dengan preview (bisa ganti atau retain existing)
 * - Toggle status aktif dan featured
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import InputError from '@/components/InputError.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as productsIndex, update } from '@/routes/admin/products'
import {
    Package,
    Upload,
    X,
    ArrowLeft,
    Save,
    ImageOff,
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

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

// Form state - pre-filled dengan data produk
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
    form.value.image = null
    imagePreview.value = null
    showExistingImage.value = hasExistingImage.value
}

/**
 * Submit form untuk update produk
 */
function submitForm() {
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
        onError: (errs) => {
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
    router.get(productsIndex().url)
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Edit Produk
                    </h1>
                    <p class="text-muted-foreground">
                        Ubah informasi produk "{{ product.name }}"
                    </p>
                </div>
                <Button
                    variant="outline"
                    class="gap-2"
                    @click="cancel"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 flex flex-col gap-6">
                    <!-- Basic Info Card -->
                    <Card>
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
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
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
                                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
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

                    <!-- Image Upload Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Upload class="h-5 w-5" />
                                Gambar Produk
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <!-- New Image Preview -->
                                <div
                                    v-if="imagePreview"
                                    class="relative inline-block"
                                >
                                    <img
                                        :src="imagePreview"
                                        alt="Preview"
                                        class="h-48 w-48 rounded-lg object-cover"
                                    />
                                    <button
                                        type="button"
                                        class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-white shadow"
                                        @click="removeNewImage"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                    <p class="mt-2 text-xs text-muted-foreground">
                                        Gambar baru (akan menggantikan gambar lama)
                                    </p>
                                </div>

                                <!-- Existing Image -->
                                <div
                                    v-else-if="showExistingImage && existingImageUrl"
                                    class="flex flex-col gap-2"
                                >
                                    <p class="text-sm font-medium">Gambar Saat Ini</p>
                                    <img
                                        :src="existingImageUrl"
                                        :alt="product.name"
                                        class="h-48 w-48 rounded-lg object-cover"
                                    />
                                </div>

                                <!-- Upload Input -->
                                <div
                                    class="relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-8"
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
                </div>

                <!-- Sidebar -->
                <div class="flex flex-col gap-6">
                    <!-- Status Card -->
                    <Card>
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

                    <!-- Actions Card -->
                    <Card>
                        <CardContent class="flex flex-col gap-3 pt-6">
                            <Button
                                type="submit"
                                class="w-full gap-2"
                                :disabled="isSubmitting"
                            >
                                <Save class="h-4 w-4" />
                                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                @click="cancel"
                            >
                                Batal
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

