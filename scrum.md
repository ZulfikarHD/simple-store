# F&B Web App - Scrum Development Plan

## Project Overview
**Project Name:** F&B Product Sales Web App  
**Tech Stack:** Laravel + Vue.js  
**Payment Method:** WhatsApp API (No Payment Gateway)  
**Timeline:** 6-8 Weeks (estimated)

---

## Product Backlog

### Epic 1: Project Setup & Infrastructure
**Priority:** Highest  
**Story Points:** 8

#### User Stories:
- **DEV-001:** As a developer, I need to set up Laravel project with Vue.js so development can begin
  - Acceptance Criteria:
    - Laravel 10/11 installed and configured
    - Vue.js 3 integrated (Inertia.js or standalone SPA)
    - Database connected and migrations ready
    - Git repository initialized
  - Story Points: 3

- **DEV-002:** As a developer, I need to design and create database schema
  - Acceptance Criteria:
    - Users, Products, Categories, Orders, Order_Items tables created
    - Relationships properly defined
    - Seeders created for testing
  - Story Points: 3

- **DEV-003:** As a developer, I need to set up authentication system
  - Acceptance Criteria:
    - Laravel Breeze/Sanctum installed
    - Login/Register for admin
    - Role-based middleware (admin/customer)
  - Story Points: 2

---

### Epic 2: Customer-Facing Features
**Priority:** High  
**Story Points:** 34

#### Sprint 1: Product Catalog & Browse (Week 1-2)

- **CUST-001:** As a customer, I want to view all products so I can browse what's available
  - Acceptance Criteria:
    - Home page displays all products with images
    - Products show name, price, description
    - Responsive grid layout
  - Story Points: 5

- **CUST-002:** As a customer, I want to filter products by category
  - Acceptance Criteria:
    - Category navigation menu
    - Filter products by selected category
    - Show "All Products" option
  - Story Points: 3

- **CUST-003:** As a customer, I want to search for products
  - Acceptance Criteria:
    - Search bar in header
    - Real-time or on-submit search
    - Display search results
  - Story Points: 3

- **CUST-004:** As a customer, I want to view product details
  - Acceptance Criteria:
    - Click product to see detail page
    - Show full description, price, images
    - "Add to Cart" button
  - Story Points: 2

#### Sprint 2: Shopping Cart & Checkout (Week 2-3)

- **CUST-005:** As a customer, I want to add products to cart
  - Acceptance Criteria:
    - Add to cart functionality
    - Cart counter in header
    - Success notification
  - Story Points: 5

- **CUST-006:** As a customer, I want to view and manage my cart
  - Acceptance Criteria:
    - Cart page showing all items
    - Adjust quantities (+/-)
    - Remove items
    - Show subtotal and total
  - Story Points: 5

- **CUST-007:** As a customer, I want to checkout my order
  - Acceptance Criteria:
    - Checkout form (name, phone, address)
    - Order notes field
    - Order summary display
    - Form validation
  - Story Points: 5

- **CUST-008:** As a customer, I want to complete order via WhatsApp
  - Acceptance Criteria:
    - "Send to WhatsApp" button
    - Generate formatted message with order details
    - Open WhatsApp with pre-filled message
    - Save order to database
  - Story Points: 5

- **CUST-009:** As a customer, I want to see my order confirmation
  - Acceptance Criteria:
    - Success page after order placed
    - Display order number
    - Show order details
    - Clear cart after order
  - Story Points: 1

---

### Epic 3: Admin Panel Features
**Priority:** High  
**Story Points:** 42

#### Sprint 3: Product Management (Week 3-4)

- **ADMIN-001:** As an admin, I want to view dashboard overview
  - Acceptance Criteria:
    - Display today's orders count
    - Show pending orders
    - Quick stats (total sales, products)
    - Recent orders list
  - Story Points: 5

- **ADMIN-002:** As an admin, I want to view all products
  - Acceptance Criteria:
    - Products table with pagination
    - Show name, category, price, status
    - Search and filter options
  - Story Points: 3

