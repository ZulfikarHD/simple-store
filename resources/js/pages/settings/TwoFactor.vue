<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { computed, onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Autentikasi 2 Faktor',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Autentikasi 2 Faktor - ${store.name}`" />
        <SettingsLayout>
            <div class="space-y-6">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                >
                    <HeadingSmall
                        title="Autentikasi 2 Faktor"
                        description="Kelola pengaturan autentikasi dua faktor Anda"
                    />
                </Motion>

                <Motion
                    v-if="!twoFactorEnabled"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="springPresets.bouncy"
                    >
                        <Badge variant="destructive">Nonaktif</Badge>
                    </Motion>

                    <p class="text-muted-foreground">
                        Ketika Anda mengaktifkan autentikasi dua faktor, Anda akan
                        diminta PIN keamanan saat login. PIN ini dapat diperoleh dari
                        aplikasi TOTP di ponsel Anda (seperti Google Authenticator).
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Lanjutkan Setup
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                <ShieldCheck />Aktifkan 2FA</Button
                            ></Form
                        >
                    </div>
                </Motion>

                <Motion
                    v-else
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="springPresets.bouncy"
                    >
                        <Badge variant="default">Aktif</Badge>
                    </Motion>

                    <p class="text-muted-foreground">
                        Dengan autentikasi dua faktor aktif, Anda akan diminta
                        PIN keamanan saat login, yang dapat Anda ambil dari
                        aplikasi TOTP di ponsel Anda.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                            >
                                <ShieldBan />
                                Nonaktifkan 2FA
                            </Button>
                        </Form>
                    </div>
                </Motion>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
