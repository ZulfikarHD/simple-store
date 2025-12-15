<script setup lang="ts">
/**
 * Admin Store Settings Page
 * Halaman pengaturan toko dengan iOS-style premium design, yaitu:
 * - Premium form sections untuk informasi toko
 * - iOS-style WhatsApp configuration
 * - Elegant jam operasional dengan toggles
 * - Premium delivery settings dengan area tags
 *
 * @author Zulfikar Hidayatullah
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import InputError from '@/components/InputError.vue'
import PullToRefresh from '@/components/mobile/PullToRefresh.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { update } from '@/routes/admin/settings'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import {
    Store,
    MessageSquare,
    Clock,
    Truck,
    Save,
    Plus,
    X,
    MapPin,
    Phone,
    Building2,
    Wallet,
    ShoppingBag,
    Timer,
    Ban,
    ImagePlus,
    Tag,
    Trash2,
} from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { COUNTRY_CONFIGS, getWhatsAppInputStatus, validateWhatsAppNumber } from '@/composables/usePhoneFormat'

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Interface untuk struktur jam operasional per hari
 */
interface DayHours {
    open: string
    close: string
    is_open: boolean
}

/**
 * Interface untuk semua jam operasional dalam seminggu
 */
interface OperatingHours {
    monday: DayHours
    tuesday: DayHours
    wednesday: DayHours
    thursday: DayHours
    friday: DayHours
    saturday: DayHours
    sunday: DayHours
}

/**
 * Interface untuk props settings dari backend
 */
interface Settings {
    store_name: string
    store_tagline: string
    store_logo: string | null
    store_favicon: string | null
    store_address: string
    store_phone: string
    whatsapp_number: string
    phone_country_code: string
    operating_hours: OperatingHours
    delivery_areas: string[]
    delivery_fee: number
    minimum_order: number
    auto_cancel_enabled: boolean
    auto_cancel_minutes: number
}

/**
 * Daftar negara yang didukung untuk dropdown selection
 * dengan format { code, name, dialCode }
 */
const supportedCountries = Object.values(COUNTRY_CONFIGS).map(config => ({
    code: config.code,
    name: config.name,
    dialCode: `+${config.dialCode}`,
}))

interface Props {
    settings: Settings
}

const props = defineProps<Props>()
const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pengaturan Toko', href: '#' },
]

// Flash messages dari session
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success)
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error)

// Label hari dalam Bahasa Indonesia
const dayLabels: Record<string, string> = {
    monday: 'Senin',
    tuesday: 'Selasa',
    wednesday: 'Rabu',
    thursday: 'Kamis',
    friday: 'Jumat',
    saturday: 'Sabtu',
    sunday: 'Minggu',
}

// Form state dengan deep copy dari props
const form = ref<Settings>({
    store_name: props.settings.store_name || '',
    store_tagline: props.settings.store_tagline || '',
    store_logo: props.settings.store_logo || null,
    store_favicon: props.settings.store_favicon || null,
    store_address: props.settings.store_address || '',
    store_phone: props.settings.store_phone || '',
    whatsapp_number: props.settings.whatsapp_number || '',
    phone_country_code: props.settings.phone_country_code || 'ID',
    operating_hours: props.settings.operating_hours || {
        monday: { open: '08:00', close: '21:00', is_open: true },
        tuesday: { open: '08:00', close: '21:00', is_open: true },
        wednesday: { open: '08:00', close: '21:00', is_open: true },
        thursday: { open: '08:00', close: '21:00', is_open: true },
        friday: { open: '08:00', close: '21:00', is_open: true },
        saturday: { open: '09:00', close: '22:00', is_open: true },
        sunday: { open: '09:00', close: '20:00', is_open: true },
    },
    delivery_areas: props.settings.delivery_areas || [],
    delivery_fee: props.settings.delivery_fee || 0,
    minimum_order: props.settings.minimum_order || 0,
    auto_cancel_enabled: props.settings.auto_cancel_enabled ?? true,
    auto_cancel_minutes: props.settings.auto_cancel_minutes || 30,
})

// New delivery area input
const newArea = ref('')

// Logo upload refs
const logoInput = ref<HTMLInputElement | null>(null)
const logoPreview = ref<string | null>(props.settings.store_logo ? `/storage/${props.settings.store_logo}` : null)
const isUploadingLogo = ref(false)

