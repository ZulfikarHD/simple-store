# Design System - F&B Web App

Design system ini terinspirasi dari Airbnb dengan fokus pada kesederhanaan, kejelasan, dan pengalaman pengguna yang menyenangkan. Warna biru digunakan sebagai primary color untuk memberikan kesan profesional, terpercaya, dan modern.

---

## 🎨 Design Philosophy

### Core Principles

1. **Clarity Over Complexity**
   - Setiap elemen harus memiliki tujuan yang jelas
   - Hindari elemen dekoratif yang tidak fungsional
   - Gunakan whitespace secara strategis

2. **Consistency**
   - Gunakan komponen yang konsisten di seluruh aplikasi
   - Pattern yang sama untuk interaksi serupa
   - Typography yang seragam

3. **Accessibility First**
   - Kontras warna yang memenuhi WCAG AA
   - Touch target minimal 44x44px
   - Support keyboard navigation

4. **Mobile-First**
   - Design dimulai dari mobile, kemudian scale up
   - Prioritaskan konten penting di viewport kecil
   - Touch-friendly interactions

---

## 🔵 Color Palette

### Primary Colors (Blue Spectrum)

```css
/* CSS Variables untuk Tailwind v4 */
@theme {
  /* Primary Blue - Main Brand Color */
  --color-primary-50: oklch(0.97 0.01 240);   /* #f0f7ff */
  --color-primary-100: oklch(0.93 0.03 240);  /* #e0efff */
  --color-primary-200: oklch(0.86 0.06 240);  /* #b3d4ff */
  --color-primary-300: oklch(0.76 0.10 240);  /* #80b8ff */
  --color-primary-400: oklch(0.65 0.15 240);  /* #4d9aff */
  --color-primary-500: oklch(0.55 0.20 240);  /* #1a7cff - Main */
  --color-primary-600: oklch(0.48 0.20 240);  /* #0066e6 */
  --color-primary-700: oklch(0.40 0.18 240);  /* #0052bf */
  --color-primary-800: oklch(0.32 0.15 240);  /* #003d99 */
  --color-primary-900: oklch(0.25 0.12 240);  /* #002966 */
}
```

| Name | Value | Hex Approx | Usage |
|------|-------|------------|-------|
| Primary 50 | `oklch(0.97 0.01 240)` | #F0F7FF | Backgrounds, hover states |
| Primary 100 | `oklch(0.93 0.03 240)` | #E0EFFF | Light backgrounds |
| Primary 200 | `oklch(0.86 0.06 240)` | #B3D4FF | Borders, dividers |
| Primary 300 | `oklch(0.76 0.10 240)` | #80B8FF | Disabled states |
| Primary 400 | `oklch(0.65 0.15 240)` | #4D9AFF | Hover states |
| **Primary 500** | `oklch(0.55 0.20 240)` | **#1A7CFF** | **Primary buttons, links** |
| Primary 600 | `oklch(0.48 0.20 240)` | #0066E6 | Active states |
| Primary 700 | `oklch(0.40 0.18 240)` | #0052BF | Dark accents |
| Primary 800 | `oklch(0.32 0.15 240)` | #003D99 | Very dark accents |
| Primary 900 | `oklch(0.25 0.12 240)` | #002966 | Text on light backgrounds |

### Neutral Colors

```css
@theme {
  /* Neutrals - Clean & Minimal */
  --color-neutral-50: oklch(0.98 0 0);   /* #FAFAFA */
  --color-neutral-100: oklch(0.96 0 0);  /* #F5F5F5 */
  --color-neutral-200: oklch(0.92 0 0);  /* #EEEEEE */
  --color-neutral-300: oklch(0.85 0 0);  /* #DDDDDD */
  --color-neutral-400: oklch(0.70 0 0);  /* #AAAAAA */
  --color-neutral-500: oklch(0.55 0 0);  /* #777777 */
  --color-neutral-600: oklch(0.43 0 0);  /* #555555 */
  --color-neutral-700: oklch(0.32 0 0);  /* #333333 */
  --color-neutral-800: oklch(0.22 0 0);  /* #222222 */
  --color-neutral-900: oklch(0.13 0 0);  /* #111111 */
}
```