- **ADMIN-003:** As an admin, I want to add new products
  - Acceptance Criteria:
    - Product creation form
    - Upload product image
    - Set name, description, price, category
    - Enable/disable product
    - Form validation
  - Story Points: 5

- **ADMIN-004:** As an admin, I want to edit existing products
  - Acceptance Criteria:
    - Edit product form
    - Pre-filled with current data
    - Update product information
    - Change product image
  - Story Points: 3

- **ADMIN-005:** As an admin, I want to delete products
  - Acceptance Criteria:
    - Delete button with confirmation
    - Soft delete or permanent delete
    - Cannot delete if in active orders
  - Story Points: 2

- **ADMIN-006:** As an admin, I want to manage categories
  - Acceptance Criteria:
    - View all categories
    - Add new category
    - Edit/delete category
    - Assign products to categories
  - Story Points: 5

#### Sprint 4: Order Management (Week 4-5)

- **ADMIN-007:** As an admin, I want to view all orders
  - Acceptance Criteria:
    - Orders table with pagination
    - Show order number, customer, total, status, date
    - Filter by status and date
    - Search by customer or order number
  - Story Points: 5

- **ADMIN-008:** As an admin, I want to view order details
  - Acceptance Criteria:
    - Click order to see full details
    - Display customer info, items, total
    - Show order status and timestamps
  - Story Points: 3

- **ADMIN-009:** As an admin, I want to update order status
  - Acceptance Criteria:
    - Status dropdown (Pending, Confirmed, Preparing, Ready, Delivered, Cancelled)
    - Update status with confirmation
    - Log status changes with timestamp
  - Story Points: 3

#### Sprint 5: Settings & Configuration (Week 5-6)

- **ADMIN-010:** As an admin, I want to configure store settings
  - Acceptance Criteria:
    - Store name, address, contact
    - WhatsApp business number
    - Operating hours
    - Delivery areas/fees
    - Save settings
  - Story Points: 5

- **ADMIN-011:** As an admin, I want to manage admin users
  - Acceptance Criteria:
    - View admin users
    - Add new admin
    - Edit/delete admin
    - Password reset
  - Story Points: 3

---

### Epic 4: Enhancement & Polish
**Priority:** Medium  
**Story Points:** 20

#### Sprint 6: Testing & Refinement (Week 6-7)

- **ENH-001:** As a user, I want a responsive mobile-friendly design
  - Acceptance Criteria:
    - Mobile responsive layout
    - Touch-friendly buttons
    - Optimized for 320px+ screens
  - Story Points: 5

- **ENH-002:** As a user, I want fast page loading
  - Acceptance Criteria:
    - Image optimization
    - Lazy loading implementation
    - API response caching
  - Story Points: 3

- **ENH-003:** As a customer, I want to see product availability
  - Acceptance Criteria:
    - "In Stock" / "Out of Stock" badge
    - Disable add to cart for unavailable items
  - Story Points: 2

- **ENH-004:** As an admin, I want order notifications
  - Acceptance Criteria:
    - Browser notification for new orders (optional)
    - Badge count on orders menu
  - Story Points: 3

- **TEST-001:** As QA, I need comprehensive testing
  - Acceptance Criteria:
    - Unit tests for critical functions
    - Feature tests for main flows
    - Manual testing checklist completed
  - Story Points: 5

- **DEPLOY-001:** As a developer, I need deployment documentation
  - Acceptance Criteria:
    - Deployment guide written
    - Environment variables documented
    - Staging environment ready
  - Story Points: 2

---

## Sprint Planning

### Sprint 1 (Week 1-2): Foundation & Product Catalog
**Goal:** Setup project and customer can browse products  
**Story Points:** 16  
**Stories:** DEV-001, DEV-002, DEV-003, CUST-001, CUST-002, CUST-003, CUST-004

