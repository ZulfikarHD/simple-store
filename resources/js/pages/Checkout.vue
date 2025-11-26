<script setup lang="ts">
/**
 * Checkout Page - Halaman Checkout
 * Menampilkan form data customer dan ringkasan pesanan
 * dengan validasi real-time dan integrasi WhatsApp
 *
 * @author Zulfikar Hidayatullah
 */
import { Head, Link } from '@inertiajs/vue3'
import { Form } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { home, show as showCart } from '@/routes/cart'
import { store as checkoutStore } from '@/actions/App/Http/Controllers/CheckoutController'
import CartCounter from '@/components/store/CartCounter.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    ShoppingBag,
    ArrowLeft,
    Package,
    User,
    Phone,
    MapPin,
    FileText,
    Loader2,
    AlertCircle,
} from 'lucide-vue-next'

/**
 * Interface untuk cart item dari CartService
 */
interface CartItemData {
    id: number
    product: {
        id: number
        name: string
        slug: string
        price: number
        image?: string | null
        is_available: boolean
    }
    quantity: number
    subtotal: number
}

/**
 * Interface untuk cart data dari CartController
 */
interface CartData {
    id: number
    items: CartItemData[]
    total_items: number
    subtotal: number
    formatted_subtotal: string
}

/**
 * Props yang diterima dari CheckoutController::show
 */
interface Props {
    cart: CartData
}

const props = defineProps<Props>()

/**
 * Computed untuk format subtotal
 */
const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.cart.subtotal)
})

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
</script>