### Semantic Colors

```css
@theme {
  /* Success - Green */
  --color-success-50: oklch(0.96 0.03 145);
  --color-success-500: oklch(0.60 0.18 145);  /* #22C55E */
  --color-success-700: oklch(0.45 0.15 145);

  /* Warning - Amber */
  --color-warning-50: oklch(0.97 0.03 85);
  --color-warning-500: oklch(0.75 0.15 85);   /* #F59E0B */
  --color-warning-700: oklch(0.55 0.13 85);

  /* Error - Red */
  --color-error-50: oklch(0.97 0.02 25);
  --color-error-500: oklch(0.60 0.22 25);     /* #EF4444 */
  --color-error-700: oklch(0.45 0.18 25);

  /* Info - Cyan */
  --color-info-50: oklch(0.96 0.02 200);
  --color-info-500: oklch(0.65 0.15 200);     /* #06B6D4 */
  --color-info-700: oklch(0.50 0.13 200);
}
```

### Color Usage Guidelines

| Context | Color | Example |
|---------|-------|---------|
| Primary Actions | Primary 500 | "Tambah ke Keranjang", "Pesan Sekarang" |
| Secondary Actions | Neutral 700 | "Batal", "Kembali" |
| Success States | Success 500 | "Pesanan Berhasil", badges |
| Warning States | Warning 500 | "Stok Terbatas" |
| Error States | Error 500 | Validation errors, "Gagal" |
| Links | Primary 600 | Text links, breadcrumbs |
| Body Text | Neutral 700 | Paragraphs, descriptions |
| Headings | Neutral 900 | H1, H2, H3 |
| Placeholder | Neutral 400 | Input placeholders |
| Borders | Neutral 200 | Cards, inputs, dividers |
| Backgrounds | Neutral 50/100 | Page backgrounds, cards |

---

## ✏️ Typography

### Font Family

```css
@theme {
  /* Primary Font - Clean Sans-Serif */
  --font-family-sans: 'Plus Jakarta Sans', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  
  /* Alternative Options */
  --font-family-display: 'DM Sans', sans-serif;
  --font-family-mono: 'JetBrains Mono', 'SF Mono', monospace;
}
```

**Font Recommendations:**
1. **Plus Jakarta Sans** - Primary (Modern, clean, excellent readability)
2. **DM Sans** - Alternative (Geometric, friendly)
3. **Inter** - Fallback (System-friendly, widely available)

### Type Scale

| Name | Size | Weight | Line Height | Usage |
|------|------|--------|-------------|-------|
| Display | 48px / 3rem | 700 | 1.1 | Hero headlines |
| H1 | 36px / 2.25rem | 700 | 1.2 | Page titles |
| H2 | 28px / 1.75rem | 600 | 1.25 | Section headers |
| H3 | 22px / 1.375rem | 600 | 1.3 | Card titles |
| H4 | 18px / 1.125rem | 600 | 1.4 | Subheadings |
| Body Large | 18px / 1.125rem | 400 | 1.6 | Featured text |
| Body | 16px / 1rem | 400 | 1.6 | Default body text |
| Body Small | 14px / 0.875rem | 400 | 1.5 | Secondary text |
| Caption | 12px / 0.75rem | 400 | 1.5 | Labels, hints |
| Overline | 11px / 0.6875rem | 600 | 1.4 | Category labels (uppercase) |

### Typography CSS Classes

```css
/* Tailwind Custom Classes */
.text-display { @apply text-5xl font-bold leading-tight tracking-tight; }
.text-h1 { @apply text-4xl font-bold leading-tight; }
.text-h2 { @apply text-2xl font-semibold leading-snug; }
.text-h3 { @apply text-xl font-semibold leading-normal; }
.text-h4 { @apply text-lg font-semibold; }
.text-body-lg { @apply text-lg leading-relaxed; }
.text-body { @apply text-base leading-relaxed; }
.text-body-sm { @apply text-sm leading-normal; }
.text-caption { @apply text-xs leading-normal; }
.text-overline { @apply text-[11px] font-semibold uppercase tracking-wider; }
```

