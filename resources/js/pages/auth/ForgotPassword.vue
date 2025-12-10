<script setup lang="ts">
/**
 * ForgotPassword Page - Halaman Lupa Password
 * Dengan iOS-like animations, spring physics, haptic feedback,
 * dan form interactions yang smooth
 *
 * @author Zulfikar Hidayatullah
 */
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { login } from '@/routes'
import { email } from '@/routes/password'
import { Form, Head } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { ref } from 'vue'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { Mail, ArrowLeft } from 'lucide-vue-next'

defineProps<{
    status?: string
}>()

/**
 * Haptic feedback untuk iOS-like tactile response
 */
const haptic = useHapticFeedback()

/**
 * Focus state untuk email field
 */
const isEmailFocused = ref(false)

/**
 * Press state untuk submit button
 */
const isSubmitPressed = ref(false)
</script>

<template>
    <AuthLayout
        title="Lupa Password?"
        description="Masukkan email Anda untuk menerima link reset password"
    >
        <Head title="Lupa Password" />

        <!-- Success status message dengan iOS-style alert -->
        <Motion
            v-if="status"
            :initial="{ opacity: 0, y: -10, scale: 0.95 }"
            :animate="{ opacity: 1, y: 0, scale: 1 }"
            :transition="springPresets.bouncy"
            class="mb-6 flex items-center gap-3 rounded-2xl bg-green-50 px-4 py-3.5 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
        >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-800/50">
                <Mail class="h-4 w-4" />
            </div>
            <span>{{ status }}</span>
        </Motion>

        <div class="space-y-6">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <!-- Email Field dengan iOS-style input -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(0) }"
                    class="grid gap-2"
                >
                    <Label for="email" class="text-sm font-medium">Email</Label>
                    <div
                        class="transition-transform duration-200"
                        :class="{ 'scale-[1.02]': isEmailFocused }"
                    >
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            autofocus
                            placeholder="email@example.com"
                            class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base focus:bg-background focus:ring-2 focus:ring-primary/20"
                            @focus="isEmailFocused = true; haptic.selection()"
                            @blur="isEmailFocused = false"
                        />
                    </div>
                    <InputError :message="errors.email" class="animate-ios-shake" />
                </Motion>

                <!-- Submit Button dengan iOS-style -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="mt-6"
                >
                    <Button
                        type="submit"
                        class="ios-button h-13 w-full rounded-2xl text-base font-semibold shadow-lg transition-all duration-150"
                        :class="{ 'scale-95': isSubmitPressed }"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                        @mousedown="isSubmitPressed = true; haptic.medium()"
                        @mouseup="isSubmitPressed = false"
                        @mouseleave="isSubmitPressed = false"
                        @touchstart.passive="isSubmitPressed = true; haptic.medium()"
                        @touchend="isSubmitPressed = false"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Mengirim...' : 'Kirim Link Reset' }}
                    </Button>
                </Motion>
            </Form>

            <!-- Back to Login Link -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ ...springPresets.smooth, delay: staggerDelay(2) }"
                class="text-center"
            >
                <TextLink
                    :href="login()"
                    class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="haptic.light()"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke halaman masuk
                </TextLink>
            </Motion>
        </div>
    </AuthLayout>
</template>
