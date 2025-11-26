<script setup lang="ts">
/**
 * Admin Dashboard Component
 * Menampilkan overview statistik toko dengan metrics utama, yaitu:
 * - Total orders hari ini
 * - Pending orders yang perlu diproses
 * - Total sales keseluruhan
 * - Active products count
 * - Recent orders list
 * - Browser notifications untuk pesanan baru
 * - Mobile redirect ke halaman orders (order-centric view)
 *
 * @author Zulfikar Hidayatullah
 */
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { OrderStatusBadge } from '@/components/store'
import PriceDisplay from '@/components/store/PriceDisplay.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { dashboard } from '@/routes/admin'
import { index as productsIndex } from '@/routes/admin/products'
import { index as categoriesIndex } from '@/routes/admin/categories'
import { index as ordersIndex } from '@/routes/admin/orders'
import { useOrderNotifications } from '@/composables/useOrderNotifications'
import {
    ShoppingBag,
    Clock,
    TrendingUp,
    Package,
    Users,
    Calendar,
    FolderTree,
    Bell,
    BellOff,
} from 'lucide-vue-next'

/**
 * Setup browser notifications untuk pesanan baru
 */
const {
    isSupported: notificationSupported,
    notificationPermission,
    requestPermission,
    watchPendingOrders,
} = useOrderNotifications()

/**
 * Mobile detection dan redirect ke orders view
 * Admin pada mobile sebaiknya langsung melihat pesanan
 */
function checkMobileAndRedirect(): void {
    const isMobile = window.innerWidth < 768
    if (isMobile) {
        router.visit(ordersIndex().url, { replace: true })
    }
}

onMounted(() => {
    watchPendingOrders()
    // Redirect ke orders page pada mobile untuk order-centric experience
    checkMobileAndRedirect()
})

interface OrderItem {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    total: number
    status: string
    items_count: number
    created_at: string
    created_at_human: string
}

interface DashboardStats {
    today_orders: number
    pending_orders: number
    total_sales: number
    active_products: number
    recent_orders: OrderItem[]
    order_status_breakdown: Record<string, number>
}

