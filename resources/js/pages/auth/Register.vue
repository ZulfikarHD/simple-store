<script setup lang="ts">
/**
 * Register Page - Halaman Pendaftaran
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * dan form interactions yang smooth
 *
 * @author Zulfikar Hidayatullah
 */
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { ref } from 'vue';
import { useHapticFeedback } from '@/composables/useHapticFeedback';
import { useShakeAnimation } from '@/composables/useSpringAnimation';

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
        title="Buat Akun Baru"
        description="Daftar untuk mulai berbelanja di Simple Store"
    >
        <Head title="Daftar" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
            :class="shakeClass"
            @error="handleFormError"
        >
            <div class="grid gap-4">
                <!-- Name Field -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="grid gap-2"
                >
                    <Label for="name" class="text-sm font-medium">Nama Lengkap</Label>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'name' }"
                    >
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="Masukkan nama lengkap"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="handleFieldFocus('name')"
                            @blur="handleFieldBlur"
                        />
                    </div>
                    <InputError :message="errors.name" class="animate-ios-shake" />
                </Motion>

                <!-- Email Field -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
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
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
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
                    :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
                    class="grid gap-2"
                >
                    <Label for="password" class="text-sm font-medium">Password</Label>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'password' }"
                    >
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
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
                    :transition="{ ...springPresets.ios, delay: staggerDelay(3) }"
                    class="grid gap-2"
                >
                    <Label for="password_confirmation" class="text-sm font-medium">Konfirmasi Password</Label>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': focusedField === 'password_confirmation' }"
                    >
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
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
                    :transition="{ ...springPresets.ios, delay: staggerDelay(4) }"
                >
                    <Button
                        type="submit"
                        class="ios-button mt-2 h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                        :class="{ 'scale-95': isSubmitPressed }"
                        tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                        @mousedown="isSubmitPressed = true; haptic.medium()"
                        @mouseup="isSubmitPressed = false"
                        @mouseleave="isSubmitPressed = false"
                        @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                        @touchend="isSubmitPressed = false"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Memproses...' : 'Buat Akun' }}
                    </Button>
                </Motion>
            </div>

            <!-- Login Link -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ ...springPresets.smooth, delay: staggerDelay(5) }"
                class="text-center text-sm text-muted-foreground"
            >
                Sudah punya akun?
                <TextLink
                    :href="login()"
                    class="font-medium text-primary transition-colors hover:text-primary/80"
                    :tabindex="6"
                >
                    Masuk
                </TextLink>
            </Motion>
        </Form>
    </AuthBase>
</template>
