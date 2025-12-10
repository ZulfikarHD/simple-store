# F&B Web App - Dokumentasi Proyek

Selamat datang di dokumentasi aplikasi F&B Web App. Aplikasi ini merupakan platform penjualan produk makanan dan minuman berbasis web dengan integrasi WhatsApp untuk pemesanan.

## Quick Links

### Untuk Developer
- [Project Charter](01-project-overview/project-charter.md)
- [Setup Guide](04-technical/setup-guide.md)
- [Coding Standards](04-technical/coding-standards.md)
- [Git Workflow](04-technical/git-workflow.md)
- [API Documentation](05-api-documentation/api-overview.md)

### Untuk Designer
- [Design System](03-design/ui-ux/design-system.md) ⭐ **Updated dengan Vue Components**
- [Animation System](03-design/ui-ux/animation-system.md) **iOS Spring Physics**
- [iOS Design Refactor](03-design/ui-ux/ios-design-refactor.md) 🆕 **Sprint 8 - Glass Effects & Premium Styling**
- [Wireframes](03-design/ui-ux/wireframes/)
- [Mockups](03-design/ui-ux/mockups/)
- [User Flows](03-design/ui-ux/user-flows.md)

### Untuk User
- [Quick Start Guide](06-user-guides/quick-start.md)
- [User Manual](06-user-guides/user-manual.md)
- [FAQ](06-user-guides/faq.md)
- [Troubleshooting](06-user-guides/troubleshooting.md)

### Untuk Admin
- [Admin Guide](06-user-guides/admin-guide.md)
- [Deployment Guide](04-technical/deployment-guide.md)
- [Monitoring](09-operations/monitoring.md)

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.4) |
| Frontend | Vue.js 3 + Inertia.js v2 |
| Styling | Tailwind CSS v4 |
| UI Components | shadcn-vue + Custom Store Components |
| Database | SQLite (Development) |
| Payment | WhatsApp API Integration |

## Store Components

Komponen Vue siap pakai untuk F&B store:

```
resources/js/components/store/
├── ProductCard.vue      # Card produk
├── CartItem.vue         # Item keranjang
├── CategoryFilter.vue   # Filter kategori
├── SearchBar.vue        # Search dengan debounce
├── OrderStatusBadge.vue # Badge status pesanan
├── EmptyState.vue       # Empty state
├── PriceDisplay.vue     # Format harga Rupiah
└── index.ts             # Export semua components
```

**Quick Import:**

```vue
import { ProductCard, CartItem, SearchBar } from '@/components/store'
```

---

## Project Status

- **Current Version**: 0.7.0 (Development)
- **Last Updated**: 2025-12-10
- **Project Status**: In Development - Sprint 8 Completed
- **Latest Feature**: ✅ Sprint 8 - iOS Design System Refactoring (IOS-001)

### Sprint 1 Progress

| Story | Description | Status |
|-------|-------------|--------|
| CUST-001 | Product Catalog | ✅ Completed |
| CUST-002 | Category Filter | ✅ Completed |
| CUST-003 | Product Search | ✅ Completed |
| CUST-004 | Product Detail | ✅ Completed |

### Sprint 2 Progress

| Story | Description | Status |
|-------|-------------|--------|
| CUST-005 | Add Products to Cart | ✅ Completed |
| CUST-006 | View and Manage Cart | ✅ Completed |
| CUST-007 | Checkout Form | ✅ Completed |
| CUST-008 | WhatsApp Integration | ✅ Completed |
| CUST-009 | Order Confirmation | ✅ Completed |

### Sprint 3 Progress

| Story | Description | Status |
|-------|-------------|--------|
| ADMIN-001 | Admin Dashboard Overview | ✅ Completed |
| ADMIN-002 | View All Products | ✅ Completed |
| ADMIN-003 | Add New Products | ✅ Completed |
| ADMIN-004 | Edit Products | ✅ Completed |
| ADMIN-005 | Delete Products | ✅ Completed |
| ADMIN-006 | Manage Categories | ✅ Completed |

### Sprint 4 Progress

| Story | Description | Status |
|-------|-------------|--------|
| ADMIN-007 | View All Orders | ✅ Completed |
| ADMIN-008 | View Order Details | ✅ Completed |
| ADMIN-009 | Update Order Status | ✅ Completed |
| ADMIN-010 | Configure Store Settings | ✅ Completed |

### Sprint 6 Progress (Enhancement & Polish)

| Story | Description | Status |
|-------|-------------|--------|
| ENH-001 | Responsive Mobile-Friendly Design | ✅ Completed |
| ENH-002 | Fast Page Loading | ✅ Completed |
| ENH-003 | Product Availability Badges | ✅ Completed |
| ENH-004 | Order Notifications | ✅ Completed |

### Sprint 7 Progress (Animation System)

| Story | Description | Status |
|-------|-------------|--------|
| ANI-001 | Motion-V Animation System Migration | ✅ Completed |
| ANI-002 | iOS Spring Physics Implementation | ✅ Completed |
| ANI-003 | Staggered Animations for Lists | ✅ Completed |
| ANI-004 | Press Feedback Implementation | ✅ Completed |

### Sprint 8 Progress (iOS Design System)

| Story | Description | Status |
|-------|-------------|--------|
| IOS-001 | iOS Glass Effects & Premium Styling | ✅ Completed |
| IOS-002 | Layout Refactoring (Sidebar, Customer, Auth) | ✅ Completed |
| IOS-003 | Auth Pages iOS Redesign | ✅ Completed |
| IOS-004 | Admin Pages iOS Redesign | ✅ Completed |
| IOS-005 | Store Components Enhancement | ✅ Completed |

---

## Struktur Dokumentasi

```
docs/
├── README.md                           # Index dokumentasi utama
├── 01-project-overview/                # Gambaran umum proyek
├── 02-requirements/                    # Requirement specification
├── 03-design/                          # Design & UI/UX
├── 04-technical/                       # Technical documentation
├── 05-api-documentation/               # API reference
├── 06-user-guides/                     # Panduan pengguna
├── 07-development/                     # Development planning
├── 08-testing/                         # Testing documentation
├── 09-operations/                      # Operations & maintenance
├── 10-meetings/                        # Meeting notes
└── 11-assets/                          # Assets & media files
```

---

## Kontributor

| Nama | Role | Kontak |
|------|------|--------|
| Zulfikar Hidayatullah | Developer | 0857-1583-8733 |
| Zulfikar Hidayatullah | Designer | 0857-1583-8733 |
| Zulfikar Hidayatullah | Product Owner | 0857-1583-8733 |

---

## License

Hak Cipta © 2024. All Rights Reserved.

