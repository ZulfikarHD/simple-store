<script setup lang="ts">
/**
 * Admin Categories Index Page
 * Menampilkan daftar kategori dengan fitur CRUD, yaitu:
 * - Tabel data kategori dengan kolom nama, deskripsi, jumlah produk, status
 * - Modal form untuk tambah dan edit kategori
 * - Delete dengan confirmation dialog
 * - Tampilan product count per kategori
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import InputError from '@/components/InputError.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as categoriesIndex, store, update, destroy } from '@/routes/admin/categories'
import {
    FolderTree,
    Plus,
    Pencil,
    Trash2,
    Package,
    ImageOff,
    Upload,
    X,
    Save,
} from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

interface Category {
    id: number
    name: string
    slug: string
    description: string | null
    image: string | null
    is_active: boolean
    sort_order: number
    products_count: number
}

interface Props {
    categories: Category[]
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Kategori', href: categoriesIndex().url },
]

// Flash messages
const flashSuccess = computed(() => page.props.flash?.success as string | undefined)
const flashError = computed(() => page.props.flash?.error as string | undefined)

// Modal states
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const categoryToEdit = ref<Category | null>(null)
const categoryToDelete = ref<Category | null>(null)

// Form state
const form = ref({
    name: '',
    description: '',
    image: null as File | null,
    is_active: true,
    sort_order: '',
})

// Form errors & loading
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)
const isDeleting = ref(false)

// Image preview
const imagePreview = ref<string | null>(null)

/**
 * Reset form ke default values
 */
function resetForm() {
    form.value = {
        name: '',
        description: '',
        image: null,
        is_active: true,
        sort_order: '',
    }
    errors.value = {}
    imagePreview.value = null
    categoryToEdit.value = null
    isEditing.value = false
}

/**
 * Buka modal untuk tambah kategori baru
 */
function openCreateModal() {
    resetForm()
    showFormModal.value = true
}

/**
 * Buka modal untuk edit kategori
 */
function openEditModal(category: Category) {
    resetForm()
    isEditing.value = true
    categoryToEdit.value = category
    form.value = {
        name: category.name,
        description: category.description || '',
        image: null,
        is_active: category.is_active,
        sort_order: category.sort_order.toString(),
    }
    showFormModal.value = true
}

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
 * Submit form (create atau update)
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

    if (isEditing.value && categoryToEdit.value) {
        // Update
        formData.append('_method', 'PUT')
        router.post(update(categoryToEdit.value.id).url, formData, {
            forceFormData: true,
            onSuccess: () => {
                showFormModal.value = false
                resetForm()
            },
            onError: (errs) => {
                errors.value = errs as Record<string, string>
            },
            onFinish: () => {
                isSubmitting.value = false
            },
        })
    } else {
        // Create
        router.post(store().url, formData, {
            forceFormData: true,
            onSuccess: () => {
                showFormModal.value = false
                resetForm()
            },
            onError: (errs) => {
                errors.value = errs as Record<string, string>
            },
            onFinish: () => {
                isSubmitting.value = false
            },
        })
    }
}

/**
 * Buka dialog konfirmasi delete
 */
function confirmDelete(category: Category) {
    categoryToDelete.value = category
    showDeleteModal.value = true
}

/**
 * Eksekusi delete kategori
 */
function deleteCategory() {
    if (!categoryToDelete.value) return

    isDeleting.value = true
    router.delete(destroy(categoryToDelete.value.id).url, {
        onFinish: () => {
            isDeleting.value = false
            showDeleteModal.value = false
            categoryToDelete.value = null
        },
    })
}

/**
 * Get image URL
 */
function getImageUrl(image: string | null): string | null {
    if (!image) return null
    return `/storage/${image}`
}

/**
 * Get existing image URL untuk edit
 */
const existingImageUrl = computed(() => {
    if (categoryToEdit.value?.image) {
        return getImageUrl(categoryToEdit.value.image)
    }
    return null
})
</script>

