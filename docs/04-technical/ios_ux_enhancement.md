# iOS-like UI/UX Enhancement

Dokumen ini merinci implementasi UI/UX gaya iOS pada storefront, mengikuti contoh desain Airbnb dan referensi internal di diasection.org. Tujuan utama adalah memberikan pengalaman yang responsif, halus, dan konsisten di semua halaman.

## Ruang Lingkup
- Halaman utama, detail produk, keranjang, dan checkout diubah menjadi pengalaman iOS-like dengan animasi spring, transisi mulus, dan interaksi card yang halus.
- Komponen utama yang diubah/ditambahkan: `ProductCard.vue`, `CartItem.vue`, `Home.vue`, `ProductDetail.vue`, `Cart.vue`, `Checkout.vue`, `PageTransition.vue`, `BottomSheet.vue`, `PullToRefresh.vue`, `UserBottomNav.vue`.

## Arsitektur dan Teknologi
- Tech stack: Vue 3 + Inertia.js + Tailwind CSS v4 + @vueuse/motion (spring physics)
- Prinsip desain: clarity, depth, deference; glass navbar/footer menggunakan backdrop-filter untuk efek iOS.
- Perilaku interaksi: haptic feedback (vibration), efek tekan (scale), animasi masuk/keluar dengan timing yang konsisten.

## Panduan Pengujian
- Jalankan UI di perangkat/mobile view untuk merasakan transisi, parallax, dan gesture (swipe, pull-to-refresh, bottom sheet).
- Jalankan build untuk memastikan semua bundle ter-compile tanpa error: `yarn build`.

## Ringkas Perubahan Utama
- Menambahkan/mengubah komponen: `PageTransition.vue`, `BottomSheet.vue`, `PullToRefresh.vue`.
- Menerapkan animasi pada `ProductCard.vue`, `CartItem.vue`, dan halaman terkait (`Home.vue`, `ProductDetail.vue`, `Cart.vue`, `Checkout.vue`).
- Menambahkan utilitas composable untuk spring dan haptic feedback.

## Cara Uji Lokal (Singkat)
- Jalankan dev server: `yarn dev`
- Uji interaksi kartu produk (tekan, hover), transisi halaman, dan gesture:
  - Tarik ke bawah untuk refresh
  - Swipe left pada item keranjang untuk hapus
  - Drag bottom sheet untuk membuka/menutup


