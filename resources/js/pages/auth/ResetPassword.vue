<script setup lang="ts">
/**
 * ResetPassword Page - Halaman Reset Password
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * dan form interactions yang smooth
 *
 * @author Zulfikar Hidayatullah
 */
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { update } from '@/routes/password'
import { Form, Head } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { ref } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { useShakeAnimation } from '@/composables/useSpringAnimation'
import { Lock, Mail } from 'lucide-vue-next'

const props = defineProps<{
    token: string
    email: string
}>()

const inputEmail = ref(props.email)

/**
 * Haptic feedback dan shake animation
 */
const haptic = useHapticFeedback()
const { shakeClass, shake } = useShakeAnimation()

/**
 * Focus states untuk form fields
 */
const focusedField = ref<string | null>(null)

/**
 * Press state untuk submit button
 */
const isSubmitPressed = ref(false)

/**
 * Handle field focus
 */
function handleFieldFocus(fieldName: string) {
    focusedField.value = fieldName
    haptic.selection()
}

/**
 * Handle field blur
 */
function handleFieldBlur() {
    focusedField.value = null
}

/**
 * Handle form error dengan shake animation
 */
function handleFormError() {
    haptic.error()
    shake()
}
</script>

<template>
    <AuthLayout
        title="Reset Password"
        description="Masukkan password baru Anda di bawah ini"
    >
        <Head title="Reset Password" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            :class="shakeClass"
            @error="handleFormError"
        >
            <div class="grid gap-5">
                <!-- Email Field (readonly) dengan iOS-style -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="grid gap-2"
                >
                    <Label for="email" class="text-sm font-medium">Email</Label>
                    <div class="relative">
                        <Mail class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            v-model="inputEmail"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/30 pl-11 text-base text-muted-foreground"
                            readonly
                        />
                    </div>
                    <InputError :message="errors.email" class="animate-ios-shake" />
                </Motion>

                <!-- Password Field -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="grid gap-2"
                >
                    <Label for="password" class="text-sm font-medium">Password Baru</Label>
                    <div
                        class="relative transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'password' }"
                    >
                        <Lock class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            autofocus
                            placeholder="Minimal 8 karakter"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 pl-11 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="handleFieldFocus('password')"
                            @blur="handleFieldBlur"
                        />
                    </div>
                    <InputError :message="errors.password" class="animate-ios-shake" />
                </Motion>

                <!-- Confirm Password Field -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                    class="grid gap-2"
                >
                    <Label for="password_confirmation" class="text-sm font-medium">
                        Konfirmasi Password
                    </Label>
                    <div
                        class="relative transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'password_confirmation' }"
                    >
                        <Lock class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 pl-11 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="handleFieldFocus('password_confirmation')"
                            @blur="handleFieldBlur"
                        />
                    </div>
                    <InputError :message="errors.password_confirmation" class="animate-ios-shake" />
                </Motion>

                <!-- Submit Button -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                >
                    <Button
                        type="submit"
                        class="ios-button mt-2 h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                        :class="{ 'scale-95': isSubmitPressed }"
                        :disabled="processing"
                        data-test="reset-password-button"
                        @mousedown="isSubmitPressed = true; haptic.medium()"
                        @mouseup="isSubmitPressed = false"
                        @mouseleave="isSubmitPressed = false"
                        @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                        @touchend="isSubmitPressed = false"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                    </Button>
                </Motion>
            </div>
        </Form>
    </AuthLayout>
</template>
