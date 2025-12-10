<script setup lang="ts">
/**
 * Admin Categories Index Page
 * Menampilkan daftar kategori dengan iOS-style premium design, yaitu:
 * - iOS grouped table dengan category thumbnails
 * - Premium modal form untuk CRUD
 * - Animated rows dengan press feedback
 * - Mobile FAB untuk quick add
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
import InputError from '@/components/InputError.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as categoriesIndex, store, update, destroy } from '@/routes/admin/categories'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
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

defineProps<Props>()
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

// Press state
const pressedRow = ref<number | null>(null)

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
 * Buka dialog konfirmasi delete
 */
function confirmDelete(category: Category) {
    haptic.warning()
    categoryToDelete.value = category
    showDeleteModal.value = true
}

/**
 * Eksekusi delete kategori
 */
function deleteCategory() {
    if (!categoryToDelete.value) return

    haptic.heavy()
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

/**
 * Handle row press
 */
function handleRowPress(categoryId: number) {
    pressedRow.value = categoryId
    haptic.light()
}

/**
 * Handle row release
 */
function handleRowRelease() {
    pressedRow.value = null
}
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

                <!-- Categories Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                >
                    <div class="ios-grouped-table">
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Produk</th>
                                        <th class="text-center">Urutan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <Motion
                                        v-for="(category, index) in categories"
                                        :key="category.id"
                                        tag="tr"
                                        :initial="{ opacity: 0, x: -20 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ ...springPresets.ios, delay: 0.1 + index * 0.03 }"
                                        :class="{ 'scale-[0.995] bg-muted/60': pressedRow === category.id }"
                                        @mousedown="handleRowPress(category.id)"
                                        @mouseup="handleRowRelease"
                                        @mouseleave="handleRowRelease"
                                        @touchstart.passive="handleRowPress(category.id)"
                                        @touchend="handleRowRelease"
                                    >
                                        <!-- Category Info -->
                                        <td>
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
                                                <span class="font-semibold">{{ category.name }}</span>
                                            </div>
                                        </td>

                                        <!-- Description -->
                                        <td>
                                            <span
                                                v-if="category.description"
                                                class="line-clamp-2 text-sm text-muted-foreground"
                                            >
                                                {{ category.description }}
                                            </span>
                                            <span v-else class="text-sm text-muted-foreground">-</span>
                                        </td>

                                        <!-- Products Count -->
                                        <td class="text-center">
                                            <Badge variant="outline" class="gap-1 tabular-nums">
                                                <Package class="h-3 w-3" />
                                                {{ category.products_count }}
                                            </Badge>
                                        </td>

                                        <!-- Sort Order -->
                                        <td class="text-center">
                                            <span class="text-sm tabular-nums">{{ category.sort_order }}</span>
                                        </td>

                                        <!-- Status -->
                                        <td class="text-center">
                                            <span
                                                :class="[
                                                    'admin-badge',
                                                    category.is_active ? 'admin-badge--success' : 'bg-muted text-muted-foreground',
                                                ]"
                                            >
                                                {{ category.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="ios-button h-9 w-9 p-0"
                                                    @click="openEditModal(category)"
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="ios-button h-9 w-9 p-0 text-destructive hover:text-destructive"
                                                    :disabled="category.products_count > 0"
                                                    @click="confirmDelete(category)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </Motion>

                                    <!-- Empty State -->
                                    <tr v-if="categories.length === 0">
                                        <td colspan="6">
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
                        <img :src="imagePreview" alt="Preview" class="!h-28 !w-28" />
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
                            :alt="categoryToEdit?.name"
                            class="h-28 w-28 rounded-xl object-cover"
                        />
                    </div>

                    <!-- Upload Input -->
                    <div class="admin-upload-area !p-6">
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

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteModal">
        <DialogContent class="rounded-2xl">
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
                    class="ios-button"
                    @click="showDeleteModal = false"
                >
                    Batal
                </Button>
                <Button
                    variant="destructive"
                    class="ios-button"
                    :disabled="isDeleting"
                    @click="deleteCategory"
                >
                    {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
