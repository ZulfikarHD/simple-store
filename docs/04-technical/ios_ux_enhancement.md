# iOS-like UI/UX Enhancement

Dokumen ini merinci implementasi UI/UX gaya iOS pada aplikasi Simple Store, mengikuti contoh desain Airbnb dan referensi internal di diasection.org. Tujuan utama adalah memberikan pengalaman yang responsif, halus, dan konsisten di semua halaman.

## Ruang Lingkup

### Halaman yang Di-enhance
- **Storefront**: Home, ProductDetail, Cart, Checkout
- **Authentication**: Login, Register
- **Navigation**: UserBottomNav, AuthSimpleLayout

### Komponen yang Diubah/Ditambahkan
| Komponen | Lokasi | Perubahan |
|----------|--------|-----------|
| `ProductCard.vue` | components/store | Press feedback, spring animations, staggered entrance |
| `CartItem.vue` | components/store | Swipe-to-delete, quantity animations |
| `UserBottomNav.vue` | components/mobile | Glass effect, badge bounce, haptic feedback |
| `PageTransition.vue` | components | Smooth page transitions |
| `BottomSheet.vue` | components/ui | Drag-to-dismiss gesture |
| `PullToRefresh.vue` | components/mobile | iOS-style pull refresh |
| `AuthSimpleLayout.vue` | layouts/auth | Glass card, spring animations, decorative background |
| `Login.vue` | pages/auth | Form animations, haptic feedback, shake on error |
| `Register.vue` | pages/auth | Form animations, haptic feedback, shake on error |

## Arsitektur dan Teknologi

### Tech Stack
- **Vue 3** + **Inertia.js** + **Tailwind CSS v4**
- **@vueuse/motion** untuk spring physics animations

### Prinsip Desain iOS
1. **Clarity**: Text legible, icons precise
2. **Depth**: Layered UI dengan shadows dan blur
3. **Deference**: Content-first, UI unobtrusive

### Implementasi Utama
- Glass navbar/footer menggunakan `backdrop-filter` untuk efek iOS
- Haptic feedback (vibration) untuk tactile response
- Press effect (scale 0.97) pada interactive elements
- Spring physics untuk natural bounce animations
- Staggered animations untuk list/grid items

## Composables yang Dibuat

### `useSpringAnimation.ts`
```typescript
// Spring presets: default, bouncy, snappy, smooth, stiff, gentle
const { animateScale, animatePosition, animate, reset } = useSpringAnimation(target, 'bouncy')
```

### `useHapticFeedback.ts`
```typescript
// Haptic types: light, medium, heavy, selection, success, warning, error
const haptic = useHapticFeedback()
haptic.medium() // trigger haptic
```

### `useShakeAnimation.ts`
```typescript
// Shake animation untuk error states
const { shakeClass, shake } = useShakeAnimation()
shake() // trigger shake
```

## CSS Variables untuk iOS Feel

```css
/* Spring Timing Functions */
--ios-spring-bounce: cubic-bezier(0.175, 0.885, 0.32, 1.275);
--ios-spring-smooth: cubic-bezier(0.23, 1, 0.32, 1);
--ios-spring-snappy: cubic-bezier(0.25, 0.46, 0.45, 0.94);

/* Duration */
--ios-duration-fast: 200ms;
--ios-duration-normal: 350ms;

/* Interaction */
--ios-press-scale: 0.97;

/* Shadows */
--ios-shadow-md: 0 4px 6px oklch(0 0 0 / 0.05), 0 2px 4px oklch(0 0 0 / 0.04);
--ios-shadow-lg: 0 10px 15px oklch(0 0 0 / 0.08), 0 4px 6px oklch(0 0 0 / 0.05);
```

## Utility Classes Tersedia

| Class | Deskripsi |
|-------|-----------|
| `.ios-press` | Press effect pada active state |
| `.ios-hover-lift` | Lift effect pada hover |
| `.ios-card` | Card dengan iOS-style shadow dan interactions |
| `.ios-glass` | Frosted glass effect |
| `.ios-button` | Button dengan press feedback |
| `.ios-input` | Input field dengan iOS styling |
| `.ios-navbar` | Navigation bar dengan glass effect |
| `.ios-tabbar` | Tab bar dengan glass effect |
| `.ios-scroll` | Momentum scrolling |
| `.ios-snap-scroll` | Snap scroll untuk carousel |
| `.animate-ios-bounce-in` | Bounce entrance animation |
| `.animate-ios-slide-up` | Slide up animation |
| `.animate-ios-shake` | Shake animation untuk errors |
| `.animate-ios-pop` | Pop animation untuk badges |
| `.stagger-1` sampai `.stagger-8` | Animation delay helpers |

## Halaman Authentication

### Login (`/login`)
- iOS-style glass card container
- Animated logo dengan rotation
- Staggered form field animations
- Input focus scale effect
- Shake animation pada error
- Haptic feedback pada interactions

### Register (`/register`)
- Sama dengan Login dengan tambahan fields
- Progressive form field reveal
- Real-time validation feedback

## Panduan Pengujian

### Development
```bash
yarn dev
# atau
composer run dev
```

### Production Build
```bash
yarn build
```

### Testing Manual
1. Buka di mobile view (Chrome DevTools atau device asli)
2. Test interaksi:
   - Tap card products (scale feedback)
   - Pull down untuk refresh (PullToRefresh)
   - Swipe left pada cart item (delete action)
   - Navigate antar halaman (smooth transitions)
3. Test forms:
   - Focus input (scale animation)
   - Submit dengan error (shake animation)
   - Check haptic feedback (jika device support)

## Changelog

### v1.1.0 - Authentication Enhancement
- Ditambahkan iOS-like styling pada Login page
- Ditambahkan iOS-like styling pada Register page
- Enhanced AuthSimpleLayout dengan glass card dan decorative background
- Bahasa Indonesia pada form labels dan messages

### v1.0.0 - Initial iOS Enhancement
- Implementasi spring animations pada storefront
- Komponen baru: PageTransition, BottomSheet, PullToRefresh
- Enhanced components: ProductCard, CartItem, UserBottomNav
- Halaman: Home, ProductDetail, Cart, Checkout
