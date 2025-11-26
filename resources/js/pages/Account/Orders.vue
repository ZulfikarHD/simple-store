<script setup lang="ts">
/**
 * Account Orders Page
 * Halaman riwayat pesanan user dengan list pesanan
 * dan status tracking untuk setiap pesanan
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { home } from '@/routes'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { OrderStatusBadge } from '@/components/store'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import {
    ShoppingBag,
    ArrowLeft,
    Package,
    Clock,
    ChevronRight,
    ShoppingCart,
} from 'lucide-vue-next'

/**
 * Interface untuk order item
 */
interface OrderItem {
    id: number
    product_name: string
    quantity: number
    subtotal: number
}

/**
 * Interface untuk order data
 */
interface Order {
    id: number
    order_number: string
    total: number
    status: string
    items: OrderItem[]
    items_count: number
    created_at: string
    created_at_human: string
}

/**
 * Props dari controller
 */
interface Props {
    orders: Order[]
}

const props = defineProps<Props>()

/**
 * Format tanggal
 */
function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head title="Riwayat Pesanan - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-6 sm:px-6 sm:py-8">
            <!-- Back Button -->
            <Link
                href="/account"
                class="mb-4 inline-flex h-11 items-center gap-2 rounded-lg px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Akun
            </Link>

            <!-- Page Title -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Riwayat Pesanan
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ orders.length }} pesanan ditemukan
                </p>
            </div>

            <!-- Empty State -->
            <div
                v-if="orders.length === 0"
                class="flex flex-col items-center justify-center py-12"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                    <ShoppingCart class="h-8 w-8 text-muted-foreground" />
                </div>
                <h2 class="mb-2 text-lg font-semibold text-foreground">Belum Ada Pesanan</h2>
                <p class="mb-6 text-center text-muted-foreground">
                    Anda belum pernah melakukan pemesanan. Yuk mulai belanja!
                </p>
                <Link :href="home()">
                    <Button class="gap-2">
                        <ShoppingBag class="h-4 w-4" />
                        Mulai Belanja
                    </Button>
                </Link>
            </div>

            <!-- Orders List -->
            <div v-else class="space-y-4">
                <Card
                    v-for="order in orders"
                    :key="order.id"
                    class="overflow-hidden"
                >
                    <CardContent class="p-4">
                        <!-- Header Row -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-mono text-sm font-semibold text-primary">
                                    {{ order.order_number }}
                                </p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-muted-foreground">
                                    <Clock class="h-3 w-3" />
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <OrderStatusBadge :status="order.status" />
                        </div>

                        <!-- Items Preview -->
                        <div class="mt-3 rounded-lg bg-muted/50 p-3">
                            <div class="flex items-center gap-2 text-sm">
                                <Package class="h-4 w-4 text-muted-foreground" />
                                <span class="text-muted-foreground">{{ order.items_count }} item</span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground truncate">
                                {{ order.items.map(item => item.product_name).join(', ') }}
                            </p>
                        </div>

                        <!-- Footer Row -->
                        <div class="mt-3 flex items-center justify-between">
                            <PriceDisplay :price="order.total" size="lg" class="font-bold" />
                            <Button variant="ghost" size="sm" class="gap-1">
                                Detail
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>