---

## 📐 Spacing System

### Base Unit: 4px

| Token | Value | Tailwind | Usage |
|-------|-------|----------|-------|
| space-0 | 0px | `p-0` | Reset |
| space-1 | 4px | `p-1` | Tight spacing |
| space-2 | 8px | `p-2` | Icon gaps |
| space-3 | 12px | `p-3` | Button padding |
| space-4 | 16px | `p-4` | Card padding (mobile) |
| space-5 | 20px | `p-5` | Section spacing |
| space-6 | 24px | `p-6` | Card padding (desktop) |
| space-8 | 32px | `p-8` | Large gaps |
| space-10 | 40px | `p-10` | Section gaps |
| space-12 | 48px | `p-12` | Page sections |
| space-16 | 64px | `p-16` | Hero sections |
| space-20 | 80px | `p-20` | Large sections |
| space-24 | 96px | `p-24` | Extra large sections |

### Layout Spacing Guidelines

```
┌─────────────────────────────────────────────────────────────┐
│                    Header (h: 64px)                         │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────┐    │
│  │                                                     │    │
│  │   Page Content                                      │    │
│  │   Padding: 16px (mobile) / 24px (desktop)          │    │
│  │                                                     │    │
│  │   ┌─────────┐ gap: 16px ┌─────────┐               │    │
│  │   │  Card   │           │  Card   │               │    │
│  │   │  p: 16  │           │  p: 16  │               │    │
│  │   └─────────┘           └─────────┘               │    │
│  │                                                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔲 Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `rounded-none` | 0px | Sharp edges |
| `rounded-sm` | 4px | Input fields, small buttons |
| `rounded` | 8px | Default cards, buttons |
| `rounded-md` | 12px | Cards, modals |
| `rounded-lg` | 16px | Large cards, images |
| `rounded-xl` | 20px | Hero sections |
| `rounded-2xl` | 24px | Bottom sheets |
| `rounded-full` | 9999px | Avatars, pills, circular buttons |

### Border Radius Guidelines

```css
/* Rounded Corners Pattern */
.card-small { @apply rounded-lg; }      /* 16px */
.card-default { @apply rounded-xl; }    /* 20px */
.button { @apply rounded-lg; }          /* 16px */
.input { @apply rounded-lg; }           /* 16px */
.avatar { @apply rounded-full; }        /* Circle */
.badge { @apply rounded-full; }         /* Pill */
.image { @apply rounded-xl; }           /* 20px */
.modal { @apply rounded-2xl; }          /* 24px */
```

---

## 🌑 Shadows

Gunakan shadows secara minimal untuk menciptakan depth tanpa berlebihan.

```css
@theme {
  /* Airbnb-inspired subtle shadows */
  --shadow-xs: 0 1px 2px oklch(0 0 0 / 0.04);
  --shadow-sm: 0 1px 3px oklch(0 0 0 / 0.08), 0 1px 2px oklch(0 0 0 / 0.04);
  --shadow-md: 0 4px 6px oklch(0 0 0 / 0.07), 0 2px 4px oklch(0 0 0 / 0.05);
  --shadow-lg: 0 10px 15px oklch(0 0 0 / 0.08), 0 4px 6px oklch(0 0 0 / 0.04);
  --shadow-xl: 0 20px 25px oklch(0 0 0 / 0.10), 0 8px 10px oklch(0 0 0 / 0.05);
  
  /* Card hover effect */
  --shadow-card: 0 2px 8px oklch(0 0 0 / 0.08);
  --shadow-card-hover: 0 8px 24px oklch(0 0 0 / 0.12);
}
```

| Shadow | Usage |
|--------|-------|
| `shadow-xs` | Subtle elevation, pressed states |
| `shadow-sm` | Cards at rest, inputs |
| `shadow-md` | Dropdowns, popovers |
| `shadow-lg` | Modals, dialogs |
| `shadow-xl` | Full-screen overlays |
| `shadow-card` | Product cards default |
| `shadow-card-hover` | Product cards on hover |

---

## 🧩 Components

### Buttons

#### Primary Button
```html
<button class="
  px-6 py-3 
  bg-primary-500 hover:bg-primary-600 active:bg-primary-700
  text-white font-semibold
  rounded-lg
  shadow-sm hover:shadow-md
  transition-all duration-200
  focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:ring-offset-2
