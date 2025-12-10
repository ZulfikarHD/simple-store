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
            v-slot="{ errors, processing, hasErrors }"
            class="flex flex-col gap-6"
            :class="shakeClass"
            @submit="hasErrors && handleFormError()"
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
            </div>

            <!-- Register Link -->
            <Motion
                v-if="canRegister"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ ...springPresets.smooth, delay: staggerDelay(4) }"
                class="text-center text-sm text-muted-foreground"
            >
                Belum punya akun?
                <TextLink
                    :href="register()"
                    :tabindex="5"
                    class="font-medium text-primary transition-colors hover:text-primary/80"
                >
                    Daftar sekarang
                </TextLink>
            </Motion>
        </Form>
    </AuthBase>
</template>
