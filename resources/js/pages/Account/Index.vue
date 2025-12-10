<script setup lang="ts">
/**
 * Account Index Page
 * Halaman akun user dengan profile info untuk logged-in users
 * dan login/register prompt untuk guest users dengan iOS-like animations
 * menggunakan motion-v dan bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { Motion, AnimatePresence } from 'motion-v'
import { computed, ref } from 'vue'
import { home } from '@/routes'
import { login, register } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useHapticFeedback } from '@/composables/useHapticFeedback'
import { springPresets } from '@/composables/useMotionV'
import {
    ShoppingBag,
    User,
    Mail,
    Calendar,
    Settings,
    LogOut,
    ChevronRight,
    ShieldCheck,
    Package,
} from 'lucide-vue-next'

const page = usePage()
const haptic = useHapticFeedback()

/**
 * Press states untuk iOS-like feedback
 */
const isLogoutPressed = ref(false)
const pressedMenuItem = ref<string | null>(null)

/**
 * Computed untuk user data
 */
const user = computed(() => (page.props as { auth?: { user?: { name: string; email: string; created_at: string; email_verified_at?: string } } }).auth?.user)

/**
 * Computed untuk cek apakah user sudah login
 */
const isAuthenticated = computed(() => !!user.value)

/**
 * Format tanggal bergabung
 */
function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
}

/**
 * Handle logout
 */
function handleLogout() {
    haptic.medium()
    router.post('/logout')
}

/**
 * Handle menu item press
 */
function handleMenuPress(itemTitle: string) {
    pressedMenuItem.value = itemTitle
    haptic.light()
}

/**
 * Menu items untuk user yang sudah login
 */
const menuItems = [
    {
        title: 'Riwayat Pesanan',
        description: 'Lihat pesanan yang pernah Anda buat',
        icon: Package,
        href: '/account/orders',
    },
    {
        title: 'Pengaturan Akun',
        description: 'Ubah profil dan password',
        icon: Settings,
        href: '/settings/profile',
    },
    {
        title: 'Keamanan',
        description: 'Two-factor authentication',
        icon: ShieldCheck,
        href: '/settings/two-factor',
    },
]

/**
 * Spring transitions untuk iOS-like animations
 */
const springTransition = { type: 'spring' as const, ...springPresets.ios }
const bouncyTransition = { type: 'spring' as const, ...springPresets.bouncy }
const snappyTransition = { type: 'spring' as const, ...springPresets.snappy }
</script>

