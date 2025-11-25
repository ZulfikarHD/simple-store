# Project Charter - F&B Web App

## Ringkasan Proyek

**Nama Proyek:** F&B Product Sales Web App  
**Versi:** 1.0  
**Tanggal Mulai:** November 2024  
**Timeline:** 6-8 Minggu  

---

## Visi & Misi

### Visi
Menyediakan platform penjualan makanan dan minuman yang modern, cepat, dan user-friendly untuk memudahkan pelanggan memesan produk F&B.

### Misi
- Memberikan pengalaman belanja online yang seamless
- Mengintegrasikan pemesanan dengan WhatsApp untuk kemudahan komunikasi
- Menyediakan dashboard admin yang komprehensif untuk pengelolaan produk dan pesanan

---

## Scope Proyek

### In Scope
1. **Customer Portal**
   - Katalog produk dengan pencarian dan filter kategori
   - Shopping cart dengan manajemen item
   - Checkout dengan integrasi WhatsApp
   - Konfirmasi pesanan

2. **Admin Panel**
   - Dashboard overview dengan statistik
   - Manajemen produk (CRUD)
   - Manajemen kategori
   - Manajemen pesanan
   - Konfigurasi toko

3. **Sistem**
   - Autentikasi user (Admin)
   - Responsive design (mobile-first)
   - Database SQLite/MySQL

### Out of Scope
- Payment gateway integration (Midtrans, Xendit, dll)
- Loyalty program & points system
- Multi-vendor marketplace
- Real-time tracking delivery
- Mobile native application

---

## Stakeholders

| Role | Nama | Tanggung Jawab |
|------|------|----------------|
| Project Owner | TBD | Keputusan bisnis, approval final |
| Developer | TBD | Development & testing |
| Designer | TBD | UI/UX design |
| End User | Customer | Testing, feedback |

---

## Success Criteria

### MVP Success Metrics
- [ ] Customer dapat browse dan search produk
- [ ] Customer dapat checkout via WhatsApp
- [ ] Admin dapat mengelola produk dan kategori
- [ ] Admin dapat melihat dan update status pesanan
- [ ] Aplikasi responsive di semua device
- [ ] Zero critical bugs pada production

### Performance Targets
- Page load time: < 3 detik
- Time to interactive: < 5 detik
- 100% critical user flows berfungsi

---

## Risiko & Mitigasi

| Risiko | Impact | Probability | Mitigasi |
|--------|--------|-------------|----------|
| Perubahan requirement | High | Medium | Komunikasi rutin, backlog flexible |
| WhatsApp API changes | Medium | Low | Dokumentasi usage, backup plan |
| Performance issues | Medium | Low | Pagination, query optimization |
| Scope creep | High | High | Sprint planning ketat, DoD jelas |

---

## Timeline High-Level

| Phase | Sprint | Durasi | Deliverable |
|-------|--------|--------|-------------|
| Foundation | Sprint 1 | Week 1-2 | Setup & Product Catalog |
| Shopping | Sprint 2 | Week 2-3 | Cart & Checkout |
| Admin Basic | Sprint 3 | Week 3-4 | Product Management |
| Admin Advanced | Sprint 4 | Week 4-5 | Order Management |
| Configuration | Sprint 5 | Week 5-6 | Settings |
| Polish | Sprint 6 | Week 6-7 | Testing & Deployment |

---

## Approval

| Role | Nama | Tanggal | Tanda Tangan |
|------|------|---------|--------------|
| Project Owner | | | |
| Developer Lead | | | |

