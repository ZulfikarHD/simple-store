# iOS Design System Refactoring - Sprint 8

Dokumentasi refactoring desain aplikasi F&B Web App ke iOS-style modern e-commerce design, yaitu: implementasi glass effects, spring animations, haptic feedback, dan premium visual styling yang konsisten di seluruh aplikasi.

> **📁 Implementation:** `resources/css/app.css`, `resources/js/layouts/`, `resources/js/pages/`  
> **🧩 Library:** `motion-v`, Tailwind CSS v4

---

## 🎯 Feature Overview

iOS Design System Refactoring merupakan pembaruan komprehensif yang bertujuan untuk meningkatkan pengalaman pengguna dengan desain modern yang menyerupai aplikasi iOS native, yaitu:

- **Glass Effects** - Frosted glass dengan backdrop blur untuk navbar, cards, dan modals
- **Spring Animations** - Natural bounce animations menggunakan `motion-v` library
- **Haptic Feedback** - Tactile response untuk interaksi touch
- **Press Feedback** - Scale-down effect (0.97) saat element di-tap
- **Premium Gradients** - Subtle gradient backgrounds untuk visual depth
- **Consistent Spacing** - iOS-style padding dan margins

### Key Features

| Feature | Description |
|---------|-------------|
| **Glass Navbar** | Header dengan backdrop blur dan semi-transparent background |
| **iOS Cards** | Cards dengan rounded corners (2xl/3xl), shadows, dan glass effect |
| **iOS Inputs** | Form inputs dengan focus scale animation dan ring effect |
| **iOS Buttons** | Buttons dengan press feedback dan shadow |
| **Staggered Animations** | Sequential entrance animations untuk lists dan forms |
| **Safe Area Support** | Padding untuk iOS devices dengan notch |

---

## 💼 Business Case

### Problem Statement

Sebelum refactoring:
- Desain menggunakan styling default yang kurang premium
- Tidak ada konsistensi visual antara halaman admin dan auth
- Animasi belum optimal untuk mobile experience
- Kurang feedback visual saat user berinteraksi

### Solution

Implementasi iOS Design System yang mencakup:
- CSS variables untuk iOS-like animations dan shadows
- Utility classes untuk glass effects dan press feedback
- Refactoring semua layouts dan pages dengan styling konsisten
- Haptic feedback composable untuk tactile response

### Impact

- ✅ **Premium Look & Feel** - Aplikasi terlihat lebih modern dan profesional
- ✅ **Improved UX** - Feedback visual yang lebih responsive
- ✅ **Consistency** - Desain seragam di seluruh aplikasi
- ✅ **Mobile-First** - Optimized untuk touch interactions

---

## 🔄 User Flow

### Page Load Animation

```
┌─────────────────────────────────────────────────────────────┐
│                     Page Load                                │
├─────────────────────────────────────────────────────────────┤
│  1. Background gradient renders                              │
│  2. Header slides in with glass effect                       │
│  3. Page title animates (opacity: 0 → 1, y: 20 → 0)         │
│  4. Content sections stagger in (50ms delay each)           │
│  5. Footer/Actions appear last                              │
└─────────────────────────────────────────────────────────────┘
```

### Form Interaction

```
┌─────────────────────────────────────────────────────────────┐
│                   Form Field Focus                           │
├─────────────────────────────────────────────────────────────┤
│  1. User taps input field                                   │
│  2. Haptic selection feedback triggers                      │
│  3. Input scales up slightly (1.02x)                        │
│  4. Focus ring appears with primary color                   │
│  5. On blur, input scales back to normal                    │
└─────────────────────────────────────────────────────────────┘
```

### Button Press

```
┌─────────────────────────────────────────────────────────────┐
│                   Button Press                               │
├─────────────────────────────────────────────────────────────┤
│  1. User presses button                                     │
│  2. Haptic medium feedback triggers                         │
│  3. Button scales down to 0.95                              │
│  4. On release, button scales back with spring              │
│  5. Action executes                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Technical Implementation

### CSS Variables (app.css)

```css
/* iOS-like animation timing */
--ios-spring-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
--ios-spring-smooth: cubic-bezier(0.4, 0, 0.2, 1);
--ios-spring-snappy: cubic-bezier(0.2, 0.8, 0.2, 1);

/* iOS-like shadows */
--ios-shadow-sm: 0 1px 2px oklch(0 0 0 / 0.04);
--ios-shadow-md: 0 4px 12px oklch(0 0 0 / 0.08);
--ios-shadow-lg: 0 8px 24px oklch(0 0 0 / 0.12);

/* iOS blur values */
--ios-blur-md: 12px;

/* iOS interaction scales */
--ios-press-scale: 0.97;
```

### Utility Classes

```css
/* Press feedback */
.ios-press {
    transition: transform 0.15s var(--ios-spring-snappy);
}
.ios-press:active {
    transform: scale(var(--ios-press-scale));
}

/* Glass effect */
.ios-glass {
    background: oklch(var(--card) / 0.8);
    backdrop-filter: blur(var(--ios-blur-md));
    border: 1px solid oklch(var(--border) / 0.5);
}

/* iOS Card */
.ios-card {
    background: oklch(var(--card) / 0.95);
    border-radius: 1rem;
    box-shadow: var(--ios-shadow-md);
}
```

### Component Patterns

#### iOS-style Input

```vue
<Input
    class="ios-input h-12 rounded-xl border-0 bg-muted/50 text-base 
           focus:bg-background focus:ring-2 focus:ring-primary/20"
    @focus="handleFieldFocus('email'); haptic.selection()"
    @blur="handleFieldBlur"
