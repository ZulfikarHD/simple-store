# Animation System - iOS-Like Spring Physics

Dokumentasi sistem animasi aplikasi F&B Web App yang menggunakan `motion-v` library untuk menghasilkan animasi dengan karakteristik iOS native, yaitu spring physics yang natural dan responsive.

> **📁 Implementation:** `resources/js/composables/useMotionV.ts`  
> **🧩 Library:** `motion-v` (Vue 3 Animation Library)

---

## 🎯 Feature Overview

Animation System merupakan sistem animasi terpusat yang bertujuan untuk memberikan pengalaman pengguna yang konsisten dan menyerupai aplikasi iOS native, yaitu:

- **Spring Physics** - Animasi dengan bounce effect yang natural
- **Press Feedback** - Scale-down effect (0.97) saat element di-tap
- **Staggered Animations** - Sequential entrance animations untuk lists
- **Consistent Timing** - Preset konfigurasi yang dapat digunakan di seluruh aplikasi

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Konsistensi** | Semua halaman menggunakan preset yang sama |
| **Performance** | Animasi berbasis spring lebih smooth dari CSS transitions |
| **Maintainability** | Konfigurasi terpusat di satu composable |
| **iOS-like UX** | Karakteristik animasi mengikuti Apple Human Interface Guidelines |

---

## 💼 Business Case

### Problem Statement

Sebelum implementasi animation system:
- Animasi menggunakan `@vueuse/motion` dengan directive `v-motion`
- Konfigurasi animasi tersebar di berbagai file
- Tidak ada standar timing dan easing yang konsisten
- Pengalaman pengguna kurang menyerupai aplikasi native

### Solution

Migrasi ke `motion-v` dengan composable terpusat yang menyediakan:
- Spring presets yang sudah dikonfigurasi
- Helper functions untuk staggered animations
- Komponen `<Motion>` dan `<AnimatePresence>` yang deklaratif

### Impact

- ✅ **Konsistensi UI** - Semua animasi mengikuti standar yang sama
- ✅ **Developer Experience** - Mudah menggunakan preset yang sudah tersedia
- ✅ **Performance** - Animasi lebih smooth dengan spring physics
- ✅ **User Experience** - Feedback visual yang lebih responsive

---

## 🔄 User Flow

### Animation Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│                     Page Load                                │
├─────────────────────────────────────────────────────────────┤
│  1. Initial State (opacity: 0, y: 20)                       │
│  2. Animate to (opacity: 1, y: 0) dengan spring physics     │
│  3. Staggered delay untuk setiap element                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   User Interaction                           │
├─────────────────────────────────────────────────────────────┤
│  1. Touch/Click pada element                                │
│  2. Scale down to 0.97 (press feedback)                     │
│  3. Release → Scale back to 1.0 dengan spring               │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   List Item Animation                        │
├─────────────────────────────────────────────────────────────┤
│  Item 1: delay 0ms    → animate                             │
│  Item 2: delay 50ms   → animate                             │
│  Item 3: delay 100ms  → animate                             │
│  Item N: delay N*50ms → animate                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Technical Implementation

### Spring Presets

Konfigurasi spring physics yang tersedia di `useMotionV.ts`:

```typescript
export const springPresets = {
    // Default - Balanced spring untuk general use
    default: { stiffness: 300, damping: 30, mass: 1 },

    // Bouncy - Lebih bounce untuk badges, notifications
    bouncy: { stiffness: 400, damping: 15, mass: 0.8 },

    // Snappy - Respons cepat untuk micro-interactions
    snappy: { stiffness: 500, damping: 40, mass: 0.5 },

    // Smooth - Transisi halus untuk page transitions
    smooth: { stiffness: 200, damping: 25, mass: 1.2 },

    // Stiff - Minimal bounce untuk form validation
    stiff: { stiffness: 600, damping: 50, mass: 0.5 },

    // Gentle - Slow dan smooth untuk large elements
    gentle: { stiffness: 150, damping: 20, mass: 1.5 },

    // iOS Native - Mengikuti karakteristik iOS system animations
    ios: { stiffness: 300, damping: 25, mass: 1 },
}
```

### Stagger Delay Helper

```typescript
// Simple stagger delay untuk Motion components
export function staggerDelay(index: number, baseDelay: number = 0.05): number {
    return index * baseDelay
}
```

### Usage Examples

#### Basic Animation

```vue
<script setup lang="ts">
import { Motion } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="springPresets.ios"
    >
        <div>Animated Content</div>
    </Motion>
</template>
```

#### Staggered List Animation