<template>
    <Head title="Checkout - Simple Store">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header Navigation - Mobile Optimized -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <Link href="/" class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary sm:h-10 sm:w-10">
                        <ShoppingBag class="h-4 w-4 text-primary-foreground sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-lg font-bold text-foreground sm:text-xl">Simple Store</span>
                </Link>

                <!-- Cart Counter & Auth - Hidden auth on mobile -->
                <nav class="flex items-center gap-2 sm:gap-3">
                    <CartCounter :count="cart.total_items" />

                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="hidden rounded-lg px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent sm:inline-flex"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="hidden rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:inline-flex"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content - Mobile Optimized -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <!-- Back Button - Touch-friendly -->
            <Link
                :href="showCart()"
                class="mb-4 inline-flex h-11 items-center gap-2 rounded-lg px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:mb-6 sm:h-auto sm:px-0"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Keranjang
            </Link>

            <!-- Page Title - Responsive -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                    Checkout
                </h1>
                <p class="mt-1 text-sm text-muted-foreground sm:mt-2 sm:text-base">
                    Lengkapi data pengiriman untuk melanjutkan pesanan
                </p>
            </div>

            <!-- Checkout Form - Mobile Optimized -->
            <Form
                v-bind="checkoutStore.form()"
                #default="{ errors, processing, hasErrors }"
                class="grid gap-6 lg:grid-cols-3 lg:gap-8"
            >
                <!-- Form Fields -->
                <div class="space-y-4 sm:space-y-6 lg:col-span-2">
                    <!-- Error Alert -->
                    <div
                        v-if="hasErrors && errors.checkout"
                        class="rounded-lg border border-destructive/50 bg-destructive/10 p-3 sm:p-4"
                    >
                        <div class="flex items-center gap-2 text-destructive">
                            <AlertCircle class="h-5 w-5 shrink-0" />
                            <span class="text-sm font-medium sm:text-base">{{ errors.checkout }}</span>
                        </div>
                    </div>

                    <!-- Data Penerima Section -->
                    <div class="rounded-lg border border-border bg-card p-4 sm:rounded-xl sm:p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <User class="h-5 w-5 text-primary" />
                            Data Penerima
                        </h2>

                        <div class="space-y-4">
                            <!-- Nama Lengkap -->
                            <div class="space-y-2">
                                <Label for="customer_name" class="text-sm sm:text-base">Nama Lengkap *</Label>
                                <Input
                                    id="customer_name"
                                    name="customer_name"
                                    type="text"
                                    placeholder="Masukkan nama lengkap"
                                    class="h-11 text-base sm:h-10 sm:text-sm"
                                    :aria-invalid="!!errors.customer_name"
                                />
                                <p v-if="errors.customer_name" class="text-sm text-destructive">
                                    {{ errors.customer_name }}
                                </p>
                            </div>

                            <!-- Nomor Telepon -->
                            <div class="space-y-2">
                                <Label for="customer_phone" class="text-sm sm:text-base">
                                    Nomor Telepon (WhatsApp) *
                                </Label>
                                <Input
                                    id="customer_phone"
                                    name="customer_phone"
                                    type="tel"
                                    inputmode="tel"
                                    placeholder="08xxxxxxxxxx"
                                    class="h-11 text-base sm:h-10 sm:text-sm"
                                    :aria-invalid="!!errors.customer_phone"
                                />
                                <p v-if="errors.customer_phone" class="text-sm text-destructive">
                                    {{ errors.customer_phone }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Pesanan akan dikonfirmasi via WhatsApp ke nomor ini
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman Section -->
                    <div class="rounded-lg border border-border bg-card p-4 sm:rounded-xl sm:p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-foreground sm:mb-6 sm:text-lg">
                            <MapPin class="h-5 w-5 text-primary" />
                            Alamat Pengiriman
                        </h2>

                        <div class="space-y-4">
                            <!-- Alamat Lengkap -->
                            <div class="space-y-2">
                                <Label for="customer_address" class="text-sm sm:text-base">Alamat Lengkap *</Label>
                                <textarea
                                    id="customer_address"
                                    name="customer_address"
                                    rows="4"
                                    placeholder="Masukkan alamat lengkap dengan RT/RW, kelurahan, kecamatan, kota, dan kode pos"
                                    class="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-3 text-base shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 sm:py-2 sm:text-sm"
                                    :aria-invalid="!!errors.customer_address"
                                ></textarea>
                                <p v-if="errors.customer_address" class="text-sm text-destructive">
                                    {{ errors.customer_address }}
                                </p>
                            </div>

                            <!-- Catatan -->
                            <div class="space-y-2">
                                <Label for="notes" class="text-sm sm:text-base">
                                    Catatan (Opsional)
                                </Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="Catatan tambahan untuk pesanan (misal: warna, ukuran, patokan alamat, dll)"
                                    class="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-3 text-base shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 sm:py-2 sm:text-sm"
                                    :aria-invalid="!!errors.notes"
                                ></textarea>
                                <p v-if="errors.notes" class="text-sm text-destructive">
                                    {{ errors.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-xl border border-border bg-card p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <Package class="h-5 w-5 text-primary" />
                            Ringkasan Pesanan
                        </h2>

                        <!-- Items List -->
                        <div class="mb-4 max-h-64 space-y-3 overflow-y-auto">
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="flex items-center gap-3 rounded-lg bg-muted/50 p-3"
                            >
                                <!-- Product Image -->
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-muted">
                                    <img
                                        v-if="item.product.image"
                                        :src="`/storage/${item.product.image}`"
                                        :alt="item.product.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center">
                                        <Package class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-foreground">
                                        {{ item.product.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ item.quantity }} x {{ formatPrice(item.product.price) }}
                                    </p>
                                </div>

                                <!-- Subtotal -->
                                <p class="text-sm font-medium text-foreground">
                                    {{ formatPrice(item.subtotal) }}
                                </p>
                            </div>
                        </div>

                        <hr class="my-4 border-border" />

                        <!-- Summary Details -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal ({{ cart.total_items }} item)</span>
                                <span class="font-medium text-foreground">{{ formattedSubtotal }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Ongkos Kirim</span>
                                <span class="font-medium text-green-600">Gratis</span>
                            </div>

                            <hr class="border-border" />

                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-foreground">Total</span>
                                <span class="text-xl font-bold text-primary">{{ formattedSubtotal }}</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            size="lg"
                            class="mt-6 w-full gap-2"
                            :disabled="processing"
                        >
                            <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                            <template v-else>
                                Pesan via WhatsApp
                            </template>
                        </Button>

                        <!-- Info -->
                        <p class="mt-4 text-center text-xs text-muted-foreground">
                            Dengan memesan, Anda akan diarahkan ke WhatsApp untuk konfirmasi pesanan
                        </p>
                    </div>
                </div>
            </Form>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-border bg-muted/30">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Simple Store. Dibuat oleh Zulfikar Hidayatullah.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