<template>
    <Head title="Akun Saya - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation dengan iOS Glass Effect (Fixed) -->
        <header class="ios-navbar fixed inset-x-0 top-0 z-50 border-b border-border/30">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="springTransition"
                >
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-sm sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>
                </Motion>

                <!-- Desktop Auth Links -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                    tag="nav"
                    class="hidden items-center gap-3 sm:flex"
                >
                    <Link
                        v-if="isAuthenticated"
                        href="/settings/profile"
                        class="ios-button rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                    >
                        Pengaturan
                    </Link>
                </Motion>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-6 sm:px-6 sm:py-8">
            <!-- Guest View -->
            <AnimatePresence>
                <Motion
                    v-if="!isAuthenticated"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -20 }"
                    :transition="springTransition"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <Motion
                        :initial="{ scale: 0 }"
                        :animate="{ scale: 1 }"
                        :transition="{ ...bouncyTransition, delay: 0.1 }"
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-muted"
                    >
                    <User class="h-10 w-10 text-muted-foreground" />
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.2 }"
                    >
                <h1 class="mb-2 text-2xl font-bold text-foreground">Masuk ke Akun Anda</h1>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.25 }"
                    >
                <p class="mb-6 text-center text-muted-foreground">
                    Masuk atau daftar untuk melihat riwayat pesanan dan mengelola akun Anda.
                </p>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ ...springTransition, delay: 0.3 }"
                        class="flex w-full max-w-xs flex-col gap-3"
                    >
                    <Link :href="login()">
                            <Button class="ios-button h-12 w-full rounded-xl">
                            Masuk
                        </Button>
                    </Link>
                    <Link :href="register()">
                            <Button variant="outline" class="ios-button h-12 w-full rounded-xl">
                            Daftar Akun Baru
                        </Button>
                    </Link>
                    </Motion>
                </Motion>
            </AnimatePresence>

            <!-- Authenticated View -->
            <div v-if="isAuthenticated" class="space-y-6">
                <!-- Profile Card dengan animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="springTransition"
                >
                    <Card class="ios-card overflow-hidden rounded-2xl border-border/50">
                    <CardContent class="pt-6">
                        <div class="flex items-start gap-4">
                                <Motion
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :transition="{ ...bouncyTransition, delay: 0.1 }"
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10"
                                >
                                <User class="h-8 w-8 text-primary" />
                                </Motion>
                                <div class="min-w-0 flex-1">
                                    <h1 class="truncate text-xl font-bold text-foreground">
                                    {{ user.name }}
                                </h1>
                                <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                    <Mail class="h-4 w-4" />
                                    <span class="truncate">{{ user.email }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                    <Calendar class="h-4 w-4" />
                                    <span>Bergabung {{ formatDate(user.created_at) }}</span>
                                </div>
                                    <AnimatePresence>
                                        <Motion
                                            v-if="user.email_verified_at"
                                            :initial="{ scale: 0, opacity: 0 }"
                                            :animate="{ scale: 1, opacity: 1 }"
                                            :transition="bouncyTransition"
                                        >
                                <Badge
                                    variant="secondary"
                                    class="mt-2 gap-1"
                                >
                                    <ShieldCheck class="h-3 w-3" />
                                    Email Terverifikasi
                                </Badge>
                                        </Motion>
                                    </AnimatePresence>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                </Motion>

                <!-- Menu Items dengan staggered animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.1 }"
                >
                    <Card class="ios-card overflow-hidden rounded-2xl border-border/50">
                    <CardHeader>
                        <CardTitle class="text-lg">Menu</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                            <div class="divide-y divide-border/50">
                                <Motion
                                    v-for="(item, index) in menuItems"
                                    :key="item.title"
                                    :initial="{ opacity: 0, x: -20 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ ...springTransition, delay: 0.15 + index * 0.05 }"
                                >
                                    <Motion
                                        :animate="{
                                            scale: pressedMenuItem === item.title ? 0.98 : 1,
                                            backgroundColor: pressedMenuItem === item.title ? 'var(--muted)' : 'transparent',
                                        }"
                                        :transition="snappyTransition"
                                    >
                            <Link
                                :href="item.href"
                                class="flex items-center gap-4 p-4 transition-colors hover:bg-muted/50"
                                            @mousedown="handleMenuPress(item.title)"
                                            @mouseup="pressedMenuItem = null"
                                            @mouseleave="pressedMenuItem = null"
                                            @touchstart.passive="handleMenuPress(item.title)"
                                            @touchend="pressedMenuItem = null"
                            >
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                    <component :is="item.icon" class="h-5 w-5 text-primary" />
                                </div>
                                            <div class="min-w-0 flex-1">
                                    <p class="font-medium text-foreground">{{ item.title }}</p>
                                                <p class="truncate text-sm text-muted-foreground">{{ item.description }}</p>
                                </div>
                                <ChevronRight class="h-5 w-5 text-muted-foreground" />
                            </Link>
                                    </Motion>
                                </Motion>
                        </div>
                    </CardContent>
                </Card>
                </Motion>

                <!-- Logout Button dengan press feedback -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ ...springTransition, delay: 0.3 }"
                >
                    <Motion
                        :animate="{ scale: isLogoutPressed ? 0.97 : 1 }"
                        :transition="snappyTransition"
                    >
                <Button
                    variant="outline"
                            class="ios-button h-12 w-full gap-2 rounded-xl text-destructive hover:bg-destructive/10 hover:text-destructive"
                    @click="handleLogout"
                            @mousedown="isLogoutPressed = true"
                            @mouseup="isLogoutPressed = false"
                            @mouseleave="isLogoutPressed = false"
                            @touchstart.passive="isLogoutPressed = true"
                            @touchend="isLogoutPressed = false"
                >
                    <LogOut class="h-4 w-4" />
                    Keluar
                </Button>
                    </Motion>
                </Motion>
            </div>
        </main>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>
