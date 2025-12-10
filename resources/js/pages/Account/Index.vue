<script setup lang="ts">
/**
 * Account Index Page
 * Halaman akun user dengan profile info untuk logged-in users
 * dan login/register prompt untuk guest users
 * dengan mobile bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { home } from '@/routes'
import { login, register } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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
    router.post('/logout')
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
</script>

<template>
    <Head title="Akun Saya - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation (Fixed) -->
        <header class="fixed inset-x-0 top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>

                <!-- Desktop Auth Links -->
                <nav class="hidden items-center gap-3 sm:flex">
                    <Link
                        v-if="isAuthenticated"
                        href="/settings/profile"
                        class="rounded-lg px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    >
                        Pengaturan
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-6 sm:px-6 sm:py-8">
            <!-- Guest View -->
            <div v-if="!isAuthenticated" class="flex flex-col items-center justify-center py-12">
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                    <User class="h-10 w-10 text-muted-foreground" />
                </div>
                <h1 class="mb-2 text-2xl font-bold text-foreground">Masuk ke Akun Anda</h1>
                <p class="mb-6 text-center text-muted-foreground">
                    Masuk atau daftar untuk melihat riwayat pesanan dan mengelola akun Anda.
                </p>
                <div class="flex flex-col gap-3 w-full max-w-xs">
                    <Link :href="login()">
                        <Button class="w-full h-12">
                            Masuk
                        </Button>
                    </Link>
                    <Link :href="register()">
                        <Button variant="outline" class="w-full h-12">
                            Daftar Akun Baru
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Authenticated View -->
            <div v-else class="space-y-6">
                <!-- Profile Card -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                <User class="h-8 w-8 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h1 class="text-xl font-bold text-foreground truncate">
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
                                <Badge
                                    v-if="user.email_verified_at"
                                    variant="secondary"
                                    class="mt-2 gap-1"
                                >
                                    <ShieldCheck class="h-3 w-3" />
                                    Email Terverifikasi
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Menu Items -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Menu</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="divide-y">
                            <Link
                                v-for="item in menuItems"
                                :key="item.title"
                                :href="item.href"
                                class="flex items-center gap-4 p-4 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                    <component :is="item.icon" class="h-5 w-5 text-primary" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-foreground">{{ item.title }}</p>
                                    <p class="text-sm text-muted-foreground truncate">{{ item.description }}</p>
                                </div>
                                <ChevronRight class="h-5 w-5 text-muted-foreground" />
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Logout Button -->
                <Button
                    variant="outline"
                    class="w-full h-12 gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    @click="handleLogout"
                >
                    <LogOut class="h-4 w-4" />
                    Keluar
                </Button>
            </div>
        </main>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>


