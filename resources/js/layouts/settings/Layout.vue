<script setup lang="ts">
/**
 * Settings Layout Component
 * Layout untuk halaman settings dengan iOS-style design, yaitu:
 * - Navigation sidebar dengan iOS grouped list styling
 * - Spring animations untuk smooth transitions
 * - Haptic-like press feedback
 * - Glass effect untuk active states
 * - Dynamic store branding dari shared props
 *
 * @author Zulfikar Hidayatullah
 */
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { toUrl, urlIsActive } from '@/lib/utils'
import { edit as editAppearance } from '@/routes/appearance'
import { edit as editProfile } from '@/routes/profile'
import { show } from '@/routes/two-factor'
import { edit as editPassword } from '@/routes/user-password'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
import { User, Lock, ShieldCheck, Palette } from 'lucide-vue-next'
import { computed } from 'vue'

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

/**
 * Navigation items dengan icons untuk iOS-style list
 */
const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: Lock,
    },
    {
        title: 'Autentikasi 2 Faktor',
        href: show(),
        icon: ShieldCheck,
    },
    {
        title: 'Tampilan',
        href: editAppearance(),
        icon: Palette,
    },
]

const currentPath = typeof window !== undefined ? window.location.pathname : ''
</script>

<template>
    <div class="px-4 py-6 md:px-6 md:py-8">
        <!-- Page Header dengan animation dan dynamic store name -->
        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="springPresets.ios"
        >
            <Heading
                title="Pengaturan"
                :description="`Kelola profil dan pengaturan akun Anda di ${store.name}`"
            />
        </Motion>

        <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
            <!-- iOS-style Sidebar Navigation -->
            <Motion
                tag="aside"
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ ...springPresets.ios, delay: 0.1 }"
                class="w-full lg:w-56"
            >
                <nav class="ios-settings-nav overflow-hidden rounded-2xl border border-border/50 bg-card/50 backdrop-blur-sm">
                    <Motion
                        v-for="(item, index) in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :initial="{ opacity: 0, x: -10 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ ...springPresets.ios, delay: staggerDelay(index) + 0.15 }"
                    >
                        <Link
                            :href="item.href"
                            :class="[
                                'ios-nav-item flex items-center gap-3 px-4 py-3.5 text-sm font-medium transition-all duration-150',
                                'border-b border-border/30 last:border-b-0',
                                'hover:bg-muted/50 active:bg-muted active:scale-[0.98]',
                                urlIsActive(item.href, currentPath)
                                    ? 'bg-primary/10 text-primary dark:bg-primary/20'
                                    : 'text-foreground/80',
                            ]"
                        >
                            <component
                                :is="item.icon"
                                :class="[
                                    'h-5 w-5 shrink-0',
                                    urlIsActive(item.href, currentPath)
                                        ? 'text-primary'
                                        : 'text-muted-foreground',
                                ]"
                            />
                            <span>{{ item.title }}</span>

                            <!-- Active indicator -->
                            <div
                                v-if="urlIsActive(item.href, currentPath)"
                                class="ml-auto h-2 w-2 rounded-full bg-primary"
                            />
                        </Link>
                    </Motion>
                </nav>
            </Motion>

            <!-- Separator untuk mobile -->
            <Separator class="lg:hidden" />

            <!-- Main Content Area -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ ...springPresets.ios, delay: 0.2 }"
                class="flex-1 lg:max-w-2xl"
            >
                <section class="ios-settings-content max-w-xl space-y-8">
                    <slot />
                </section>
            </Motion>
        </div>
    </div>
</template>

<style scoped>
@reference "tailwindcss";

/**
 * iOS-style settings navigation enhancements
 */
.ios-settings-nav {
    /* Subtle shadow untuk depth */
    --tw-shadow: 0 2px 8px oklch(0 0 0 / 0.04), 0 1px 2px oklch(0 0 0 / 0.02);
    box-shadow: var(--tw-shadow);
}

.ios-nav-item {
    /* Press feedback transition */
    transition:
        background-color 0.15s ease,
        transform 0.1s ease;
}

.ios-nav-item:active {
    transform: scale(0.98);
}

.ios-settings-content {
    /* Form sections spacing */
    @apply space-y-8;
}

/* Enhanced form section styling */
.ios-settings-content :deep(.space-y-12) {
    @apply space-y-8;
}

.ios-settings-content :deep(form) {
    @apply space-y-6;
}
</style>
