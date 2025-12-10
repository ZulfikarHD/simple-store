<script setup lang="ts">
/**
 * Admin Categories Index Page
 * Menampilkan daftar kategori dengan design yang konsisten dengan Orders, yaitu:
 * - Card view untuk mobile
 * - Tabel data untuk desktop
 * - Modal form untuk CRUD
 * - Password confirmation untuk delete
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
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
import PasswordConfirmDialog from '@/components/admin/PasswordConfirmDialog.vue'
import InputError from '@/components/InputError.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as categoriesIndex, store, update, destroy } from '@/routes/admin/categories'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import SortableHeader from '@/components/admin/SortableHeader.vue'
import {
    FolderTree,
    Plus,
    Pencil,
    Trash2,
    Package,
    Upload,
    X,
    Save,
    Image,
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

/**
 * Type untuk kolom yang dapat di-sort
 */
type SortColumn = 'name' | 'products_count' | 'sort_order' | 'is_active'

/**
 * Sorting state
 */
const sortBy = ref<SortColumn | null>(null)
const sortDirection = ref<'asc' | 'desc'>('asc')

/**
 * Handle sort action dengan toggle direction
 */
function handleSort(column: string) {
    const col = column as SortColumn
    if (sortBy.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = col
        sortDirection.value = 'asc'
    }
}
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
    products_count: number
}

interface Props {
    categories: Category[]
}

const props = defineProps<Props>()
const page = usePage()

/**
 * Computed untuk sorted categories berdasarkan sortBy dan sortDirection
 */
