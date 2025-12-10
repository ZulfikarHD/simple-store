<script setup lang="ts">
/**
 * TwoFactorChallenge Page - Halaman Verifikasi 2FA
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * PIN input yang smooth, dan mode recovery code
 *
 * @author Zulfikar Hidayatullah
 */
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { store } from '@/routes/two-factor/login'
import { Form, Head } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { computed, ref } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { useShakeAnimation } from '@/composables/useSpringAnimation'
import { ShieldCheck, Key, RefreshCw } from 'lucide-vue-next'

interface AuthConfigContent {
    title: string
    description: string
    toggleText: string
    icon: typeof ShieldCheck
}

/**
 * Haptic feedback dan shake animation
 */
const haptic = useHapticFeedback()
const { shakeClass, shake } = useShakeAnimation()

/**
 * Auth config content berdasarkan mode
 */
const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Kode Pemulihan',
            description: 'Masukkan salah satu kode pemulihan darurat Anda untuk mengakses akun.',
            toggleText: 'Gunakan kode autentikator',
            icon: Key,
        }
    }

    return {
        title: 'Kode Autentikasi',
        description: 'Masukkan kode 6 digit dari aplikasi autentikator Anda.',
        toggleText: 'Gunakan kode pemulihan',
        icon: ShieldCheck,
    }
})

const showRecoveryInput = ref<boolean>(false)

/**
 * Press state untuk submit button
 */
const isSubmitPressed = ref(false)

/**
 * Focus state untuk recovery input
 */
const isRecoveryFocused = ref(false)

/**
 * Toggle recovery mode dengan haptic feedback
 */
const toggleRecoveryMode = (clearErrors: () => void): void => {
    haptic.selection()
    showRecoveryInput.value = !showRecoveryInput.value
    clearErrors()
    code.value = []
}

/**
 * Handle form error dengan shake animation
 */
function handleFormError() {
    haptic.error()
    shake()
}

const code = ref<number[]>([])
const codeValue = computed<string>(() => code.value.join(''))
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head title="Verifikasi 2FA" />

        <!-- Security Icon dengan animation -->
        <Motion
            :initial="{ opacity: 0, scale: 0.8 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="springPresets.bouncy"
            class="mb-6 flex justify-center"
        >
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10">
                <component :is="authConfigContent.icon" class="h-8 w-8 text-primary" />
            </div>
        </Motion>

        <div class="space-y-6">
            <!-- OTP Input Mode -->
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    :class="shakeClass"
                    reset-on-error
                    @error="code = []; handleFormError()"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="codeValue" />

                    <!-- PIN Input dengan iOS-style -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        class="flex flex-col items-center justify-center space-y-4"
                    >
                        <div class="flex w-full items-center justify-center">
                            <PinInput
                                id="otp"
                                placeholder="○"
                                v-model="code"
                                type="number"
                                otp
                                class="ios-pin-input"
                            >
                                <PinInputGroup class="gap-2 sm:gap-3">
                                    <PinInputSlot
                                        v-for="(id, index) in 6"
                                        :key="id"
                                        :index="index"
                                        :disabled="processing"
                                        autofocus
                                        class="ios-pin-slot h-12 w-10 rounded-xl border-0 bg-muted/50 text-lg font-semibold transition-all focus:bg-background focus:ring-2 focus:ring-primary/30 sm:h-14 sm:w-12 sm:text-xl"
                                        @focus="haptic.selection()"
                                    />
                                </PinInputGroup>
                            </PinInput>
                        </div>
                        <InputError :message="errors.code" class="animate-ios-shake" />
                    </Motion>

                    <!-- Submit Button -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    >
                        <Button
                            type="submit"
                            class="ios-button h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                            :class="{ 'scale-95': isSubmitPressed }"
                            :disabled="processing"
                            @mousedown="isSubmitPressed = true; haptic.medium()"
                            @mouseup="isSubmitPressed = false"
                            @mouseleave="isSubmitPressed = false"
                            @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                            @touchend="isSubmitPressed = false"
                        >
                            <Spinner v-if="processing" class="mr-2" />
                            {{ processing ? 'Memverifikasi...' : 'Verifikasi' }}
                        </Button>
                    </Motion>

                    <!-- Toggle to Recovery Mode -->
                    <Motion
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ ...springPresets.smooth, delay: staggerDelay(2) }"
                        class="text-center"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            <RefreshCw class="h-3.5 w-3.5" />
                            {{ authConfigContent.toggleText }}
                        </button>
                    </Motion>
                </Form>
            </template>

            <!-- Recovery Code Mode -->
            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    :class="shakeClass"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                    @error="handleFormError"
                >
                    <!-- Recovery Code Input dengan iOS-style -->
                    <Motion
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                        class="grid gap-2"
                    >
                        <div
                            class="relative transition-transform duration-200"
                            :class="{ 'scale-[1.02]': isRecoveryFocused }"
                        >
                            <Key class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                name="recovery_code"
                                type="text"
                                placeholder="Masukkan kode pemulihan"
                                :autofocus="showRecoveryInput"
                                required
                                class="ios-input h-12 rounded-xl border-0 bg-muted/50 pl-11 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                                @focus="isRecoveryFocused = true; haptic.selection()"
                                @blur="isRecoveryFocused = false"
                            />
                        </div>
                        <InputError :message="errors.recovery_code" class="animate-ios-shake" />
                    </Motion>

                    <!-- Submit Button -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    >
                        <Button
                            type="submit"
                            class="ios-button h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                            :class="{ 'scale-95': isSubmitPressed }"
                            :disabled="processing"
                            @mousedown="isSubmitPressed = true; haptic.medium()"
                            @mouseup="isSubmitPressed = false"
                            @mouseleave="isSubmitPressed = false"
                            @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                            @touchend="isSubmitPressed = false"
                        >
                            <Spinner v-if="processing" class="mr-2" />
                            {{ processing ? 'Memverifikasi...' : 'Verifikasi' }}
                        </Button>
                    </Motion>

                    <!-- Toggle to OTP Mode -->
                    <Motion
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ ...springPresets.smooth, delay: staggerDelay(2) }"
                        class="text-center"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            <RefreshCw class="h-3.5 w-3.5" />
                            {{ authConfigContent.toggleText }}
                        </button>
                    </Motion>
                </Form>
            </template>
        </div>

        <!-- Info Text -->
        <Motion
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ ...springPresets.smooth, delay: staggerDelay(3) }"
            class="mt-8 rounded-xl bg-muted/50 p-4 text-center text-xs text-muted-foreground"
        >
            Buka aplikasi autentikator Anda (seperti Google Authenticator atau Authy) untuk mendapatkan kode.
        </Motion>
    </AuthLayout>
</template>

<style scoped>
/**
 * iOS-style PIN input enhancements
 */
.ios-pin-slot {
    /* Enhanced slot styling */
    box-shadow: 0 1px 3px oklch(0 0 0 / 0.05);
}

.ios-pin-slot:focus {
    box-shadow: 0 0 0 3px oklch(var(--primary) / 0.15);
}
</style>