/>
```

#### iOS-style Button

```vue
<Button
    class="ios-button h-13 w-full rounded-2xl text-base font-semibold 
           shadow-lg transition-all duration-150"
    :class="{ 'scale-95': isSubmitPressed }"
    @mousedown="isSubmitPressed = true; haptic.medium()"
    @mouseup="isSubmitPressed = false"
    @touchstart.passive="isSubmitPressed = true; haptic.medium()"
    @touchend="isSubmitPressed = false"
>
    Submit
</Button>
```

---

## 📁 Files Updated

### Layouts

| File | Changes |
|------|---------|
| `layouts/app/AppSidebarLayout.vue` | Glass sidebar, iOS page transitions, safe area support |
| `layouts/app/AppCustomerLayout.vue` | Frosted glass navbar, spring animations, gradient background |
| `layouts/app/AppHeaderLayout.vue` | Glass header, smooth content animation |
| `layouts/auth/AuthCardLayout.vue` | Decorative orbs, bouncy logo, glass card |
| `layouts/auth/AuthSplitLayout.vue` | Gradient brand section, glass effects |
| `layouts/settings/Layout.vue` | iOS grouped navigation, active indicators |

### Auth Pages

| File | Changes |
|------|---------|
| `pages/auth/Login.vue` | Fixed @error handler |
| `pages/auth/Register.vue` | Fixed @error handler |
| `pages/auth/ForgotPassword.vue` | iOS inputs, haptic feedback, Indonesian translations |
| `pages/auth/ResetPassword.vue` | iOS inputs with icons, shake animation |
| `pages/auth/ConfirmPassword.vue` | Security icon, iOS password input |
| `pages/auth/VerifyEmail.vue` | Animated mail icon, iOS buttons |
| `pages/auth/TwoFactorChallenge.vue` | iOS PIN input, recovery mode toggle |

### Admin Pages

| File | Changes |
|------|---------|
| `pages/Admin/Dashboard.vue` | iOS cards, stats animations |
| `pages/Admin/Orders/Index.vue` | iOS table, mobile cards, pagination |
| `pages/Admin/Orders/Show.vue` | Premium detail layout, timeline |
| `pages/Admin/Products/Index.vue` | iOS table with thumbnails |
| `pages/Admin/Products/Create.vue` | iOS form sections |
| `pages/Admin/Products/Edit.vue` | iOS form sections |
| `pages/Admin/Categories/Index.vue` | iOS table, CRUD modals |
| `pages/Admin/Categories/Create.vue` | iOS form sections |
| `pages/Admin/Categories/Edit.vue` | iOS form sections |
| `pages/Admin/Settings/Index.vue` | iOS form cards |

### Store Components

| File | Changes |
|------|---------|
| `components/store/ProductCard.vue` | Press feedback, animations |
| `components/store/CartItem.vue` | Staggered animations |
| `components/store/CategoryFilter.vue` | iOS button styling |

### CSS

| File | Changes |
|------|---------|
| `resources/css/app.css` | iOS variables, utility classes, admin styles |

---

## 📋 Changelog

### Version 2.0.0 (December 2024) - Sprint 8

#### Added
- ✅ iOS CSS variables untuk animations, shadows, dan blur
- ✅ Utility classes: `.ios-press`, `.ios-glass`, `.ios-card`, `.ios-button`
- ✅ Admin-specific styles: `.admin-badge`, `.admin-input`, `.admin-form-section`
- ✅ Safe area support untuk iOS devices
- ✅ Decorative gradient orbs untuk auth layouts
- ✅ Indonesian translations untuk auth pages

#### Changed
- ✅ All layouts refactored dengan iOS glass effects
- ✅ All auth pages dengan iOS-style inputs dan buttons
- ✅ All admin pages dengan premium card styling
- ✅ Form error handling menggunakan `@error` event
- ✅ Button press feedback menggunakan state management

#### Fixed
- ✅ TypeScript errors untuk `hasErrors` property
- ✅ Tailwind CSS v4 class syntax (`!text-3xl` → `text-3xl!`)
- ✅ `OrderStatus` type compatibility
- ✅ Flash message typing in `AppPageProps`

#### Files Modified
- 6 layout files
- 7 auth page files
- 10 admin page files
- 3 store component files
- 1 CSS file (app.css)

---

## 🎨 Design Guidelines

### Color Usage

| Context | Class | Example |
|---------|-------|---------|
| Glass Background | `bg-card/80 backdrop-blur-xl` | Navbars, modals |
| Card Background | `bg-card/95` | Content cards |
| Input Background | `bg-muted/50` | Form inputs |
| Gradient Background | `from-background via-background to-muted/20` | Page backgrounds |

### Border Radius

| Element | Class | Value |
|---------|-------|-------|
| Buttons | `rounded-2xl` | 16px |
| Cards | `rounded-2xl` / `rounded-3xl` | 16px / 24px |
| Inputs | `rounded-xl` | 12px |
| Avatars | `rounded-full` | 9999px |

### Shadows

| Level | Variable | Usage |
|-------|----------|-------|
| Small | `--ios-shadow-sm` | Subtle elevation |
| Medium | `--ios-shadow-md` | Cards, buttons |
| Large | `--ios-shadow-lg` | Modals, dropdowns |

---

## 📚 Resources

- [Apple Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/)
- [motion-v Documentation](https://motion.vueuse.org/)
- [Tailwind CSS v4](https://tailwindcss.com/)

---

*Document version: 2.0*  
*Author: Zulfikar Hidayatullah*  
*Last updated: December 2024*

