<script setup lang="ts">
/**
 * IconPicker Component
 * Modal untuk memilih icon dari library Lucide icons
 * Digunakan untuk customize icon timeline status pesanan
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Search, Check } from 'lucide-vue-next'

// Import semua icon yang tersedia untuk timeline
import {
    Clock,
    Timer,
    Hourglass,
    CalendarClock,
    CheckCircle2,
    CircleCheck,
    CircleCheckBig,
    BadgeCheck,
    ChefHat,
    Utensils,
    Flame,
    CookingPot,
    Loader,
    RefreshCw,
    Package,
    Box,
    Gift,
    Archive,
    PackageCheck,
    Truck,
    Car,
    Bike,
    Send,
    Navigation,
    XCircle,
    X,
    Ban,
    CircleX,
    AlertCircle,
    ShoppingBag,
    ShoppingCart,
    Receipt,
    FileText,
    Star,
    Heart,
    ThumbsUp,
} from 'lucide-vue-next'

// Mapping icon name ke component
const iconComponents: Record<string, typeof Clock> = {
    Clock,
    Timer,
    Hourglass,
    CalendarClock,
    CheckCircle2,
    CircleCheck,
    Check,
    CircleCheckBig,
    BadgeCheck,
    ChefHat,
    Utensils,
    Flame,
    CookingPot,
    Loader,
    RefreshCw,
    Package,
    Box,
    Gift,
    Archive,
    PackageCheck,
    Truck,
    Car,
    Bike,
    Send,
    Navigation,
    XCircle,
    X,
    Ban,
    CircleX,
    AlertCircle,
    ShoppingBag,
    ShoppingCart,
    Receipt,
    FileText,
    Star,
    Heart,
    ThumbsUp,
}

// Daftar icon dengan label Indonesia
const availableIcons = [
    // Time related
    { name: 'Clock', label: 'Jam', category: 'Waktu' },
    { name: 'Timer', label: 'Timer', category: 'Waktu' },
    { name: 'Hourglass', label: 'Jam Pasir', category: 'Waktu' },
    { name: 'CalendarClock', label: 'Kalender Jam', category: 'Waktu' },

    // Check/Success
    { name: 'CheckCircle2', label: 'Centang Lingkaran', category: 'Sukses' },
    { name: 'CircleCheck', label: 'Lingkaran Centang', category: 'Sukses' },
    { name: 'Check', label: 'Centang', category: 'Sukses' },
    { name: 'CircleCheckBig', label: 'Centang Besar', category: 'Sukses' },
    { name: 'BadgeCheck', label: 'Badge Centang', category: 'Sukses' },

    // Processing/Cooking
    { name: 'ChefHat', label: 'Topi Chef', category: 'Proses' },
    { name: 'Utensils', label: 'Alat Makan', category: 'Proses' },
    { name: 'Flame', label: 'Api', category: 'Proses' },
    { name: 'CookingPot', label: 'Panci', category: 'Proses' },
    { name: 'Loader', label: 'Loading', category: 'Proses' },
    { name: 'RefreshCw', label: 'Proses', category: 'Proses' },

    // Package/Ready
    { name: 'Package', label: 'Paket', category: 'Paket' },
    { name: 'Box', label: 'Kotak', category: 'Paket' },
    { name: 'Gift', label: 'Hadiah', category: 'Paket' },
    { name: 'Archive', label: 'Arsip', category: 'Paket' },
    { name: 'PackageCheck', label: 'Paket Centang', category: 'Paket' },

    // Delivery
    { name: 'Truck', label: 'Truk', category: 'Pengiriman' },
    { name: 'Car', label: 'Mobil', category: 'Pengiriman' },
    { name: 'Bike', label: 'Sepeda', category: 'Pengiriman' },
    { name: 'Send', label: 'Kirim', category: 'Pengiriman' },
    { name: 'Navigation', label: 'Navigasi', category: 'Pengiriman' },

    // Cancel/Error
    { name: 'XCircle', label: 'X Lingkaran', category: 'Batal' },
    { name: 'X', label: 'X', category: 'Batal' },
    { name: 'Ban', label: 'Larangan', category: 'Batal' },
    { name: 'CircleX', label: 'Lingkaran X', category: 'Batal' },
    { name: 'AlertCircle', label: 'Alert Lingkaran', category: 'Batal' },

    // Other
    { name: 'ShoppingBag', label: 'Tas Belanja', category: 'Lainnya' },
    { name: 'ShoppingCart', label: 'Keranjang', category: 'Lainnya' },
    { name: 'Receipt', label: 'Struk', category: 'Lainnya' },
    { name: 'FileText', label: 'Dokumen', category: 'Lainnya' },
    { name: 'Star', label: 'Bintang', category: 'Lainnya' },
    { name: 'Heart', label: 'Hati', category: 'Lainnya' },
    { name: 'ThumbsUp', label: 'Jempol', category: 'Lainnya' },
]

// Daftar kategori unik
const categories = [...new Set(availableIcons.map(icon => icon.category))]

interface Props {
    /** State untuk membuka/menutup dialog */
    open: boolean
    /** Icon yang saat ini terpilih */
    currentIcon?: string
    /** Status label untuk context */
    statusLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
    currentIcon: 'Clock',
    statusLabel: '',
})

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'select', iconName: string): void
}>()

