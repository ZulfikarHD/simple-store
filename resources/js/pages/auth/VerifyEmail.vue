<script setup lang="ts">
/**
 * VerifyEmail Page - Halaman Verifikasi Email
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * dan visual feedback yang menarik
 *
 * @author Zulfikar Hidayatullah
 */
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { logout } from '@/routes'
import { send } from '@/routes/verification'
import { Form, Head } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { ref } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Mail, CheckCircle, RefreshCw, LogOut } from 'lucide-vue-next'

defineProps<{
    status?: string
}>()

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Press state untuk buttons
 */
const isResendPressed = ref(false)
</script>

<template>
    <AuthLayout
        title="Verifikasi Email"
        description="Silakan verifikasi alamat email Anda dengan mengklik link yang telah kami kirimkan."
    >
        <Head title="Verifikasi Email" />

        <!-- Email Icon dengan animation -->
        <Motion
            :initial="{ opacity: 0, scale: 0.8, rotate: -10 }"
            :animate="{ opacity: 1, scale: 1, rotate: 0 }"
            :transition="springPresets.bouncy"
            class="mb-6 flex justify-center"
        >
            <div class="relative">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-primary/10">
                    <Mail class="h-10 w-10 text-primary" />
                </div>
                <!-- Animated pulse ring -->
                <div class="absolute inset-0 animate-ping rounded-3xl bg-primary/20" style="animation-duration: 2s;" />
            </div>
        </Motion>

        <!-- Success status message dengan iOS-style alert -->
        <Motion
            v-if="status === 'verification-link-sent'"
            :initial="{ opacity: 0, y: -10, scale: 0.95 }"
            :animate="{ opacity: 1, y: 0, scale: 1 }"
            :transition="springPresets.bouncy"
            class="mb-6 flex items-center gap-3 rounded-2xl bg-green-50 px-4 py-3.5 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
        >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-800/50">
                <CheckCircle class="h-4 w-4" />
            </div>
            <span>Link verifikasi baru telah dikirim ke alamat email Anda.</span>
        </Motion>

        <Form
            v-bind="send.form()"
            class="space-y-6"
            v-slot="{ processing }"
        >
            <!-- Resend Button dengan iOS-style -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                class="flex justify-center"
            >
                <Button
                    type="submit"
                    class="ios-button h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                    :class="{ 'scale-95': isResendPressed }"
                    :disabled="processing"
                    @mousedown="isResendPressed = true; haptic.medium()"
                    @mouseup="isResendPressed = false"
                    @mouseleave="isResendPressed = false"
                    @touchstart.passive="isResendPressed = true; haptic.medium()"
                    @touchend="isResendPressed = false"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    <RefreshCw v-else class="mr-2 h-4 w-4" />
                    {{ processing ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi' }}
                </Button>
            </Motion>

            <!-- Logout Link -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ ...springPresets.smooth, delay: staggerDelay(1) }"
                class="text-center"
            >
                <TextLink
                    :href="logout()"
                    as="button"
                    class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="haptic.light()"
                >
                    <LogOut class="h-4 w-4" />
                    Keluar dari akun
                </TextLink>
            </Motion>
        </Form>

        <!-- Info Text -->
        <Motion
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ ...springPresets.smooth, delay: staggerDelay(2) }"
            class="mt-8 rounded-xl bg-muted/50 p-4 text-center text-xs text-muted-foreground"
        >
            Tidak menerima email? Periksa folder spam atau coba kirim ulang email verifikasi.
        </Motion>
    </AuthLayout>
</template>
