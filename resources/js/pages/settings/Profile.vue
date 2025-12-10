<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Motion, AnimatePresence } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { computed } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();

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
        title: 'Pengaturan Profil',
        href: edit().url,
    },
];

const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Pengaturan Profil - ${store.name}`" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                >
                    <HeadingSmall
                        title="Informasi Profil"
                        description="Perbarui nama dan alamat email Anda"
                    />
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                >
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Nama lengkap"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Alamat Email</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Alamat email"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Alamat email Anda belum diverifikasi.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Klik di sini untuk mengirim ulang email verifikasi.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Simpan</Button
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

            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springPresets.ios, delay: staggerDelay(2) }"
            >
                <DeleteUser />
            </Motion>
        </SettingsLayout>
    </AppLayout>
</template>