const sortedCategories = computed(() => {
    if (!sortBy.value) return props.categories

    return [...props.categories].sort((a, b) => {
        let comparison = 0
        const col = sortBy.value as SortColumn

        switch (col) {
            case 'name':
                comparison = a.name.localeCompare(b.name, 'id')
                break
            case 'products_count':
                comparison = a.products_count - b.products_count
                break
            case 'sort_order':
                comparison = a.sort_order - b.sort_order
                break
            case 'is_active':
                comparison = (a.is_active ? 1 : 0) - (b.is_active ? 1 : 0)
                break
        }

        return sortDirection.value === 'asc' ? comparison : -comparison
    })
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Kategori', href: categoriesIndex().url },
]

// Flash messages
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
const flashSuccess = computed(() => flash.value?.success)
const flashError = computed(() => flash.value?.error)

// Modal states
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showPasswordDialog = ref(false)
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
 * Reset form
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
 * Buka modal untuk tambah kategori
 */
function openCreateModal() {
    haptic.medium()
    resetForm()
    showFormModal.value = true
}

/**
 * Buka modal untuk edit kategori
 */
function openEditModal(category: Category) {
    haptic.selection()
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
 * Submit form
 */
function submitForm() {
    haptic.medium()
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
        formData.append('_method', 'PUT')
        router.post(update(categoryToEdit.value.id).url, formData, {
            forceFormData: true,
            onSuccess: () => {
                haptic.success()
                showFormModal.value = false
                resetForm()
            },
            onError: (errs) => {
                haptic.error()
                errors.value = errs as Record<string, string>
            },
            onFinish: () => {
                isSubmitting.value = false
            },
        })
    } else {
        router.post(store().url, formData, {
            forceFormData: true,
            onSuccess: () => {
                haptic.success()
                showFormModal.value = false
                resetForm()
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
}

/**
 * Buka dialog konfirmasi delete dengan preview
 */
function confirmDelete(category: Category) {
    haptic.warning()
    categoryToDelete.value = category
    showDeleteModal.value = true
}

/**
 * Lanjut ke verifikasi password
 */
function proceedToPasswordConfirm() {
    showDeleteModal.value = false
    showPasswordDialog.value = true
}

/**
 * Eksekusi delete kategori setelah password terverifikasi
 */
function executeDelete() {
    if (!categoryToDelete.value) return

    haptic.heavy()
    isDeleting.value = true
    router.delete(destroy(categoryToDelete.value.id).url, {
        onSuccess: () => {
            haptic.success()
        },
        onError: () => {
            haptic.error()
        },
        onFinish: () => {
            isDeleting.value = false
            showPasswordDialog.value = false
            categoryToDelete.value = null
        },
    })
}

/**
 * Handle password dialog cancel
 */
function handlePasswordCancel() {
    showPasswordDialog.value = false
    categoryToDelete.value = null
}

/**
 * Get image URL
 */
function getImageUrl(image: string | null | undefined): string | undefined {
    if (!image) return undefined
    return `/storage/${image}`
}

/**
 * Get existing image URL untuk edit
 */
const existingImageUrl = computed(() => {
    if (categoryToEdit.value?.image) {
        return getImageUrl(categoryToEdit.value.image)
    }
    return undefined
})
</script>

<template>
    <Head title="Manajemen Kategori" />

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
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                                Kategori
                            </h1>
                            <Badge variant="secondary" class="tabular-nums">
                                {{ categories.length }} total
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">
                            Kelola kategori produk F&B toko Anda
                        </p>
                    </div>
                    <Button class="admin-btn-primary hidden gap-2 md:flex" @click="openCreateModal">
                        <Plus class="h-4 w-4" />
                        Tambah Kategori
                    </Button>
                </Motion>

                <!-- Flash Messages -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashSuccess"
                        class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        {{ flashSuccess }}
                    </div>
                </Transition>
                <Transition
                    enter-active-class="transition-all duration-300 ease-[var(--ios-spring-smooth)]"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="flashError"
                        class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {{ flashError }}
                    </div>
                </Transition>

                <!-- Mobile Category Cards -->
                <div class="flex flex-col gap-3 md:hidden">
                    <Motion
                        v-for="(category, index) in sortedCategories"
                        :key="category.id"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: 0.1 + index * 0.03 }"
                        class="rounded-2xl border border-border/50 bg-card p-4 shadow-sm"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-muted">
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
                                    <FolderTree class="h-6 w-6 text-muted-foreground" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-lg truncate">{{ category.name }}</h3>
                                <p v-if="category.description" class="text-sm text-muted-foreground line-clamp-1">
                                    {{ category.description }}
                                </p>
                                <div class="flex items-center gap-3 mt-2">
                                                    <span class="ios-badge ios-badge--outline tabular-nums">
                                                        <Package class="h-3 w-3" />
                                                        {{ category.products_count }} produk
                                                    </span>
                                                    <span
                                                        :class="[
                                                            'ios-badge',
                                                            category.is_active ? 'ios-badge--success' : 'ios-badge--muted',
                                                        ]"
                                                    >
                                                        <span class="ios-badge-dot" />
                                                        {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-border/50">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 gap-1.5"
                                @click="openEditModal(category)"
                            >
                                <Pencil class="h-4 w-4" />
                                Edit
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-9 gap-1.5 text-destructive hover:text-destructive"
                                :disabled="category.products_count > 0"
                                @click="confirmDelete(category)"
                            >
                                <Trash2 class="h-4 w-4" />
                                Hapus
                            </Button>
                        </div>
                    </Motion>

                    <!-- Mobile Empty State -->
                    <div v-if="sortedCategories.length === 0" class="admin-form-section">
                        <div class="admin-empty-state">
                            <div class="icon-wrapper">
                                <FolderTree />
                            </div>
                            <h3>Belum Ada Kategori</h3>
                            <p>Mulai tambahkan kategori pertama Anda</p>
                            <Button class="admin-btn-primary gap-2" @click="openCreateModal">
                                <Plus class="h-4 w-4" />
                                Tambah Kategori
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table (same style as Orders) -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="hidden md:block"
                >
                    <div class="ios-table-container">
                        <div class="overflow-x-auto">
                            <table class="ios-table w-full">
                                <thead>
                                    <tr>
                                        <SortableHeader
                                            column="name"
                                            label="Kategori"
                                            :current-sort="sortBy"
                                            :current-direction="sortDirection"
                                            style="width: 280px;"
                                            @sort="handleSort"
                                        />
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="min-width: 200px;">
                                            Deskripsi
                                        </th>
                                        <SortableHeader
                                            column="products_count"
                                            label="Produk"
                                            :current-sort="sortBy"
                                            :current-direction="sortDirection"
                                            align="center"
                                            style="width: 100px;"
                                            @sort="handleSort"
                                        />
                                        <SortableHeader
                                            column="sort_order"
                                            label="Urutan"
                                            :current-sort="sortBy"
                                            :current-direction="sortDirection"
                                            align="center"
                                            style="width: 80px;"
                                            @sort="handleSort"
                                        />
                                        <SortableHeader
                                            column="is_active"
                                            label="Status"
                                            :current-sort="sortBy"
                                            :current-direction="sortDirection"
                                            align="center"
                                            style="width: 100px;"
                                            @sort="handleSort"
                                        />
                                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground" style="width: 160px;">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="category in sortedCategories"
                                        :key="category.id"
                                        class="ios-table-row"
                                    >
                                        <!-- Category Info -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-4">
                                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-muted">
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
                                                        <FolderTree class="h-5 w-5 text-muted-foreground" />
                                                    </div>
                                                </div>
                                                <span class="font-medium text-foreground">{{ category.name }}</span>
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
                                            <span class="ios-badge ios-badge--outline tabular-nums">
                                                <Package class="h-3 w-3" />
                                                {{ category.products_count }}
                                            </span>
                                        </td>

                                        <!-- Sort Order -->
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm tabular-nums text-muted-foreground">{{ category.sort_order }}</span>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                :class="[
                                                    'ios-badge',
                                                    category.is_active ? 'ios-badge--success' : 'ios-badge--muted',
                                                ]"
                                            >
                                                <span class="ios-badge-dot" />
                                                {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-8 gap-1.5"
                                                    @click="openEditModal(category)"
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                    Edit
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-8 gap-1.5 text-destructive hover:text-destructive"
                                                    :disabled="category.products_count > 0"
                                                    @click="confirmDelete(category)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                    Hapus
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr v-if="sortedCategories.length === 0">
                                        <td colspan="6" class="px-4 py-16">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4 rounded-2xl bg-muted p-4">
                                                    <FolderTree class="h-10 w-10 text-muted-foreground/50" />
                                                </div>
                                                <h3 class="text-lg font-semibold">Belum Ada Kategori</h3>
                                                <p class="mt-1 text-sm text-muted-foreground">Mulai tambahkan kategori pertama Anda</p>
                                                <Button class="mt-4 admin-btn-primary gap-2" @click="openCreateModal">
                                                    <Plus class="h-4 w-4" />
                                                    Tambah Kategori
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Motion>

                <!-- Bottom padding untuk mobile nav -->
                <div class="h-24 md:hidden" />
            </div>
        </PullToRefresh>

        <!-- Mobile FAB -->
        <button class="admin-fab md:hidden" @click="openCreateModal">
            <Plus />
        </button>
    </AppLayout>

    <!-- Create/Edit Form Modal -->
    <Dialog v-model:open="showFormModal">
        <DialogContent class="max-h-[90vh] overflow-y-auto rounded-2xl sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Ubah informasi kategori' : 'Isi informasi kategori yang akan ditambahkan' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="flex flex-col gap-5">
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
                        rows="3"
                        placeholder="Masukkan deskripsi kategori"
                        class="admin-textarea"
                        :class="{ 'border-destructive': errors.description }"
                    />
                    <InputError :message="errors.description" />
                </div>

                <!-- Sort Order -->
                <div class="admin-input-group">
                    <Label for="sort_order">Urutan</Label>
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

                <!-- Image Upload -->
                <div class="admin-input-group">
                    <Label>Gambar</Label>

                    <!-- New Image Preview -->
                    <Motion
                        v-if="imagePreview"
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="springPresets.bouncy"
                        class="admin-image-preview"
                    >
                        <img :src="imagePreview" alt="Preview" class="h-28 w-28 rounded-xl object-cover" />
                        <button
                            type="button"
                            class="remove-btn ios-button"
                            @click="removeImage"
                        >
                            <X />
                        </button>
                    </Motion>

                    <!-- Existing Image (Edit mode) -->
                    <div v-else-if="isEditing && existingImageUrl" class="flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Image class="h-4 w-4" />
                            Gambar saat ini
                        </div>
                        <img
                            :src="existingImageUrl"
                            :alt="categoryToEdit?.name ?? undefined"
                            class="h-28 w-28 rounded-xl object-cover"
                        />
                    </div>

                    <!-- Upload Input -->
                    <div class="admin-upload-area">
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
                <div class="flex items-center gap-3 rounded-xl border border-border/50 bg-muted/20 p-4">
                    <Checkbox
                        id="is_active_modal"
                        :checked="form.is_active"
                        @update:checked="form.is_active = $event"
                    />
                    <Label for="is_active_modal" class="cursor-pointer font-medium">
                        Kategori Aktif
                    </Label>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        class="ios-button"
                        @click="showFormModal = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="isSubmitting"
                        class="admin-btn-primary gap-2"
                    >
                        <Save class="h-4 w-4" />
                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog with Preview -->
    <Dialog v-model:open="showDeleteModal">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-destructive">
                    <Trash2 class="h-5 w-5" />
                    Hapus Kategori
                </DialogTitle>
                <DialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Kategori akan dihapus permanen.
                </DialogDescription>
            </DialogHeader>

            <!-- Category Preview -->
            <div v-if="categoryToDelete" class="my-4 rounded-xl border bg-muted/30 p-4">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-muted">
                        <img
                            v-if="categoryToDelete.image"
                            :src="getImageUrl(categoryToDelete.image)"
                            :alt="categoryToDelete.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <FolderTree class="h-6 w-6 text-muted-foreground" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-semibold">{{ categoryToDelete.name }}</h4>
                        <p v-if="categoryToDelete.description" class="text-sm text-muted-foreground line-clamp-1">
                            {{ categoryToDelete.description }}
                        </p>
                        <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                            <Package class="h-3.5 w-3.5" />
                            {{ categoryToDelete.products_count }} produk
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex-col gap-2 sm:flex-row">
                <Button
                    variant="outline"
                    class="w-full sm:w-auto"
                    @click="showDeleteModal = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    class="w-full gap-2 sm:w-auto"
                    @click="proceedToPasswordConfirm"
                >
                    <Trash2 class="h-4 w-4" />
                    Ya, Hapus Kategori
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Password Confirmation Dialog -->
    <PasswordConfirmDialog
        v-model:open="showPasswordDialog"
        title="Konfirmasi Hapus Kategori"
        :description="`Masukkan password untuk menghapus kategori '${categoryToDelete?.name}'`"
        confirm-label="Hapus Permanen"
        confirm-variant="destructive"
        :loading="isDeleting"
        @confirm="executeDelete"
        @cancel="handlePasswordCancel"
    />
</template>