<template>
    <Head title="Manajemen Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Manajemen Kategori
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola kategori produk F&B toko Anda
                    </p>
                </div>
                <Button class="gap-2" @click="openCreateModal">
                    <Plus class="h-4 w-4" />
                    Tambah Kategori
                </Button>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="flashSuccess"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
            >
                {{ flashSuccess }}
            </div>
            <div
                v-if="flashError"
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
            >
                {{ flashError }}
            </div>

            <!-- Categories Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Kategori
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Deskripsi
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Jumlah Produk
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Urutan
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="transition-colors hover:bg-muted/50"
                                >
                                    <!-- Category Info -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-muted">
                                                <img
                                                    v-if="category.image"
                                                    :src="getImageUrl(category.image)"
                                                    :alt="category.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-full w-full items-center justify-center"
                                                >
                                                    <FolderTree class="h-4 w-4 text-muted-foreground" />
                                                </div>
                                            </div>
                                            <span class="font-medium">{{ category.name }}</span>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="category.description"
                                            class="line-clamp-2 text-sm text-muted-foreground"
                                        >
                                            {{ category.description }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </td>

                                    <!-- Products Count -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge variant="secondary" class="gap-1">
                                            <Package class="h-3 w-3" />
                                            {{ category.products_count }}
                                        </Badge>
                                    </td>

                                    <!-- Sort Order -->
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm">{{ category.sort_order }}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        <Badge
                                            :variant="category.is_active ? 'default' : 'outline'"
                                            :class="category.is_active ? 'bg-green-600' : ''"
                                        >
                                            {{ category.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </Badge>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0"
                                                @click="openEditModal(category)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                :disabled="category.products_count > 0"
                                                @click="confirmDelete(category)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="categories.length === 0">
                                    <td colspan="6" class="px-4 py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <FolderTree class="mb-4 h-12 w-12 text-muted-foreground/50" />
                                            <p class="text-lg font-medium text-muted-foreground">
                                                Belum ada kategori
                                            </p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Mulai tambahkan kategori pertama Anda
                                            </p>
                                            <Button class="mt-4" @click="openCreateModal">
                                                <Plus class="mr-2 h-4 w-4" />
                                                Tambah Kategori
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Create/Edit Form Modal -->
    <Dialog v-model:open="showFormModal">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Ubah informasi kategori' : 'Isi informasi kategori yang akan ditambahkan' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="flex flex-col gap-4">
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
                        rows="3"
                        placeholder="Masukkan deskripsi kategori"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
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
                    <InputError :message="errors.sort_order" />
                </div>

                <!-- Image Upload -->
                <div class="flex flex-col gap-2">
                    <Label>Gambar</Label>

                    <!-- New Image Preview -->
                    <div v-if="imagePreview" class="relative inline-block">
                        <img
                            :src="imagePreview"
                            alt="Preview"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                        <button
                            type="button"
                            class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-white shadow"
                            @click="removeImage"
                        >
                            <X class="h-3 w-3" />
                        </button>
                    </div>

                    <!-- Existing Image (Edit mode) -->
                    <div v-else-if="isEditing && existingImageUrl" class="flex flex-col gap-2">
                        <img
                            :src="existingImageUrl"
                            :alt="categoryToEdit?.name"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                        <p class="text-xs text-muted-foreground">Gambar saat ini</p>
                    </div>

                    <!-- Upload Input -->
                    <div class="relative flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-4">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Upload class="h-4 w-4" />
                            <span>Pilih gambar</span>
                        </div>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="absolute inset-0 cursor-pointer opacity-0"
                            @change="handleImageChange"
                        />
                    </div>
                    <InputError :message="errors.image" />
                </div>

                <!-- Is Active -->
                <div class="flex items-center gap-3">
                    <Checkbox
                        id="is_active_modal"
                        :checked="form.is_active"
                        @update:checked="form.is_active = $event"
                    />
                    <Label for="is_active_modal" class="cursor-pointer">
                        Kategori Aktif
                    </Label>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="showFormModal = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="isSubmitting"
                        class="gap-2"
                    >
                        <Save class="h-4 w-4" />
                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteModal">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Hapus Kategori</DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin menghapus kategori "{{ categoryToDelete?.name }}"?
                    Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="showDeleteModal = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    :disabled="isDeleting"
                    @click="deleteCategory"
                >
                    {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

