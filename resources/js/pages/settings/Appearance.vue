<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { springPresets, staggerDelay } from '@/composables/useMotionV';
import { computed } from 'vue';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

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
        title: 'Pengaturan Tampilan',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Pengaturan Tampilan - ${store.name}`" />

        <SettingsLayout>
            <div class="space-y-6">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springPresets.ios"
                >
                    <HeadingSmall
                        title="Pengaturan Tampilan"
                        description="Sesuaikan tampilan aplikasi sesuai preferensi Anda"
                    />
                </Motion>
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springPresets.ios, delay: staggerDelay(1) }"
                >
                    <AppearanceTabs />
                </Motion>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