// Favicon upload refs
const faviconInput = ref<HTMLInputElement | null>(null)
const faviconPreview = ref<string | null>(props.settings.store_favicon ? `/storage/${props.settings.store_favicon}` : null)
const isUploadingFavicon = ref(false)

// Errors
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

/**
 * Computed untuk real-time WhatsApp number validation status
 */
const whatsappInputStatus = computed(() => {
    return getWhatsAppInputStatus(form.value.whatsapp_number, form.value.phone_country_code)
})

/**
 * Trigger file input untuk logo upload
 */
function triggerLogoUpload() {
    logoInput.value?.click()
}

/**
 * Get CSRF token dari meta tag
 * Laravel Inertia menyediakan CSRF token melalui meta tag di head
 */
function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

/**
 * Handle logo file selection dan upload
 */
async function handleLogoUpload(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    // Validasi file type
    if (!file.type.startsWith('image/')) {
        errors.value.store_logo = 'File harus berupa gambar'
        haptic.error()
        return
    }

    // Validasi file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        errors.value.store_logo = 'Ukuran file maksimal 2MB'
        haptic.error()
        return
    }

    isUploadingLogo.value = true
    haptic.selection()

    try {
        const formData = new FormData()
        formData.append('logo', file)

        const response = await fetch('/admin/settings/upload-logo', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })

        // Handle 419 CSRF token mismatch - refresh halaman untuk mendapatkan token baru
        if (response.status === 419) {
            errors.value.store_logo = 'Sesi telah berakhir. Halaman akan di-refresh...'
            haptic.error()
            setTimeout(() => {
                window.location.reload()
            }, 1500)
            return
        }

        const data = await response.json()

        if (data.success) {
            form.value.store_logo = data.path
            logoPreview.value = `/storage/${data.path}`
            delete errors.value.store_logo
            haptic.success()
        } else {
            errors.value.store_logo = data.message || 'Gagal mengupload logo'
            haptic.error()
        }
    } catch {
        errors.value.store_logo = 'Gagal mengupload logo'
        haptic.error()
    } finally {
        isUploadingLogo.value = false
        // Reset input
        if (target) target.value = ''
    }
}

/**
 * Hapus logo yang sudah diupload
 */
function removeLogo() {
    haptic.light()
    form.value.store_logo = null
    logoPreview.value = null
}

/**
 * Trigger file input untuk favicon upload
 */
function triggerFaviconUpload() {
    faviconInput.value?.click()
}

/**
 * Handle favicon file selection dan upload
 */
async function handleFaviconUpload(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    // Validasi file type
    const allowedTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/x-icon', 'image/svg+xml']
    if (!allowedTypes.includes(file.type)) {
        errors.value.store_favicon = 'File harus berupa gambar (PNG, JPG, WebP, ICO, atau SVG)'
        haptic.error()
        return
    }

    // Validasi file size (max 1MB)
    if (file.size > 1 * 1024 * 1024) {
        errors.value.store_favicon = 'Ukuran file maksimal 1MB'
        haptic.error()
        return
    }

    isUploadingFavicon.value = true
    haptic.selection()

    try {
        const formData = new FormData()
        formData.append('favicon', file)

        const response = await fetch('/admin/settings/upload-favicon', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })

        // Handle 419 CSRF token mismatch - refresh halaman untuk mendapatkan token baru
        if (response.status === 419) {
            errors.value.store_favicon = 'Sesi telah berakhir. Halaman akan di-refresh...'
            haptic.error()
            setTimeout(() => {
                window.location.reload()
            }, 1500)
            return
        }

        const data = await response.json()

        if (data.success) {
            form.value.store_favicon = data.path
            faviconPreview.value = `/storage/${data.path}`
            delete errors.value.store_favicon
            haptic.success()
        } else {
            errors.value.store_favicon = data.message || 'Gagal mengupload favicon'
            haptic.error()
        }
    } catch {
        errors.value.store_favicon = 'Gagal mengupload favicon'
        haptic.error()
    } finally {
        isUploadingFavicon.value = false
        // Reset input
        if (target) target.value = ''
    }
}

/**
 * Hapus favicon yang sudah diupload
 */
function removeFavicon() {
    haptic.light()
    form.value.store_favicon = null
    faviconPreview.value = null
}

/**
 * Tambah area pengiriman baru
 */