### Sprint 2 (Week 2-3): Shopping & Checkout
**Goal:** Customer can add to cart and checkout via WhatsApp  
**Story Points:** 16  
**Stories:** CUST-005, CUST-006, CUST-007, CUST-008, CUST-009

### Sprint 3 (Week 3-4): Admin Product Management
**Goal:** Admin can manage products and categories  
**Story Points:** 23  
**Stories:** ADMIN-001, ADMIN-002, ADMIN-003, ADMIN-004, ADMIN-005, ADMIN-006

### Sprint 4 (Week 4-5): Admin Order Management
**Goal:** Admin can view and manage orders  
**Story Points:** 11  
**Stories:** ADMIN-007, ADMIN-008, ADMIN-009

### Sprint 5 (Week 5-6): Settings & Configuration
**Goal:** Admin can configure store settings  
**Story Points:** 8  
**Stories:** ADMIN-010, ADMIN-011

### Sprint 6 (Week 6-7): Testing & Polish
**Goal:** App is tested, optimized, and ready for deployment  
**Story Points:** 20  
**Stories:** ENH-001, ENH-002, ENH-003, ENH-004, TEST-001, DEPLOY-001

---

## Daily Scrum Template

### Daily Standup Questions:
1. What did I complete yesterday?
2. What will I work on today?
3. Are there any blockers?

### Example Daily Update:
```
Date: [Date]
Developer: [Name]

Yesterday:
- Completed CUST-001: Product listing page
- Started CUST-002: Category filtering

Today:
- Complete CUST-002: Category filtering
- Begin CUST-003: Product search

Blockers:
- None / [Describe blocker]
```

---

## Definition of Done (DoD)

A user story is considered "Done" when:
- [ ] Code written and follows coding standards
- [ ] Code reviewed (if team > 1)
- [ ] Unit tests written (for critical features)
- [ ] Feature tested manually
- [ ] Responsive design verified
- [ ] No console errors
- [ ] Merged to main/develop branch
- [ ] Documentation updated (if needed)

---

## Sprint Review Checklist

At end of each sprint:
- [ ] Demo completed features to stakeholder/client
- [ ] Gather feedback
- [ ] Update product backlog based on feedback
- [ ] Celebrate completed work!

---

## Sprint Retrospective Template

### What went well?
- List positive aspects

### What could be improved?
- List challenges or issues

### Action items for next sprint:
- Concrete steps to improve

---

## Technical Debt Tracker

| ID | Description | Priority | Estimated Effort |
|----|-------------|----------|------------------|
| TD-001 | Add database indexes for performance | Medium | 2 hours |
| TD-002 | Implement proper error handling | High | 4 hours |
| TD-003 | Add API rate limiting | Low | 2 hours |

---

## Risk Management

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Client changes requirements | High | Medium | Regular communication, flexible backlog |
| WhatsApp API changes | Medium | Low | Document API usage, have fallback plan |
| Performance issues with many products | Medium | Low | Implement pagination early, optimize queries |
| Scope creep | High | High | Strict sprint planning, clear DoD |

---

## Communication Plan

- **Daily Standup:** Every morning (15 min)
- **Sprint Planning:** Start of each sprint (1-2 hours)
- **Sprint Review:** End of each sprint (1 hour)
- **Sprint Retrospective:** After sprint review (30 min)
- **Client Demo:** Every 2 sprints or as needed

---

## Success Metrics

### MVP Success Criteria:
- [ ] Customers can browse and search products
- [ ] Customers can checkout via WhatsApp
- [ ] Admin can manage products and categories
- [ ] Admin can view and update orders
- [ ] App is mobile responsive
- [ ] No critical bugs

### Performance Targets:
- Page load time < 3 seconds
- Time to interactive < 5 seconds
- 100% of critical user flows working

---

## Notes

- Adjust story points based on actual velocity after Sprint 1
- Keep sprints flexible for urgent client requests
- Document all major decisions
- Regular git commits with clear messages
- Back up database regularly during development
