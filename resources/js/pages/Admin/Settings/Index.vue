<script setup lang="ts">
/**
 * Admin Store Settings Page
 * Halaman pengaturan toko dengan form konfigurasi, yaitu:
 * - Informasi umum toko (nama, alamat, telepon)
 * - Konfigurasi WhatsApp bisnis
 * - Jam operasional per hari
 * - Pengaturan delivery (area, biaya, minimum order)
 */
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import InputError from '@/components/InputError.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as settingsIndex, update } from '@/routes/admin/settings'
import {
    Settings,
    Store,
    MessageSquare,
    Clock,
    Truck,
    Save,
    Plus,
    X,
} from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

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
    store_address: string
    store_phone: string
    whatsapp_number: string
    operating_hours: OperatingHours
    delivery_areas: string[]
    delivery_fee: number
    minimum_order: number
}

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
const flashSuccess = computed(() => page.props.flash?.success as string | undefined)
const flashError = computed(() => page.props.flash?.error as string | undefined)

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
    store_address: props.settings.store_address || '',
    store_phone: props.settings.store_phone || '',
    whatsapp_number: props.settings.whatsapp_number || '',
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
})

// New delivery area input
const newArea = ref('')

// Errors
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

/**
 * Tambah area pengiriman baru
 */
function addDeliveryArea() {
    if (newArea.value.trim() && !form.value.delivery_areas.includes(newArea.value.trim())) {
        form.value.delivery_areas.push(newArea.value.trim())
        newArea.value = ''
    }
}

/**
 * Hapus area pengiriman berdasarkan index
 */
function removeDeliveryArea(index: number) {
    form.value.delivery_areas.splice(index, 1)
}

/**
 * Submit form untuk menyimpan pengaturan toko
 */
