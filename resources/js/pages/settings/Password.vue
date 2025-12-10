<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Motion, AnimatePresence } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { computed } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

const page = usePage()

/**
 * Interface dan computed untuk store branding
 */
interface StoreBranding {
    name: string
    tagline: string
    logo: string | null
}

const store = computed<StoreBranding>(() => {
    return (page.props as { store?: StoreBranding }).store ?? {
        name: 'Simple Store',
        tagline: 'Premium Quality Products',
        logo: null,
    }
})

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Password',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Pengaturan Password - ${store.name}`" />

        <SettingsLayout>
            <div class="space-y-6">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                >
                    <HeadingSmall
                        title="Ubah Password"
                        description="Pastikan akun Anda menggunakan password yang panjang dan acak untuk keamanan"
                    />
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                >
                    <Form
                        v-bind="PasswordController.update.form()"
                        :options="{
                            preserveScroll: true,
                        }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                    <div class="grid gap-2">
                        <Label for="current_password">Password Saat Ini</Label>
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Password saat ini"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password Baru</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Password baru"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Konfirmasi Password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Konfirmasi password baru"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            >Simpan Password</Button
                        >

                        <AnimatePresence>
                            <Motion
                                v-if="recentlySuccessful"
                                :initial="{ opacity: 0, scale: 0.9 }"
                                :animate="{ opacity: 1, scale: 1 }"
                                :exit="{ opacity: 0, scale: 0.9 }"
                                :transition="springPresets.bouncy"
                            >
                                <p class="text-sm text-neutral-600">
                                    Tersimpan.
                                </p>
                            </Motion>
                        </AnimatePresence>
                    </div>
                    </Form>
                </Motion>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
