# Frontend Component Diagrams

**Penulis**: Zulfikar Hidayatullah

## Component Hierarchy

```mermaid
flowchart TB
    subgraph App["App Entry (app.ts)"]
        CreateApp["createApp()"]
    end

    subgraph Layouts["Layouts"]
        AppLayout["AppLayout.vue"]
        AuthLayout["AuthLayout.vue"]
        AdminLayout["AdminLayout.vue"]
    end

    subgraph Pages["Pages"]
        subgraph Store["Store Pages"]
            Home["Home.vue"]
            Cart["Cart.vue"]
            Checkout["Checkout.vue"]
            ProductDetail["ProductDetail.vue"]
            OrderSuccess["OrderSuccess.vue"]
        end
        
        subgraph Admin["Admin Pages"]
            Dashboard["Dashboard.vue"]
            Products["Products/Index.vue"]
            Categories["Categories/Index.vue"]
            Orders["Orders/Index.vue"]
            Settings["Settings/Index.vue"]
        end
        
        subgraph Auth["Auth Pages"]
            Login["Login.vue"]
            Register["Register.vue"]
            ForgotPassword["ForgotPassword.vue"]
        end
    end

    CreateApp --> Layouts
    AppLayout --> Store
    AdminLayout --> Admin
    AuthLayout --> Auth

    style App fill:#1e3a5f,color:#fff
    style Layouts fill:#3b82f6,color:#fff
    style Store fill:#22c55e,color:#fff
    style Admin fill:#ef4444,color:#fff
    style Auth fill:#f59e0b,color:#fff
```

## Store Components Structure

```mermaid
flowchart TB
    subgraph StoreComponents["Store Components"]
        subgraph Layout["Layout Components"]
            StoreHeader["StoreHeader.vue"]
            StoreFooter["StoreFooter.vue"]
        end
        
        subgraph Product["Product Components"]
            ProductCard["ProductCard.vue"]
            CategoryFilter["CategoryFilter.vue"]
            SearchBar["SearchBar.vue"]
            PriceDisplay["PriceDisplay.vue"]
        end
        
        subgraph Cart["Cart Components"]
            CartItem["CartItem.vue"]
            CartCounter["CartCounter.vue"]
        end
        
        subgraph Order["Order Components"]
            OrderStatusBadge["OrderStatusBadge.vue"]
            EmptyState["EmptyState.vue"]
        end
    end

    subgraph Pages["Store Pages"]
        HomePage["Home.vue"]
        CartPage["Cart.vue"]
        CheckoutPage["Checkout.vue"]
    end

    HomePage --> StoreHeader
    HomePage --> CategoryFilter
    HomePage --> SearchBar
    HomePage --> ProductCard
    HomePage --> StoreFooter

    CartPage --> StoreHeader
    CartPage --> CartItem
    CartPage --> CartCounter
    CartPage --> StoreFooter

    CheckoutPage --> StoreHeader
    CheckoutPage --> PriceDisplay
    CheckoutPage --> StoreFooter

    style StoreComponents fill:#dcfce7,stroke:#22c55e
    style Pages fill:#dbeafe,stroke:#3b82f6
```

## UI Component Library

```mermaid
mindmap
    root((UI Components))
        Form
            Button
            Input
            Textarea
            Select
            Checkbox
            Switch
        Feedback
            Alert
            Toast
            Badge
            Skeleton
        Layout
            Card
            Dialog
            Sheet
            Separator
            Tabs
        Navigation
            Breadcrumbs
            Pagination
            DropdownMenu
        Data Display
            Avatar
            Table
            DataTable
```

## Admin Components Structure

```mermaid
flowchart TB
    subgraph AdminComponents["Admin Components"]
        AdminBottomNav["AdminBottomNav.vue"]
        NewOrderAlert["NewOrderAlert.vue"]
        OrderCard["OrderCard.vue"]
        PasswordConfirmDialog["PasswordConfirmDialog.vue"]
        SortableHeader["SortableHeader.vue"]
    end

    subgraph AdminPages["Admin Pages"]
        AdminDashboard["Dashboard.vue"]
        AdminProducts["Products/Index.vue"]
        AdminOrders["Orders/Index.vue"]
        AdminSettings["Settings/Index.vue"]
    end

    AdminDashboard --> NewOrderAlert
    AdminDashboard --> OrderCard

    AdminProducts --> SortableHeader
    AdminProducts --> PasswordConfirmDialog

    AdminOrders --> OrderCard
    AdminOrders --> SortableHeader

    AdminSettings --> PasswordConfirmDialog

    AdminDashboard --> AdminBottomNav
    AdminProducts --> AdminBottomNav
    AdminOrders --> AdminBottomNav

    style AdminComponents fill:#fee2e2,stroke:#ef4444
    style AdminPages fill:#fef3c7,stroke:#f59e0b
```

