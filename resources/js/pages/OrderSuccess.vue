<script setup lang="ts">
/**
 * Order Success Page - Halaman Konfirmasi Pesanan
 * Menampilkan detail pesanan yang berhasil dibuat
 * dengan tombol untuk redirect ke WhatsApp dan bottom navigation
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { home } from '@/routes'
import CartCounter from '@/components/store/CartCounter.vue'
import UserBottomNav from '@/components/mobile/UserBottomNav.vue'
import { Button } from '@/components/ui/button'
import {
    ShoppingBag,
    CheckCircle2,
    Package,
    User,
    Phone,
    MapPin,
    FileText,
    ExternalLink,
    Home,
    Copy,
    Check,
} from 'lucide-vue-next'

/**
 * Interface untuk order item
 */
interface OrderItemData {
    id: number
    product_name: string
    product_price: number
    quantity: number
    subtotal: number
    formatted_subtotal: string
}

/**
 * Interface untuk order data dari OrderService
 */
interface OrderData {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address: string
    notes: string | null
    items: OrderItemData[]
    subtotal: number
    delivery_fee: number
    total: number
    formatted_total: string
    status: string
    created_at: string
}

/**
 * Props yang diterima dari CheckoutController::success
 */
interface Props {
    order: OrderData
    whatsappUrl: string
}

const props = defineProps<Props>()

/**
 * State untuk copy feedback
 */
const copied = ref(false)

/**
 * Format harga item
 */
function formatPrice(price: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price)
}

/**
 * Copy order number ke clipboard
 */
async function copyOrderNumber() {
    try {
        await navigator.clipboard.writeText(props.order.order_number)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
    }
}

/**
 * Redirect ke WhatsApp
 */
function openWhatsApp() {
    window.open(props.whatsappUrl, '_blank')
}

/**
 * Confetti animation on mount
 */
onMounted(() => {
    // Add celebration effect
    const successIcon = document.querySelector('.success-icon')
    if (successIcon) {
        successIcon.classList.add('animate-bounce')
        setTimeout(() => {
            successIcon.classList.remove('animate-bounce')
        }, 1000)
    }
})
</script>

<template>
    <Head title="Pesanan Berhasil - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation (Fixed) -->
        <header class="fixed inset-x-0 top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <ShoppingBag class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <span class="text-xl font-bold text-foreground">Simple Store</span>
                </Link>

                <!-- Cart Counter & Auth -->
                <nav class="flex items-center gap-3">
                    <CartCounter :count="0" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Spacer untuk fixed header -->
        <div class="h-14 sm:h-16" />

        <!-- Main Content -->
        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Success Header -->
            <div class="mb-8 text-center">
                <div class="success-icon mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <CheckCircle2 class="h-10 w-10 text-green-600 dark:text-green-400" />
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Pesanan Berhasil Dibuat! 🎉
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Terima kasih telah berbelanja. Silakan lanjutkan ke WhatsApp untuk konfirmasi pesanan.
                </p>
            </div>

            <!-- Order Number -->
            <div class="mb-8 flex flex-col items-center justify-center gap-2 rounded-xl border border-primary/30 bg-primary/5 p-6">
                <p class="text-sm text-muted-foreground">Nomor Pesanan</p>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-primary">{{ order.order_number }}</span>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        @click="copyOrderNumber"
                    >
                        <Check v-if="copied" class="h-4 w-4 text-green-600" />
                        <Copy v-else class="h-4 w-4" />
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground">{{ order.created_at }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Customer Info -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                        <User class="h-5 w-5 text-primary" />
                        Data Penerima
                    </h2>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Nama</p>
                                <p class="font-medium text-foreground">{{ order.customer_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Telepon</p>
                                <p class="font-medium text-foreground">{{ order.customer_phone }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Alamat</p>
                                <p class="font-medium text-foreground">{{ order.customer_address }}</p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="flex items-start gap-3">
                            <FileText class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Catatan</p>
                                <p class="font-medium text-foreground">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                        <Package class="h-5 w-5 text-primary" />
                        Detail Pesanan
                    </h2>

                    <!-- Items List -->
                    <div class="mb-4 space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between rounded-lg bg-muted/50 p-3"
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    {{ item.product_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ item.quantity }} x {{ formatPrice(item.product_price) }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-foreground">
                                {{ item.formatted_subtotal }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-4 border-border" />

                    <!-- Totals -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-medium text-foreground">{{ formatPrice(order.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Ongkos Kirim</span>
                            <span class="font-medium text-green-600">
                                {{ order.delivery_fee > 0 ? formatPrice(order.delivery_fee) : 'Gratis' }}
                            </span>
                        </div>
                        <hr class="border-border" />
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-foreground">Total</span>
                            <span class="text-xl font-bold text-primary">{{ order.formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
                <Button
                    size="lg"
                    class="gap-2"
                    @click="openWhatsApp"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Konfirmasi via WhatsApp
                    <ExternalLink class="h-4 w-4" />
                </Button>

                <Link :href="home()">
                    <Button
                        variant="outline"
                        size="lg"
                        class="w-full gap-2 sm:w-auto"
                    >
                        <Home class="h-4 w-4" />
                        Kembali ke Beranda
                    </Button>
                </Link>
            </div>

            <!-- Info Note -->
            <div class="mt-8 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-900/20">
                <p class="text-center text-sm text-blue-800 dark:text-blue-200">
                    💡 <strong>Tips:</strong> Simpan nomor pesanan Anda untuk referensi.
                    Kami akan menghubungi Anda via WhatsApp untuk konfirmasi dan pembayaran.
                </p>
            </div>
        </main>

        <!-- Footer - Hidden on mobile -->
        <footer class="mt-16 hidden border-t border-border bg-muted/30 md:block">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>

        <!-- Bottom padding untuk mobile nav -->
        <div class="h-20 md:hidden" />

        <!-- Mobile Bottom Navigation -->
        <UserBottomNav />
    </div>
</template>

