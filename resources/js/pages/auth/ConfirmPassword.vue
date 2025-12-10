<script setup lang="ts">
/**
 * ConfirmPassword Page - Halaman Konfirmasi Password
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
import { store } from '@/routes/password/confirm'
import { Form, Head } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { ref } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { useShakeAnimation } from '@/composables/useSpringAnimation'
import { ShieldCheck, Lock } from 'lucide-vue-next'

/**
 * Haptic feedback dan shake animation
 */
const haptic = useHapticFeedback()
const { shakeClass, shake } = useShakeAnimation()

/**
 * Focus state untuk password field
 */
const isPasswordFocused = ref(false)

/**
 * Press state untuk submit button
 */
const isSubmitPressed = ref(false)

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
        title="Konfirmasi Password"
        description="Ini adalah area yang aman. Silakan konfirmasi password Anda untuk melanjutkan."
    >
        <Head title="Konfirmasi Password" />

        <!-- Security Icon -->
        <Motion
            :initial="{ opacity: 0, scale: 0.8 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="springPresets.bouncy"
            class="mb-6 flex justify-center"
        >
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10">
                <ShieldCheck class="h-8 w-8 text-primary" />
            </div>
        </Motion>

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            :class="shakeClass"
            @error="handleFormError"
        >
            <div class="space-y-6">
                <!-- Password Field dengan iOS-style -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="grid gap-2"
                >
                    <Label for="password" class="text-sm font-medium">Password</Label>
                    <div
                        class="relative transition-transform duration-200"
                        :class="{ 'scale-[1.02]': isPasswordFocused }"
                    >
                        <Lock class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            autofocus
                            placeholder="Masukkan password Anda"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 pl-11 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="isPasswordFocused = true; haptic.selection()"
                            @blur="isPasswordFocused = false"
                        />
                    </div>
                    <InputError :message="errors.password" class="animate-ios-shake" />
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
                        data-test="confirm-password-button"
                        @mousedown="isSubmitPressed = true; haptic.medium()"
                        @mouseup="isSubmitPressed = false"
                        @mouseleave="isSubmitPressed = false"
                        @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                        @touchend="isSubmitPressed = false"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Memverifikasi...' : 'Konfirmasi Password' }}
                    </Button>
                </Motion>
            </div>
        </Form>
    </AuthLayout>
</template>