">
  Tambah ke Keranjang
</button>
```

#### Button Variants

| Variant | Background | Text | Border | Usage |
|---------|------------|------|--------|-------|
| Primary | `bg-primary-500` | White | None | Main CTA |
| Secondary | `bg-neutral-100` | `neutral-700` | None | Secondary actions |
| Outline | Transparent | `primary-600` | `primary-300` | Tertiary actions |
| Ghost | Transparent | `neutral-600` | None | Text-style buttons |
| Danger | `bg-error-500` | White | None | Destructive actions |

#### Button Sizes

| Size | Padding | Height | Font Size |
|------|---------|--------|-----------|
| Small | `px-3 py-1.5` | 32px | 14px |
| Medium | `px-5 py-2.5` | 40px | 14px |
| Large | `px-6 py-3` | 48px | 16px |
| XLarge | `px-8 py-4` | 56px | 16px |

---

### Cards

#### Product Card
```html
<div class="
  group
  bg-white rounded-xl overflow-hidden
  shadow-card hover:shadow-card-hover
  transition-all duration-300
  cursor-pointer
">
  <!-- Image Container -->
  <div class="relative aspect-square overflow-hidden">
    <img 
      src="product.jpg" 
      alt="Product"
      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
    />
    <!-- Stock Badge -->
    <span class="absolute top-3 left-3 px-2 py-1 bg-success-500 text-white text-xs font-medium rounded-full">
      Tersedia
    </span>
  </div>
  
  <!-- Content -->
  <div class="p-4">
    <p class="text-overline text-neutral-500 mb-1">Minuman</p>
    <h3 class="text-h4 text-neutral-900 mb-2 line-clamp-2">Es Kopi Susu</h3>
    <p class="text-body-sm text-neutral-600 mb-3 line-clamp-2">
      Kopi pilihan dengan susu segar
    </p>
    <div class="flex items-center justify-between">
      <p class="text-lg font-bold text-primary-600">Rp 25.000</p>
      <button class="p-2 rounded-full bg-primary-50 hover:bg-primary-100 text-primary-600 transition-colors">
        <svg class="w-5 h-5"><!-- Plus icon --></svg>
      </button>
    </div>
  </div>
</div>
```

---

### Input Fields

```html
<div class="space-y-2">
  <label class="text-body-sm font-medium text-neutral-700">
    Nama Lengkap
  </label>
  <input 
    type="text"
    placeholder="Masukkan nama lengkap"
    class="
      w-full px-4 py-3
      bg-white border border-neutral-200
      rounded-lg
      text-neutral-900 placeholder:text-neutral-400
      focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500
      transition-all duration-200
    "
  />
  <p class="text-caption text-neutral-500">Nama akan digunakan untuk pesanan</p>
</div>
```

#### Input States

| State | Border | Ring | Background |
|-------|--------|------|------------|
| Default | `border-neutral-200` | None | White |
| Hover | `border-neutral-300` | None | White |
| Focus | `border-primary-500` | `ring-primary-500/30` | White |
| Error | `border-error-500` | `ring-error-500/30` | `error-50` |
| Disabled | `border-neutral-200` | None | `neutral-50` |

---

### Navigation

#### Header
```html
<header class="
  sticky top-0 z-50
  bg-white/90 backdrop-blur-lg
  border-b border-neutral-100
  h-16
