<script setup lang="ts">
/**
 * Admin Categories Create Page
 * Form untuk menambahkan kategori baru dengan fitur, yaitu:
 * - Input nama dan deskripsi
 * - Image upload dengan preview
 * - Toggle status aktif
 * - Input urutan (sort_order)
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
import { index as categoriesIndex, store } from '@/routes/admin/categories'
import {
    FolderTree,
    Upload,
    X,
    ArrowLeft,
    Save,
} from 'lucide-vue-next'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Kategori', href: categoriesIndex().url },
    { title: 'Tambah Kategori', href: '#' },
]

// Form state
const form = ref({
    name: '',
    description: '',
    image: null as File | null,
    is_active: true,
    sort_order: '',
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
    form.value.image = null
    imagePreview.value = null
}

/**
 * Submit form untuk membuat kategori baru
 */
function submitForm() {
    isSubmitting.value = true
    errors.value = {}

    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description || '')
    formData.append('is_active', form.value.is_active ? '1' : '0')
    if (form.value.sort_order) {
        formData.append('sort_order', form.value.sort_order)
    }

    if (form.value.image) {
        formData.append('image', form.value.image)
    }

    router.post(store().url, formData, {
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
 * Cancel dan kembali ke daftar kategori
 */
function cancel() {
    router.get(categoriesIndex().url)
}
</script>

<template>
    <Head title="Tambah Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Tambah Kategori Baru
                    </h1>
                    <p class="text-muted-foreground">
                        Isi informasi kategori yang akan ditambahkan
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
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
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
                                    :class="{ 'border-destructive': errors.sort_order }"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Angka lebih kecil akan ditampilkan lebih dulu
                                </p>
                                <InputError :message="errors.sort_order" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Image Upload Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Upload class="h-5 w-5" />
                                Gambar Kategori
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <!-- Image Preview -->
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
                                        @click="removeImage"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Upload Input -->
                                <div
                                    v-else
                                    class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-8"
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
                </div>

                <!-- Sidebar -->
                <div class="flex flex-col gap-6">
                    <!-- Status Card -->
                    <Card>
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

                    <!-- Actions Card -->
                    <Card>
                        <CardContent class="flex flex-col gap-3 pt-6">
                            <Button
                                type="submit"
                                class="w-full gap-2"
                                :disabled="isSubmitting"
                            >
                                <Save class="h-4 w-4" />
                                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Kategori' }}
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