## Composables (Hooks)

```mermaid
flowchart LR
    subgraph Composables["Composables"]
        useAppearance["useAppearance<br/>Theme management"]
        useHapticFeedback["useHapticFeedback<br/>Mobile vibration"]
        useInitials["useInitials<br/>Name initials"]
        useMobileDetect["useMobileDetect<br/>Device detection"]
        useMotionV["useMotionV<br/>Animation"]
        useOrderNotifications["useOrderNotifications<br/>Real-time alerts"]
        useSpringAnimation["useSpringAnimation<br/>Spring physics"]
        useTwoFactorAuth["useTwoFactorAuth<br/>2FA helpers"]
    end

    subgraph Usage["Component Usage"]
        Header["Header Component"]
        Settings["Settings Page"]
        ProductCard["Product Card"]
        AdminDashboard["Admin Dashboard"]
        TwoFactorSettings["2FA Settings"]
    end

    useAppearance --> Settings
    useHapticFeedback --> ProductCard
    useMobileDetect --> Header
    useMotionV --> ProductCard
    useSpringAnimation --> ProductCard
    useOrderNotifications --> AdminDashboard
    useTwoFactorAuth --> TwoFactorSettings
    useInitials --> Header

    style Composables fill:#f3e8ff,stroke:#a855f7
    style Usage fill:#dbeafe,stroke:#3b82f6
```

## Page Data Flow (Inertia Props)

```mermaid
flowchart TB
    subgraph Backend["Laravel Controller"]
        Controller["ProductController"]
        Service["ProductService"]
        Resource["ProductResource"]
    end

    subgraph Inertia["Inertia Response"]
        Render["Inertia::render()"]
        Props["Props Object"]
    end

    subgraph Frontend["Vue Component"]
        DefineProps["defineProps()"]
        Template["Template"]
        Computed["Computed Properties"]
    end

    Controller --> Service
    Service -->|"Eloquent Collection"| Resource
    Resource -->|"Transform"| Props
    Props --> Render
    Render -->|"HTTP Response"| DefineProps
    DefineProps --> Computed
    Computed --> Template

    style Backend fill:#ff2d20,color:#fff
    style Inertia fill:#6366f1,color:#fff
    style Frontend fill:#42b883,color:#fff
```

## Mobile-First Responsive Design

```mermaid
flowchart TB
    subgraph Breakpoints["Tailwind Breakpoints"]
        Mobile["Default<br/>(mobile-first)"]
        SM["sm: 640px"]
        MD["md: 768px"]
        LG["lg: 1024px"]
        XL["xl: 1280px"]
    end

    subgraph Layout["Layout Changes"]
        MobileLayout["Single Column<br/>Bottom Nav<br/>Full-width Cards"]
        TabletLayout["2 Columns<br/>Side Nav Option<br/>Compact Cards"]
        DesktopLayout["3-4 Columns<br/>Sidebar Nav<br/>Grid Layout"]
    end

    Mobile --> MobileLayout
    SM --> MobileLayout
    MD --> TabletLayout
    LG --> DesktopLayout
    XL --> DesktopLayout

    style Breakpoints fill:#dbeafe,stroke:#3b82f6
    style Layout fill:#dcfce7,stroke:#22c55e
```

## Animation System (Motion-V)

```mermaid
flowchart TB
    subgraph MotionV["Motion-V Library"]
        Spring["Spring Physics"]
        Keyframes["Keyframes"]
        Gestures["Gesture Detection"]
    end

    subgraph Animations["iOS-like Animations"]
        Press["Press Feedback<br/>scale: 0.97"]
        Stagger["Staggered List<br/>animation-delay"]
        Slide["Slide Transitions<br/>x, y transforms"]
        Fade["Fade Effects<br/>opacity"]
    end

    subgraph Components["Applied To"]
        Buttons["Buttons"]
        Cards["Product Cards"]
        Lists["Product Lists"]
        Modals["Dialogs/Sheets"]
        PageTrans["Page Transitions"]
    end

    Spring --> Press
    Spring --> Slide
    Keyframes --> Fade
    Keyframes --> Stagger
    Gestures --> Press

    Press --> Buttons
    Press --> Cards
    Stagger --> Lists
    Slide --> Modals
    Fade --> PageTrans

    style MotionV fill:#f3e8ff,stroke:#a855f7
    style Animations fill:#fef3c7,stroke:#f59e0b
    style Components fill:#dcfce7,stroke:#22c55e
```