```vue
<script setup lang="ts">
import { Motion } from 'motion-v'
import { springPresets, staggerDelay } from '@/composables/useMotionV'
</script>

<template>
    <Motion
        v-for="(item, index) in items"
        :key="item.id"
        :initial="{ opacity: 0, x: -20 }"
        :animate="{ opacity: 1, x: 0 }"
        :transition="{ ...springPresets.ios, delay: staggerDelay(index) }"
    >
        <div>{{ item.name }}</div>
    </Motion>
</template>
```

#### Exit Animation with AnimatePresence

```vue
<script setup lang="ts">
import { Motion, AnimatePresence } from 'motion-v'
import { springPresets } from '@/composables/useMotionV'
</script>

<template>
    <AnimatePresence>
        <Motion
            v-if="isVisible"
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="springPresets.bouncy"
        >
            <div>Conditional Content</div>
        </Motion>
    </AnimatePresence>
</template>
```

#### Press Feedback

```vue
<script setup lang="ts">
import { ref } from 'vue'

const isPressed = ref(false)

function handlePressStart() {
    isPressed.value = true
}

function handlePressEnd() {
    isPressed.value = false
}
</script>

<template>
    <button
        :class="{ 'scale-[0.97]': isPressed }"
        class="transition-transform duration-150"
        @mousedown="handlePressStart"
        @mouseup="handlePressEnd"
        @mouseleave="handlePressEnd"
        @touchstart.passive="handlePressStart"
        @touchend="handlePressEnd"
    >
        Press Me
    </button>
</template>
```

---

## 📁 Files Updated

### Pages

| Directory | Files Updated | Description |
|-----------|---------------|-------------|
| `pages/` | Home, ProductDetail, Cart, Checkout, OrderSuccess | Store pages |
| `pages/auth/` | Login, Register, ConfirmPassword, ForgotPassword, ResetPassword, TwoFactorChallenge, VerifyEmail | Authentication pages |
| `pages/settings/` | Profile, Password, Appearance, TwoFactor | Settings pages |
| `pages/Account/` | Index, Orders | Account pages |
| `pages/Admin/` | Dashboard, Orders/Index, Orders/Show, Products/Index, Products/Create, Products/Edit, Categories/Index, Categories/Create, Categories/Edit, Settings/Index | Admin pages |

### Components

| File | Description |
|------|-------------|
| `components/store/ProductCard.vue` | Product card dengan press feedback |
| `components/store/CartItem.vue` | Cart item dengan staggered animation |
| `components/mobile/UserBottomNav.vue` | Bottom nav dengan active indicator |

### Layouts

| File | Description |
|------|-------------|
| `layouts/auth/AuthSimpleLayout.vue` | Auth layout dengan entrance animation |

### Composables

| File | Description |
|------|-------------|
| `composables/useMotionV.ts` | Central animation configuration |
| `composables/useSpringAnimation.ts` | Legacy spring animation (updated) |

---

## 📋 Changelog

### Version 1.0.0 (December 2024)

#### Added
- ✅ Migrasi dari `@vueuse/motion` ke `motion-v`
- ✅ Composable `useMotionV.ts` dengan spring presets
- ✅ Helper function `staggerDelay()` untuk list animations
- ✅ iOS-like spring physics configuration

#### Changed
- ✅ Semua `v-motion` directives diganti dengan `<Motion>` components
- ✅ Animation syntax dari `:initial/:enter` ke `:initial/:animate/:transition`
- ✅ Removed `MotionPlugin` global registration (tidak diperlukan di motion-v)

#### Files Modified
- 30+ Vue files updated
- `app.ts` - Removed MotionPlugin
- `useMotionV.ts` - New composable
- `useSpringAnimation.ts` - Updated imports

---

## 🎨 Design Guidelines

### When to Use Each Preset

| Preset | Use Case | Example |
|--------|----------|---------|
| `ios` | Default untuk sebagian besar animasi | Page entrance, cards |
| `bouncy` | Playful elements, success states | Badges, notifications, success icons |
| `snappy` | Quick interactions | Button press, toggle |
| `smooth` | Large transitions | Page transitions, modals |
| `stiff` | Precise movements | Form validation, error states |
| `gentle` | Slow reveals | Hero sections, galleries |

### Animation Timing Guidelines

| Context | Delay | Duration |
|---------|-------|----------|
| Page header | 0ms | Spring-based |
| First content section | 50ms | Spring-based |
| List items | index * 50ms | Spring-based |
| Secondary content | 100-200ms | Spring-based |
| Footer elements | 200-300ms | Spring-based |

---

## 📚 Resources

- [motion-v Documentation](https://motion.vueuse.org/)
- [Apple Human Interface Guidelines - Motion](https://developer.apple.com/design/human-interface-guidelines/motion)
- [Spring Physics Explained](https://www.joshwcomeau.com/animation/a-friendly-introduction-to-spring-physics/)

---

*Document version: 1.0*  
*Author: Zulfikar Hidayatullah*  
*Last updated: December 2024*

