<script setup lang="ts">
/**
 * PasswordConfirmDialog Component
 * Dialog untuk verifikasi password admin sebelum melakukan aksi sensitif
 * seperti update status, edit data, atau pembatalan pesanan
 *
 * Menggunakan axios dengan XSRF cookie handling untuk menghindari CSRF mismatch
 *
 * @author Zulfikar Hidayatullah
 */
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
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
import { Lock, Eye, EyeOff, AlertTriangle, Loader2 } from 'lucide-vue-next'

interface Props {
    /** State untuk membuka/menutup dialog */
    open: boolean
    /** Title dialog yang ditampilkan */
    title?: string
    /** Deskripsi aksi yang akan dilakukan */
    description?: string
    /** Label untuk tombol konfirmasi */
    confirmLabel?: string
    /** Variant tombol konfirmasi */
    confirmVariant?: 'default' | 'destructive'
    /** Loading state saat proses konfirmasi */
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi Password',
    description: 'Masukkan password Anda untuk melanjutkan aksi ini.',
    confirmLabel: 'Konfirmasi',
    confirmVariant: 'default',
    loading: false,
})

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'confirm', password: string): void
    (e: 'cancel'): void
}>()

const haptic = useHapticFeedback()

const password = ref('')
const showPassword = ref(false)
const error = ref<string | null>(null)
const isVerifying = ref(false)

/**
 * Reset state ketika dialog dibuka/ditutup
 */
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        password.value = ''
        showPassword.value = false
        error.value = null
    }
})

/**
 * Toggle visibility password
 */
function togglePasswordVisibility(): void {
    showPassword.value = !showPassword.value
    haptic.light()
}

/**
 * Get CSRF token dari meta tag
 * Laravel Inertia menyediakan CSRF token melalui meta tag di head
 */
function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

/**
 * Handle submit form untuk verifikasi password
 * menggunakan axios dengan CSRF token dari meta tag
 */
async function handleSubmit(): Promise<void> {
    if (!password.value.trim()) {
        error.value = 'Password wajib diisi.'
        haptic.error()
        return
    }

    isVerifying.value = true
    error.value = null

    try {
        const response = await axios.post('/admin/api/verify-password', {
            password: password.value,
        }, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            withCredentials: true,
        })

        if (!response.data.success) {
            error.value = response.data.message || 'Verifikasi password gagal.'
            haptic.error()
            return
        }

        // Password verified, emit confirm event
        haptic.success()
        emit('confirm', password.value)
    } catch (err: unknown) {
        // Handle axios error response
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            error.value = err.response.data.message
        } else if (axios.isAxiosError(err) && err.response?.status === 419) {
            // CSRF token mismatch - reload halaman untuk mendapatkan token baru
            error.value = 'Sesi telah berakhir. Halaman akan di-refresh.'
            haptic.error()
            setTimeout(() => {
                router.reload()
            }, 1500)
            return
        } else {
            error.value = 'Terjadi kesalahan. Silakan coba lagi.'
        }
        haptic.error()
    } finally {
        isVerifying.value = false
    }
}

/**
 * Handle cancel/close dialog
 */
function handleCancel(): void {
    haptic.light()
    emit('cancel')
    emit('update:open', false)
}

/**
 * Handle keydown untuk submit dengan Enter
 */
function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !isVerifying.value && !props.loading) {
        handleSubmit()
    }
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                    <Lock class="h-6 w-6 text-primary" />
                </div>
                <DialogTitle class="text-center">{{ title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Password Input -->
                <div class="space-y-2">
                    <Label for="confirm-password" class="sr-only">Password</Label>
                    <div class="relative">
                        <Input
                            id="confirm-password"
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan password Anda"
                            class="pr-10"
                            :class="{ 'border-destructive focus-visible:ring-destructive': error }"
                            autocomplete="current-password"
                            :disabled="isVerifying || loading"
                            @keydown="handleKeydown"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                            @click="togglePasswordVisibility"
                        >
                            <Eye v-if="showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="error"
                        class="flex items-center gap-2 rounded-lg border border-destructive/50 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                    >
                        <AlertTriangle class="h-4 w-4 shrink-0" />
                        <span>{{ error }}</span>
                    </div>
                </Transition>

                <DialogFooter class="flex-col gap-2 sm:flex-col">
                    <Button
                        type="submit"
                        :variant="confirmVariant"
                        class="w-full ios-button"
                        :disabled="isVerifying || loading || !password.trim()"
                    >
                        <Loader2 v-if="isVerifying || loading" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isVerifying || loading ? 'Memverifikasi...' : confirmLabel }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full ios-button"
                        :disabled="isVerifying || loading"
                        @click="handleCancel"
                    >
                        Batal
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

