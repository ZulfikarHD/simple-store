<script setup lang="ts">
/**
 * Login Page - Halaman Masuk
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * dan form interactions yang smooth
 *
 * @author Zulfikar Hidayatullah
 */
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { ref } from 'vue';
import { useHapticFeedback } from '@/composables/useHapticFeedback';
import { useShakeAnimation } from '@/composables/useSpringAnimation';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

/**
 * Haptic feedback dan shake animation
 */
const haptic = useHapticFeedback();
const { shakeClass, shake } = useShakeAnimation();

/**
 * Focus states untuk form fields dengan iOS-like animation
 */
const focusedField = ref<string | null>(null);

/**
 * Press state untuk submit button
 */
const isSubmitPressed = ref(false);

/**
 * Handle field focus
 */
function handleFieldFocus(fieldName: string) {
    focusedField.value = fieldName;
    haptic.selection();
}

/**
 * Handle field blur
 */
function handleFieldBlur() {
    focusedField.value = null;
}

/**
 * Handle form error dengan shake animation
 */
function handleFormError() {
    haptic.error();
    shake();
}
</script>

<template>
    <AuthBase
        title="Masuk ke Akun Anda"
        description="Masukkan email dan password untuk melanjutkan"
    >
        <Head title="Masuk" />

        <!-- Success status message -->
        <Motion
            v-if="status"
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="springPresets.bouncy"
            class="mb-4 rounded-xl bg-green-100 px-4 py-3 text-center text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
        >
            {{ status }}
        </Motion>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
            :class="shakeClass"
            @error="handleFormError"
        >
            <div class="grid gap-5">
                <!-- Email Field -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="grid gap-2"
                >
                    <Label for="email" class="text-sm font-medium">Email</Label>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'email' }"
                    >
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="handleFieldFocus('email')"
                            @blur="handleFieldBlur"
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
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-medium">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs text-primary transition-colors hover:text-primary/80"
                            :tabindex="5"
                        >
                            Lupa password?
                        </TextLink>
                    </div>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'password' }"
                    >
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="handleFieldFocus('password')"
                            @blur="handleFieldBlur"
                        />
                    </div>
                    <InputError :message="errors.password" class="animate-ios-shake" />
                </Motion>

                <!-- Remember Me -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                    class="flex items-center justify-between"
                >
                    <Label for="remember" class="flex cursor-pointer items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            :tabindex="3"
                            class="rounded-md"
                            @click="haptic.light()"
                        />
                        <span class="text-sm text-muted-foreground">Ingat saya</span>
                    </Label>
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
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                        @mousedown="isSubmitPressed = true; haptic.medium()"
                        @mouseup="isSubmitPressed = false"
                        @mouseleave="isSubmitPressed = false"
                        @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                        @touchend="isSubmitPressed = false"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Memproses...' : 'Masuk' }}
                    </Button>
                </Motion>

                <!-- Divider -->
                <Motion
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ ...springPresets.smooth, delay: staggerDelay(4) }"
                    class="relative"
                >
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-muted" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-background px-2 text-muted-foreground">Atau</span>
                    </div>
                </Motion>

                <!-- Google Login Button -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(5) }"
                >
                    <a
                        href="/auth/google"
                        class="ios-button flex h-13 w-full items-center justify-center gap-3 rounded-2xl border border-border bg-background text-base font-semibold shadow-sm transition-all duration-150 hover:bg-muted/50 active:scale-95"
                        :tabindex="6"
                        @click="haptic.medium()"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                            <path
                                fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            />
                            <path
                                fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            />
                            <path
                                fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                            />
                            <path
                                fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            />
                        </svg>
                        Masuk dengan Google
                    </a>
                </Motion>
            </div>

            <!-- Register Link -->
            <Motion
                v-if="canRegister"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ ...springPresets.smooth, delay: staggerDelay(6) }"
                class="text-center text-sm text-muted-foreground"
            >
                Belum punya akun?
                <TextLink
                    :href="register()"
                    :tabindex="7"
                    class="font-medium text-primary transition-colors hover:text-primary/80"
                >
                    Daftar sekarang
                </TextLink>
            </Motion>
        </Form>
    </AuthBase>
</template>