const haptic = useHapticFeedback()

const searchQuery = ref('')
const selectedIcon = ref(props.currentIcon)
const selectedCategory = ref<string | null>(null)

/**
 * Reset state ketika dialog dibuka
 */
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        searchQuery.value = ''
        selectedIcon.value = props.currentIcon
        selectedCategory.value = null
    }
})

/**
 * Filter icons berdasarkan search query dan kategori
 */
const filteredIcons = computed(() => {
    let icons = availableIcons

    // Filter berdasarkan kategori jika dipilih
    if (selectedCategory.value) {
        icons = icons.filter(icon => icon.category === selectedCategory.value)
    }

    // Filter berdasarkan search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase()
        icons = icons.filter(icon =>
            icon.name.toLowerCase().includes(query) ||
            icon.label.toLowerCase().includes(query) ||
            icon.category.toLowerCase().includes(query)
        )
    }

    return icons
})

/**
 * Get icon component berdasarkan nama
 */
function getIconComponent(iconName: string) {
    return iconComponents[iconName] || Clock
}

/**
 * Handle select icon
 */
function handleSelectIcon(iconName: string): void {
    selectedIcon.value = iconName
    haptic.light()
}

/**
 * Handle select kategori
 */
function handleSelectCategory(category: string | null): void {
    selectedCategory.value = selectedCategory.value === category ? null : category
    haptic.light()
}

/**
 * Handle konfirmasi pilihan
 */
function handleConfirm(): void {
    haptic.success()
    emit('select', selectedIcon.value)
    emit('update:open', false)
}

/**
 * Handle cancel
 */
function handleCancel(): void {
    haptic.light()
    emit('update:open', false)
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent class="rounded-2xl sm:max-w-lg max-h-[85vh] flex flex-col">
            <DialogHeader>
                <DialogTitle class="text-center">Pilih Icon</DialogTitle>
                <DialogDescription class="text-center">
                    {{ statusLabel ? `Pilih icon untuk status "${statusLabel}"` : 'Pilih icon yang akan digunakan' }}
                </DialogDescription>
            </DialogHeader>

            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Cari icon..."
                    class="pl-10"
                />
            </div>

            <!-- Category Filter -->
            <div class="flex flex-wrap gap-2">
                <Button
                    v-for="category in categories"
                    :key="category"
                    :variant="selectedCategory === category ? 'default' : 'outline'"
                    size="sm"
                    class="text-xs ios-button"
                    @click="handleSelectCategory(category)"
                >
                    {{ category }}
                </Button>
            </div>

            <!-- Icons Grid -->
            <div class="flex-1 overflow-y-auto min-h-0">
                <div class="grid grid-cols-5 sm:grid-cols-6 gap-2 p-1">
                    <button
                        v-for="icon in filteredIcons"
                        :key="icon.name"
                        type="button"
                        class="relative flex flex-col items-center justify-center gap-1 p-3 rounded-xl transition-all duration-200 ios-button"
                        :class="[
                            selectedIcon === icon.name
                                ? 'bg-primary text-primary-foreground ring-2 ring-primary ring-offset-2'
                                : 'bg-muted/50 hover:bg-muted text-foreground'
                        ]"
                        @click="handleSelectIcon(icon.name)"
                    >
                        <component
                            :is="getIconComponent(icon.name)"
                            class="h-6 w-6"
                        />
                        <span class="text-[10px] truncate max-w-full">{{ icon.label }}</span>

                        <!-- Selected indicator -->
                        <Transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 scale-50"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-50"
                        >
                            <div
                                v-if="selectedIcon === icon.name"
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background"
                            >
                                <Check class="h-3 w-3" />
                            </div>
                        </Transition>
                    </button>
                </div>

                <!-- Empty state -->
                <div
                    v-if="filteredIcons.length === 0"
                    class="flex flex-col items-center justify-center py-8 text-muted-foreground"
                >
                    <Search class="h-8 w-8 mb-2 opacity-50" />
                    <p class="text-sm">Tidak ada icon ditemukan</p>
                </div>
            </div>

            <!-- Selected Preview -->
            <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
                    <component
                        :is="getIconComponent(selectedIcon)"
                        class="h-6 w-6 text-primary"
                    />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">Icon Terpilih</p>
                    <p class="text-xs text-muted-foreground">
                        {{ availableIcons.find(i => i.name === selectedIcon)?.label || selectedIcon }}
                    </p>
                </div>
            </div>

            <DialogFooter class="flex-col gap-2 sm:flex-col">
                <Button
                    type="button"
                    class="w-full ios-button"
                    @click="handleConfirm"
                >
                    Pilih Icon
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="w-full ios-button"
                    @click="handleCancel"
                >
                    Batal
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