function submitForm() {
    isSubmitting.value = true
    errors.value = {}

    router.patch(update().url, form.value, {
        onError: (errs) => {
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
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Pengaturan Toko
                    </h1>
                    <p class="text-muted-foreground">
                        Kelola informasi dan konfigurasi toko Anda
                    </p>
                </div>
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

            <!-- Form -->
            <form @submit.prevent="submitForm" class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content (2 columns) -->
                <div class="lg:col-span-2 flex flex-col gap-6">
                    <!-- Store Information Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Store class="h-5 w-5" />
                                Informasi Toko
                            </CardTitle>
                            <CardDescription>
                                Informasi dasar toko yang akan ditampilkan ke customer
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <!-- Store Name -->
                            <div class="flex flex-col gap-2">
                                <Label for="store_name">Nama Toko *</Label>
                                <Input
                                    id="store_name"
                                    v-model="form.store_name"
                                    type="text"
                                    placeholder="Masukkan nama toko"
                                    :class="{ 'border-destructive': errors.store_name }"
                                />
                                <InputError :message="errors.store_name" />
                            </div>

                            <!-- Store Address -->
                            <div class="flex flex-col gap-2">
                                <Label for="store_address">Alamat Toko</Label>
                                <textarea
                                    id="store_address"
                                    v-model="form.store_address"
                                    rows="3"
                                    placeholder="Masukkan alamat lengkap toko"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="{ 'border-destructive': errors.store_address }"
                                ></textarea>
                                <InputError :message="errors.store_address" />
                            </div>

                            <!-- Store Phone -->
                            <div class="flex flex-col gap-2">
                                <Label for="store_phone">Nomor Telepon Toko</Label>
                                <Input
                                    id="store_phone"
                                    v-model="form.store_phone"
                                    type="text"
                                    placeholder="021-1234567"
                                    :class="{ 'border-destructive': errors.store_phone }"
                                />
                                <InputError :message="errors.store_phone" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- WhatsApp Settings Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <MessageSquare class="h-5 w-5" />
                                Pengaturan WhatsApp
                            </CardTitle>
                            <CardDescription>
                                Nomor WhatsApp bisnis untuk menerima pesanan dari customer
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <!-- WhatsApp Number -->
                            <div class="flex flex-col gap-2">
                                <Label for="whatsapp_number">Nomor WhatsApp Bisnis *</Label>
                                <Input
                                    id="whatsapp_number"
                                    v-model="form.whatsapp_number"
                                    type="text"
                                    placeholder="6281234567890"
                                    :class="{ 'border-destructive': errors.whatsapp_number }"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Format: Kode negara tanpa tanda + (contoh: 6281234567890)
                                </p>
                                <InputError :message="errors.whatsapp_number" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Operating Hours Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                Jam Operasional
                            </CardTitle>
                            <CardDescription>
                                Atur jam buka dan tutup toko untuk setiap hari
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <div
                                    v-for="(hours, day) in form.operating_hours"
                                    :key="day"
                                    class="flex flex-col gap-2 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <Checkbox
                                            :id="`is_open_${day}`"
                                            :checked="hours.is_open"
                                            @update:checked="hours.is_open = $event"
                                        />
                                        <Label :for="`is_open_${day}`" class="cursor-pointer font-medium min-w-[80px]">
                                            {{ dayLabels[day] }}
                                        </Label>
                                        <Badge v-if="hours.is_open" variant="default" class="text-xs">
                                            Buka
                                        </Badge>
                                        <Badge v-else variant="secondary" class="text-xs">
                                            Tutup
                                        </Badge>
                                    </div>
                                    <div
                                        v-if="hours.is_open"
                                        class="flex items-center gap-2 mt-2 sm:mt-0"
                                    >
                                        <Input
                                            v-model="hours.open"
                                            type="time"
                                            class="w-[120px]"
                                        />
                                        <span class="text-muted-foreground">-</span>
                                        <Input
                                            v-model="hours.close"
                                            type="time"
                                            class="w-[120px]"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="text-sm text-muted-foreground mt-2 sm:mt-0"
                                    >
                                        Toko tutup hari ini
                                    </div>
                                </div>
                            </div>
                            <InputError :message="errors.operating_hours" class="mt-2" />
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar (1 column) -->
                <div class="flex flex-col gap-6">
                    <!-- Delivery Settings Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Truck class="h-5 w-5" />
                                Pengaturan Delivery
                            </CardTitle>
                            <CardDescription>
                                Atur biaya dan area pengiriman
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <!-- Delivery Fee -->
                            <div class="flex flex-col gap-2">
                                <Label for="delivery_fee">Biaya Pengiriman (Rp) *</Label>
                                <Input
                                    id="delivery_fee"
                                    v-model.number="form.delivery_fee"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="{ 'border-destructive': errors.delivery_fee }"
                                />
                                <InputError :message="errors.delivery_fee" />
                            </div>

                            <!-- Minimum Order -->
                            <div class="flex flex-col gap-2">
                                <Label for="minimum_order">Minimum Order (Rp) *</Label>
                                <Input
                                    id="minimum_order"
                                    v-model.number="form.minimum_order"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="{ 'border-destructive': errors.minimum_order }"
                                />
                                <InputError :message="errors.minimum_order" />
                            </div>

                            <!-- Delivery Areas -->
                            <div class="flex flex-col gap-2">
                                <Label>Area Pengiriman</Label>
                                <div class="flex gap-2">
                                    <Input
                                        v-model="newArea"
                                        type="text"
                                        placeholder="Tambah area baru"
                                        @keydown.enter.prevent="addDeliveryArea"
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        @click="addDeliveryArea"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>

                                <!-- Areas List -->
                                <div
                                    v-if="form.delivery_areas.length > 0"
                                    class="flex flex-wrap gap-2 mt-2"
                                >
                                    <Badge
                                        v-for="(area, index) in form.delivery_areas"
                                        :key="index"
                                        variant="secondary"
                                        class="gap-1 pr-1"
                                    >
                                        {{ area }}
                                        <button
                                            type="button"
                                            class="ml-1 rounded-full p-0.5 hover:bg-muted"
                                            @click="removeDeliveryArea(index)"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </Badge>
                                </div>
                                <p
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    Belum ada area pengiriman
                                </p>
                                <InputError :message="errors.delivery_areas" />
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
                                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