function addDeliveryArea() {
    if (newArea.value.trim() && !form.value.delivery_areas.includes(newArea.value.trim())) {
        haptic.selection()
        form.value.delivery_areas.push(newArea.value.trim())
        newArea.value = ''
    }
}

/**
 * Hapus area pengiriman berdasarkan index
 */
function removeDeliveryArea(index: number) {
    haptic.light()
    form.value.delivery_areas.splice(index, 1)
}

/**
 * Validasi form sebelum submit
 * @returns true jika valid, false jika ada error
 */
function validateForm(): boolean {
    const newErrors: Record<string, string> = {}

    // Validasi WhatsApp number
    const waValidation = validateWhatsAppNumber(form.value.whatsapp_number, form.value.phone_country_code)
    if (!waValidation.isValid) {
        newErrors.whatsapp_number = waValidation.error || 'Format nomor WhatsApp tidak valid'
    }

    // Set errors jika ada
    if (Object.keys(newErrors).length > 0) {
        errors.value = newErrors
        haptic.error()
        return false
    }

    return true
}

/**
 * Submit form untuk menyimpan pengaturan toko
 */
function submitForm() {
    haptic.medium()

    // Validasi frontend terlebih dahulu
    if (!validateForm()) {
        return
    }

    isSubmitting.value = true
    errors.value = {}

    router.patch(update().url, form.value, {
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
</script>

<template>
    <Head title="Pengaturan Toko" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PullToRefresh>
            <div class="admin-page flex flex-col gap-6 p-4 md:p-6">
                <!-- Page Header -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                    class="flex flex-col gap-1"
                >
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Pengaturan Toko
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola informasi dan konfigurasi toko Anda
                    </p>
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

                <!-- Form -->
                <form @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <!-- Store Information -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <Building2 />
                                        Informasi Toko
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Store Logo -->
                                        <div class="admin-input-group">
                                            <Label class="flex items-center gap-2">
                                                <ImagePlus class="h-4 w-4 text-primary" />
                                                Logo Toko
                                            </Label>
                                            <div class="flex items-center gap-4">
                                                <!-- Logo Preview -->
                                                <div
                                                    class="relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-border bg-muted/30"
                                                    :class="{ 'border-primary': isUploadingLogo }"
                                                >
                                                    <img
                                                        v-if="logoPreview"
                                                        :src="logoPreview"
                                                        alt="Logo Preview"
                                                        class="h-full w-full object-contain"
                                                    />
                                                    <ShoppingBag v-else class="h-8 w-8 text-muted-foreground" />
                                                    <!-- Loading overlay -->
                                                    <div
                                                        v-if="isUploadingLogo"
                                                        class="absolute inset-0 flex items-center justify-center bg-background/80"
                                                    >
                                                        <div class="h-5 w-5 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                                                    </div>
                                                </div>
                                                <!-- Upload/Remove buttons -->
                                                <div class="flex flex-col gap-2">
                                                    <input
                                                        ref="logoInput"
                                                        type="file"
                                                        accept="image/*"
                                                        class="hidden"
                                                        @change="handleLogoUpload"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="ios-button gap-2"
                                                        :disabled="isUploadingLogo"
                                                        @click="triggerLogoUpload"
                                                    >
                                                        <ImagePlus class="h-4 w-4" />
                                                        {{ logoPreview ? 'Ganti Logo' : 'Upload Logo' }}
                                                    </Button>
                                                    <Button
                                                        v-if="logoPreview"
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="ios-button gap-2 text-destructive hover:text-destructive"
                                                        @click="removeLogo"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                        Hapus Logo
                                                    </Button>
                                                </div>
                                            </div>
                                            <p class="hint">Format: JPG, PNG, atau WebP. Maksimal 2MB. Rekomendasi: 200x200px</p>
                                            <InputError :message="errors.store_logo" />
                                        </div>

                                        <!-- Store Favicon -->
                                        <div class="admin-input-group">
                                            <Label class="flex items-center gap-2">
                                                <ImagePlus class="h-4 w-4 text-primary" />
                                                Favicon Toko
                                            </Label>
                                            <div class="flex items-center gap-4">
                                                <!-- Favicon Preview -->
                                                <div
                                                    class="relative flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-border bg-muted/30"
                                                    :class="{ 'border-primary': isUploadingFavicon }"
                                                >
                                                    <img
                                                        v-if="faviconPreview"
                                                        :src="faviconPreview"
                                                        alt="Favicon Preview"
                                                        class="h-full w-full object-contain"
                                                    />
                                                    <svg v-else class="h-6 w-6 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                                        <circle cx="12" cy="12" r="3" />
                                                    </svg>
                                                    <!-- Loading overlay -->
                                                    <div
                                                        v-if="isUploadingFavicon"
                                                        class="absolute inset-0 flex items-center justify-center bg-background/80"
                                                    >
                                                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                                                    </div>
                                                </div>
                                                <!-- Upload/Remove buttons -->
                                                <div class="flex flex-col gap-2">
                                                    <input
                                                        ref="faviconInput"
                                                        type="file"
                                                        accept="image/png,image/jpeg,image/webp,image/x-icon,image/svg+xml"
                                                        class="hidden"
                                                        @change="handleFaviconUpload"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="ios-button gap-2"
                                                        :disabled="isUploadingFavicon"
                                                        @click="triggerFaviconUpload"
                                                    >
                                                        <ImagePlus class="h-4 w-4" />
                                                        {{ faviconPreview ? 'Ganti Favicon' : 'Upload Favicon' }}
                                                    </Button>
                                                    <Button
                                                        v-if="faviconPreview"
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="ios-button gap-2 text-destructive hover:text-destructive"
                                                        @click="removeFavicon"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                        Hapus Favicon
                                                    </Button>
                                                </div>
                                            </div>
                                            <p class="hint">Format: PNG, ICO, atau SVG. Maksimal 1MB. Rekomendasi: 32x32px atau 64x64px</p>
                                            <InputError :message="errors.store_favicon" />
                                        </div>

                                        <!-- Store Name -->
                                        <div class="admin-input-group">
                                            <Label for="store_name" class="flex items-center gap-2">
                                                <Store class="h-4 w-4 text-primary" />
                                                Nama Toko *
                                            </Label>
                                            <Input
                                                id="store_name"
                                                v-model="form.store_name"
                                                type="text"
                                                placeholder="Masukkan nama toko"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.store_name }"
                                            />
                                            <InputError :message="errors.store_name" />
                                        </div>

                                        <!-- Store Tagline -->
                                        <div class="admin-input-group">
                                            <Label for="store_tagline" class="flex items-center gap-2">
                                                <Tag class="h-4 w-4 text-primary" />
                                                Tagline Toko
                                            </Label>
                                            <Input
                                                id="store_tagline"
                                                v-model="form.store_tagline"
                                                type="text"
                                                placeholder="Contoh: Premium Quality Products"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.store_tagline }"
                                            />
                                            <p class="hint">Tagline singkat yang ditampilkan di header toko</p>
                                            <InputError :message="errors.store_tagline" />
                                        </div>

                                        <!-- Store Address -->
                                        <div class="admin-input-group">
                                            <Label for="store_address" class="flex items-center gap-2">
                                                <MapPin class="h-4 w-4 text-primary" />
                                                Alamat Toko
                                            </Label>
                                            <textarea
                                                id="store_address"
                                                v-model="form.store_address"
                                                rows="3"
                                                placeholder="Masukkan alamat lengkap toko"
                                                class="admin-textarea"
                                                :class="{ 'border-destructive': errors.store_address }"
                                            />
                                            <InputError :message="errors.store_address" />
                                        </div>

                                        <!-- Store Phone -->
                                        <div class="admin-input-group">
                                            <Label for="store_phone" class="flex items-center gap-2">
                                                <Phone class="h-4 w-4 text-primary" />
                                                Nomor Telepon Toko
                                            </Label>
                                            <Input
                                                id="store_phone"
                                                v-model="form.store_phone"
                                                type="text"
                                                placeholder="021-1234567"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.store_phone }"
                                            />
                                            <InputError :message="errors.store_phone" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- WhatsApp Settings -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <MessageSquare />
                                        WhatsApp Bisnis
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Phone Country Code -->
                                        <div class="admin-input-group">
                                            <Label for="phone_country_code">Negara / Region *</Label>
                                            <select
                                                id="phone_country_code"
                                                v-model="form.phone_country_code"
                                                class="admin-select"
                                                :class="{ 'border-destructive': errors.phone_country_code }"
                                            >
                                                <option
                                                    v-for="country in supportedCountries"
                                                    :key="country.code"
                                                    :value="country.code"
                                                >
                                                    {{ country.name }} ({{ country.dialCode }})
                                                </option>
                                            </select>
                                            <p class="hint">
                                                Pilih negara untuk format nomor telepon WhatsApp customer
                                            </p>
                                            <InputError :message="errors.phone_country_code" />
                                        </div>

                                        <!-- WhatsApp Number -->
                                        <div class="admin-input-group">
                                            <Label for="whatsapp_number">Nomor WhatsApp Bisnis *</Label>
                                            <Input
                                                id="whatsapp_number"
                                                v-model="form.whatsapp_number"
                                                type="text"
                                                placeholder="081234567890"
                                                class="admin-input"
                                                :class="{
                                                    'border-destructive': errors.whatsapp_number || whatsappInputStatus.status === 'invalid',
                                                    'border-green-500 focus-visible:ring-green-500': whatsappInputStatus.status === 'valid',
                                                }"
                                            />
                                            <!-- Real-time validation hint -->
                                            <p
                                                class="text-sm"
                                                :class="{
                                                    'text-muted-foreground': whatsappInputStatus.status === 'empty' || whatsappInputStatus.status === 'typing',
                                                    'text-green-600 dark:text-green-400': whatsappInputStatus.status === 'valid',
                                                    'text-destructive': whatsappInputStatus.status === 'invalid',
                                                }"
                                            >
                                                {{ whatsappInputStatus.message }}
                                            </p>
                                            <InputError :message="errors.whatsapp_number" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Operating Hours -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <Clock />
                                        Jam Operasional
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-3">
                                        <Motion
                                            v-for="(hours, day, index) in form.operating_hours"
                                            :key="day"
                                            :initial="{ opacity: 0, x: -20 }"
                                            :animate="{ opacity: 1, x: 0 }"
                                            :transition="{ ...springPresets.ios, delay: 0.2 + (index as number) * 0.03 }"
                                            class="flex flex-col gap-3 rounded-xl border border-border/50 bg-muted/20 p-4 transition-colors hover:bg-muted/40 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div class="flex items-center gap-3">
                                                <Checkbox
                                                    :id="`is_open_${day}`"
                                                    :checked="hours.is_open"
                                                    @update:checked="hours.is_open = $event"
                                                />
                                                <Label :for="`is_open_${day}`" class="min-w-[80px] cursor-pointer font-medium">
                                                    {{ dayLabels[day] }}
                                                </Label>
                                                <Badge
                                                    :class="[
                                                        hours.is_open ? 'admin-badge--success' : 'bg-muted text-muted-foreground',
                                                    ]"
                                                >
                                                    {{ hours.is_open ? 'Buka' : 'Tutup' }}
                                                </Badge>
                                            </div>
                                            <div
                                                v-if="hours.is_open"
                                                class="flex items-center gap-2"
                                            >
                                                <Input
                                                    v-model="hours.open"
                                                    type="time"
                                                    class="admin-input h-10 w-[110px]"
                                                />
                                                <span class="text-muted-foreground">-</span>
                                                <Input
                                                    v-model="hours.close"
                                                    type="time"
                                                    class="admin-input h-10 w-[110px]"
                                                />
                                            </div>
                                            <div
                                                v-else
                                                class="text-sm text-muted-foreground"
                                            >
                                                Toko tutup hari ini
                                            </div>
                                        </Motion>
                                    </div>
                                    <InputError :message="errors.operating_hours" class="mt-2" />
                                </div>
                            </div>
                        </Motion>
                    </div>

                    <!-- Sidebar -->
                    <div class="flex flex-col gap-6">
                        <!-- Order Settings - Auto Cancel -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <Timer />
                                        Auto-Cancel Pesanan
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Auto Cancel Toggle -->
                                        <div class="flex items-center justify-between rounded-xl border border-border/50 bg-muted/20 p-4">
                                            <div class="flex items-center gap-3">
                                                <Ban class="h-5 w-5 text-orange-500" />
                                                <div>
                                                    <p class="font-medium">Auto-Cancel Pending</p>
                                                    <p class="text-sm text-muted-foreground">
                                                        Batalkan pesanan pending yang tidak dikonfirmasi
                                                    </p>
                                                </div>
                                            </div>
                                            <Checkbox
                                                id="auto_cancel_enabled"
                                                :checked="form.auto_cancel_enabled"
                                                @update:checked="form.auto_cancel_enabled = $event"
                                            />
                                        </div>

                                        <!-- Auto Cancel Minutes -->
                                        <div v-if="form.auto_cancel_enabled" class="admin-input-group">
                                            <Label for="auto_cancel_minutes" class="flex items-center gap-2">
                                                <Clock class="h-4 w-4 text-primary" />
                                                Durasi Sebelum Auto-Cancel (menit) *
                                            </Label>
                                            <Input
                                                id="auto_cancel_minutes"
                                                v-model.number="form.auto_cancel_minutes"
                                                type="number"
                                                min="5"
                                                max="1440"
                                                placeholder="30"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.auto_cancel_minutes }"
                                            />
                                            <p class="hint">
                                                Pesanan pending akan otomatis dibatalkan setelah {{ form.auto_cancel_minutes }} menit
                                            </p>
                                            <InputError :message="errors.auto_cancel_minutes" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Delivery Settings -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(4) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-header">
                                    <h3>
                                        <Truck />
                                        Pengaturan Delivery
                                    </h3>
                                </div>
                                <div class="admin-form-section-content">
                                    <div class="flex flex-col gap-5">
                                        <!-- Delivery Fee -->
                                        <div class="admin-input-group">
                                            <Label for="delivery_fee" class="flex items-center gap-2">
                                                <Wallet class="h-4 w-4 text-primary" />
                                                Biaya Pengiriman (Rp) *
                                            </Label>
                                            <Input
                                                id="delivery_fee"
                                                v-model.number="form.delivery_fee"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.delivery_fee }"
                                            />
                                            <InputError :message="errors.delivery_fee" />
                                        </div>

                                        <!-- Minimum Order -->
                                        <div class="admin-input-group">
                                            <Label for="minimum_order" class="flex items-center gap-2">
                                                <ShoppingBag class="h-4 w-4 text-primary" />
                                                Minimum Order (Rp) *
                                            </Label>
                                            <Input
                                                id="minimum_order"
                                                v-model.number="form.minimum_order"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                class="admin-input"
                                                :class="{ 'border-destructive': errors.minimum_order }"
                                            />
                                            <InputError :message="errors.minimum_order" />
                                        </div>

                                        <!-- Delivery Areas -->
                                        <div class="admin-input-group">
                                            <Label class="flex items-center gap-2">
                                                <MapPin class="h-4 w-4 text-primary" />
                                                Area Pengiriman
                                            </Label>
                                            <div class="flex gap-2">
                                                <Input
                                                    v-model="newArea"
                                                    type="text"
                                                    placeholder="Tambah area baru"
                                                    class="admin-input"
                                                    @keydown.enter.prevent="addDeliveryArea"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="icon"
                                                    class="ios-button h-12 w-12 shrink-0"
                                                    @click="addDeliveryArea"
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </Button>
                                            </div>

                                            <!-- Areas List -->
                                            <div
                                                v-if="form.delivery_areas.length > 0"
                                                class="mt-3 flex flex-wrap gap-2"
                                            >
                                                <Motion
                                                    v-for="(area, index) in form.delivery_areas"
                                                    :key="index"
                                                    :initial="{ scale: 0 }"
                                                    :animate="{ scale: 1 }"
                                                    :transition="springPresets.bouncy"
                                                >
                                                    <Badge
                                                        variant="secondary"
                                                        class="gap-1 pr-1 text-sm"
                                                    >
                                                        {{ area }}
                                                        <button
                                                            type="button"
                                                            class="ios-button ml-1 rounded-full p-0.5 hover:bg-muted"
                                                            @click="removeDeliveryArea(index)"
                                                        >
                                                            <X class="h-3 w-3" />
                                                        </button>
                                                    </Badge>
                                                </Motion>
                                            </div>
                                            <p
                                                v-else
                                                class="text-sm text-muted-foreground"
                                            >
                                                Belum ada area pengiriman
                                            </p>
                                            <InputError :message="errors.delivery_areas" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Actions -->
                        <Motion
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ ...springPresets.ios, delay: staggerDelay(5) }"
                        >
                            <div class="admin-form-section">
                                <div class="admin-form-section-content">
                                    <Button
                                        type="submit"
                                        class="admin-btn-primary w-full gap-2"
                                        :disabled="isSubmitting"
                                    >
                                        <Save class="h-4 w-4" />
                                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengaturan' }}
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