">
  <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
    <!-- Logo -->
    <a href="/" class="flex items-center gap-2">
      <div class="w-8 h-8 bg-primary-500 rounded-lg"></div>
      <span class="text-xl font-bold text-neutral-900">F&B Store</span>
    </a>
    
    <!-- Search Bar (Desktop) -->
    <div class="hidden md:flex flex-1 max-w-md mx-8">
      <div class="relative w-full">
        <input 
          type="text"
          placeholder="Cari produk..."
          class="w-full pl-10 pr-4 py-2 bg-neutral-100 rounded-full text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/30 transition-all"
        />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400">
          <!-- Search icon -->
        </svg>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="flex items-center gap-4">
      <button class="relative p-2 hover:bg-neutral-100 rounded-full transition-colors">
        <svg class="w-6 h-6 text-neutral-600"><!-- Cart icon --></svg>
        <span class="absolute -top-1 -right-1 w-5 h-5 bg-primary-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
          3
        </span>
      </button>
    </div>
  </div>
</header>
```

---

### Badges & Tags

```html
<!-- Status Badges -->
<span class="px-2.5 py-1 bg-success-50 text-success-700 text-xs font-medium rounded-full">
  Tersedia
</span>

<span class="px-2.5 py-1 bg-warning-50 text-warning-700 text-xs font-medium rounded-full">
  Stok Terbatas
</span>

<span class="px-2.5 py-1 bg-error-50 text-error-700 text-xs font-medium rounded-full">
  Habis
</span>

<!-- Category Tags -->
<span class="px-3 py-1.5 bg-primary-50 text-primary-700 text-sm font-medium rounded-lg hover:bg-primary-100 cursor-pointer transition-colors">
  Minuman
</span>
```

---

## 📱 Responsive Breakpoints

| Breakpoint | Width | Target |
|------------|-------|--------|
| `sm` | 640px | Large phones (landscape) |
| `md` | 768px | Tablets |
| `lg` | 1024px | Small laptops |
| `xl` | 1280px | Desktops |
| `2xl` | 1536px | Large screens |

### Mobile-First Design Pattern

```css
/* Base: Mobile (320px - 639px) */
.container { @apply px-4; }
.grid-products { @apply grid grid-cols-2 gap-3; }

/* sm: Large Mobile */
@screen sm {
  .container { @apply px-6; }
  .grid-products { @apply gap-4; }
}

/* md: Tablet */
@screen md {
  .grid-products { @apply grid-cols-3; }
}

/* lg: Laptop */
@screen lg {
  .container { @apply px-8; }
  .grid-products { @apply grid-cols-4 gap-6; }
}