interface Props {
    stats: DashboardStats
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

/**
 * Stats cards configuration untuk menampilkan metrics utama
 * dengan icon, warna, dan format yang sesuai
 */
const statsCards = [
    {
        title: 'Orders Hari Ini',
        value: props.stats.today_orders,
        icon: ShoppingBag,
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-50 dark:bg-blue-950',
        description: 'Total pesanan masuk hari ini',
    },
    {
        title: 'Pending Orders',
        value: props.stats.pending_orders,
        icon: Clock,
        color: 'text-yellow-600 dark:text-yellow-400',
        bgColor: 'bg-yellow-50 dark:bg-yellow-950',
        description: 'Pesanan menunggu konfirmasi',
    },
    {
        title: 'Produk Aktif',
        value: props.stats.active_products,
        icon: Package,
        color: 'text-green-600 dark:text-green-400',
        bgColor: 'bg-green-50 dark:bg-green-950',
        description: 'Total produk yang tersedia',
    },
]

/**
 * Format angka ke format Indonesia dengan separator titik
 */
function formatNumber(value: number): string {
    return new Intl.NumberFormat('id-ID').format(value)
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Dashboard Admin
                </h1>
                <p class="text-muted-foreground">
                    Overview statistik dan monitoring performa toko
                </p>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Sales Card -->
                <Card class="overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Total Penjualan
                        </CardTitle>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-950"
                        >
                            <TrendingUp
                                class="h-5 w-5 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-1">
                            <PriceDisplay
                                :price="stats.total_sales"
                                size="xl"
                            />
                            <p class="text-xs text-muted-foreground">
                                Total revenue keseluruhan
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Other Stats Cards -->
                <Card
                    v-for="stat in statsCards"
                    :key="stat.title"
                    class="overflow-hidden"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ stat.title }}
                        </CardTitle>
                        <div
                            :class="[
                                'flex h-10 w-10 items-center justify-center rounded-full',
                                stat.bgColor,
                            ]"
                        >
                            <component
                                :is="stat.icon"
                                :class="['h-5 w-5', stat.color]"
                            />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-1">
                            <div class="text-2xl font-bold">
                                {{ formatNumber(stat.value) }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stat.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Order Status Breakdown -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Orders -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShoppingBag class="h-5 w-5" />
                            Pesanan Terbaru
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="stats.recent_orders.length === 0"
                            class="flex flex-col items-center justify-center py-8 text-center"
                        >
                            <ShoppingBag
                                class="mb-2 h-12 w-12 text-muted-foreground/40"
                            />
                            <p class="text-sm text-muted-foreground">
                                Belum ada pesanan
                            </p>
                        </div>

                        <div
                            v-else
                            class="space-y-4"
                        >
                            <div
                                v-for="order in stats.recent_orders"
                                :key="order.id"
                                class="flex flex-col gap-2 rounded-lg border p-4 transition-colors hover:bg-accent"
                            >
                                <!-- Order Header -->
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm font-medium">
                                                {{ order.order_number }}
                                            </span>
                                            <OrderStatusBadge
                                                :status="order.status"
                                            />
                                        </div>
                                        <p class="text-sm font-medium">
                                            {{ order.customer_name }}
                                        </p>
                                    </div>
                                    <PriceDisplay
                                        :price="order.total"
                                        size="sm"
                                    />
                                </div>

                                <!-- Order Meta -->
                                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Users class="h-3 w-3" />
                                        {{ order.customer_phone }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Package class="h-3 w-3" />
                                        {{ order.items_count }} item
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Calendar class="h-3 w-3" />
                                        {{ order.created_at_human }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status Breakdown -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5" />
                            Status Pesanan
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="(count, status) in stats.order_status_breakdown"
                                :key="status"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <OrderStatusBadge :status="status" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold">
                                        {{ formatNumber(count) }}
                                    </span>
                                    <span class="text-sm text-muted-foreground">
                                        orders
                                    </span>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div
                                v-if="
                                    Object.keys(stats.order_status_breakdown)
                                        .length === 0
                                "
                                class="flex flex-col items-center justify-center py-8 text-center"
                            >
                                <Clock
                                    class="mb-2 h-12 w-12 text-muted-foreground/40"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Belum ada data status
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="productsIndex().url">
                            <Badge
                                variant="outline"
                                class="cursor-pointer px-4 py-2 hover:bg-accent"
                            >
                                <Package class="mr-2 h-4 w-4" />
                                Kelola Produk
                            </Badge>
                        </Link>
                        <Link :href="categoriesIndex().url">
                            <Badge
                                variant="outline"
                                class="cursor-pointer px-4 py-2 hover:bg-accent"
                            >
                                <FolderTree class="mr-2 h-4 w-4" />
                                Kelola Kategori
                            </Badge>
                        </Link>

                        <!-- Notification Permission Button -->
                        <Button
                            v-if="notificationSupported && notificationPermission !== 'granted'"
                            variant="outline"
                            size="sm"
                            class="gap-2"
                            @click="requestPermission"
                        >
                            <Bell class="h-4 w-4" />
                            Aktifkan Notifikasi
                        </Button>
                        <Badge
                            v-else-if="notificationSupported && notificationPermission === 'granted'"
                            variant="secondary"
                            class="px-4 py-2"
                        >
                            <Bell class="mr-2 h-4 w-4 text-green-600" />
                            Notifikasi Aktif
                        </Badge>
                        <Badge
                            v-else-if="notificationSupported && notificationPermission === 'denied'"
                            variant="destructive"
                            class="px-4 py-2"
                        >
                            <BellOff class="mr-2 h-4 w-4" />
                            Notifikasi Diblokir
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

