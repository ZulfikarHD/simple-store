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

- **Current Version**: 0.1.0 (Development)
- **Last Updated**: 2024-11-25
- **Project Status**: In Development - Sprint 1 Complete
- **Latest Feature**: ✅ CUST-003 Product Search & CUST-004 Product Detail (Completed)

### Sprint 1 Progress

| Story | Description | Status |
|-------|-------------|--------|
| CUST-001 | Product Catalog | ✅ Completed |
| CUST-002 | Category Filter | ✅ Completed |
| CUST-003 | Product Search | ✅ Completed |
| CUST-004 | Product Detail | ✅ Completed |

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
| Zulfikar Hidayatullah | Developer | - |
| - | Designer | - |
| - | Product Owner | - |

---

## License

Hak Cipta © 2024. All Rights Reserved.