/* xl: Desktop */
@screen xl {
  .container { @apply max-w-7xl mx-auto; }
}
```

---

## ✨ Animations & Transitions

### Duration Guidelines

| Duration | Value | Usage |
|----------|-------|-------|
| Fast | 150ms | Hover effects, color changes |
| Normal | 200ms | Most interactions |
| Smooth | 300ms | Card transitions, modals |
| Slow | 500ms | Page transitions, hero |

### Easing Functions

```css
@theme {
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Common Animations

```css
/* Fade In */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Slide Up */
@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

/* Scale In */
@keyframes scaleIn {
  from { 
    opacity: 0;
    transform: scale(0.95);
  }
  to { 
    opacity: 1;
    transform: scale(1);
  }
}

/* Skeleton Loading */
@keyframes shimmer {
  from { background-position: -200% 0; }
  to { background-position: 200% 0; }
}

.skeleton {
  @apply bg-gradient-to-r from-neutral-200 via-neutral-100 to-neutral-200;
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
```

### Micro-interactions

```html
<!-- Button Hover -->
<button class="
  transform hover:scale-[1.02] active:scale-[0.98]
  transition-transform duration-150
">
  Click Me
</button>

<!-- Card Hover -->
<div class="
  transform hover:-translate-y-1
  transition-all duration-300
">
  Card Content
</div>

<!-- Icon Spin on Load -->
<svg class="animate-spin h-5 w-5 text-primary-500">
  <!-- Spinner icon -->
</svg>
```

---

## 🖼️ Iconography

### Recommended Icon Libraries
1. **Heroicons** (Primary) - Clean, consistent, made for Tailwind
2. **Lucide Icons** - Alternative with more options
3. **Phosphor Icons** - Flexible weight options

### Icon Sizes

| Size | Dimensions | Usage |
|------|------------|-------|
| XS | 16x16px | Inline, badges |
| SM | 20x20px | Buttons, inputs |
| MD | 24x24px | Navigation, default |
| LG | 32x32px | Feature icons |
| XL | 48x48px | Hero, empty states |

### Icon Usage

```html
<!-- Button with Icon -->
<button class="flex items-center gap-2 px-4 py-2 bg-primary-500 text-white rounded-lg">
  <svg class="w-5 h-5"><!-- Icon --></svg>
  <span>Tambah</span>
</button>

<!-- Icon Button -->
<button class="p-2 rounded-lg hover:bg-neutral-100 transition-colors">
  <svg class="w-6 h-6 text-neutral-600"><!-- Icon --></svg>
</button>
```

---

## 📸 Image Guidelines

### Aspect Ratios

| Ratio | Usage |
|-------|-------|
| 1:1 (Square) | Product thumbnails, avatars |
| 4:3 | Product detail images |
| 16:9 | Banner images, hero |
| 3:2 | Category cards |

### Image Treatment

```html
<!-- Product Image -->
<div class="aspect-square rounded-xl overflow-hidden bg-neutral-100">
  <img 
    src="product.jpg" 
    alt="Product Name"
    class="w-full h-full object-cover"
    loading="lazy"
  />
</div>

<!-- Image with Overlay -->
<div class="relative aspect-video rounded-xl overflow-hidden">
  <img src="hero.jpg" class="w-full h-full object-cover" />
  <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
  <div class="absolute bottom-0 left-0 p-6 text-white">
    <h2 class="text-2xl font-bold">Title</h2>
  </div>
</div>
```

---

## 🎯 Layout Patterns

### Page Container

```html
<div class="min-h-screen bg-neutral-50">
  <header><!-- Fixed header --></header>
  
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page content -->
  </main>
  
  <footer><!-- Footer --></footer>
</div>
```

### Product Grid

```html
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
  <!-- Product cards -->
</div>
```

### Two-Column Layout (Cart/Checkout)

```html
<div class="lg:grid lg:grid-cols-12 lg:gap-8">
  <!-- Main Content -->
  <div class="lg:col-span-8">
    <!-- Cart items -->
  </div>
  
  <!-- Sidebar -->
  <div class="lg:col-span-4">
    <div class="lg:sticky lg:top-24">
      <!-- Order summary -->
    </div>
  </div>
</div>
```

---

## 🌙 Dark Mode (Optional)

```css
@theme {
  /* Dark Mode Colors */
  --color-dark-bg: oklch(0.15 0.01 240);
  --color-dark-surface: oklch(0.20 0.01 240);
  --color-dark-border: oklch(0.28 0.01 240);
  --color-dark-text: oklch(0.95 0 0);
  --color-dark-text-secondary: oklch(0.70 0 0);
}
```

```html
<!-- Dark mode classes -->
<div class="bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text">
  Content
</div>
```

---

## ✅ Design Checklist

### Before Development
- [ ] All colors meet WCAG AA contrast requirements
- [ ] Touch targets are minimum 44x44px
- [ ] Typography hierarchy is clear
- [ ] Spacing is consistent (4px grid)
- [ ] Components are reusable

### During Development
- [ ] Mobile view looks good at 320px
- [ ] Tablet view uses space effectively
- [ ] Desktop view has proper max-width
- [ ] Animations are smooth (60fps)
- [ ] Loading states are implemented

### Final QA
- [ ] All interactive elements have hover/focus states
- [ ] Error states are clearly visible
- [ ] Empty states are designed
- [ ] Images have proper fallbacks
- [ ] Typography scales properly

---

## 📚 Resources

### Design Inspiration
- [Airbnb Design](https://airbnb.design/)
- [Stripe](https://stripe.com/)
- [Linear](https://linear.app/)
- [Notion](https://notion.so/)

### Tools
- [Tailwind CSS](https://tailwindcss.com/)
- [Heroicons](https://heroicons.com/)
- [Coolors](https://coolors.co/) - Color palette generator
- [Contrast Checker](https://webaim.org/resources/contrastchecker/)

### Fonts
- [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans)
- [DM Sans](https://fonts.google.com/specimen/DM+Sans)
- [Inter](https://fonts.google.com/specimen/Inter)

