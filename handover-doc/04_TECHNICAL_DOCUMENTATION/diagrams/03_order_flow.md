# Order Flow Diagrams

**Penulis**: Zulfikar Hidayatullah

## Order Status State Machine

```mermaid
stateDiagram-v2
    [*] --> Pending: Order Created
    
    Pending --> Confirmed: Admin Confirms
    Pending --> Cancelled: Admin/System Cancels
    
    Confirmed --> Preparing: Start Processing
    Confirmed --> Cancelled: Admin Cancels
    
    Preparing --> Ready: Order Complete
    Preparing --> Cancelled: Admin Cancels
    
    Ready --> Delivered: Delivered to Customer
    Ready --> Cancelled: Customer No-show
    
    Delivered --> [*]: Order Complete
    Cancelled --> [*]: Order Ended

    note right of Pending
        Menunggu konfirmasi dari admin
        WhatsApp notification sent
    end note

    note right of Confirmed
        Admin telah mengkonfirmasi
        confirmed_at timestamp set
    end note

    note right of Preparing
        Pesanan sedang diproses
        preparing_at timestamp set
    end note

    note right of Ready
        Pesanan siap diambil/dikirim
        ready_at timestamp set
    end note

    note right of Delivered
        Pesanan telah diterima
        delivered_at timestamp set
    end note

    note right of Cancelled
        Pesanan dibatalkan
        cancelled_at + reason set
    end note
```

## Customer Checkout Flow

```mermaid
sequenceDiagram
    autonumber
    participant C as Customer
    participant F as Frontend (Vue)
    participant B as Backend (Laravel)
    participant DB as Database
    participant WA as WhatsApp

    Note over C,WA: Shopping Phase
    C->>F: Browse Products
    F->>B: GET / (ProductController@index)
    B->>DB: Query Products & Categories
    DB-->>B: Product Data
    B-->>F: Inertia Response (Home.vue)
    F-->>C: Display Catalog

    Note over C,WA: Add to Cart
    C->>F: Click "Add to Cart"
    F->>B: POST /cart (CartController@store)
    B->>DB: Create/Update CartItem
    DB-->>B: Success
    B-->>F: Redirect with Flash
    F-->>C: Show Cart Updated

    Note over C,WA: Checkout Process
    C->>F: Go to Checkout
    F->>B: GET /checkout
    B->>DB: Get Cart + Settings
    DB-->>B: Cart Data
    B-->>F: Checkout.vue with props
    F-->>C: Display Checkout Form

    C->>F: Fill Form & Submit
    F->>B: POST /checkout (CheckoutController@store)
    B->>DB: Create Order + OrderItems
    B->>DB: Clear Cart
    DB-->>B: Order Created
    B-->>F: Redirect to Success Page
    
    Note over C,WA: WhatsApp Notification
    F-->>C: Display Order Success
    C->>WA: Click "Konfirmasi via WhatsApp"
    WA-->>C: Open WhatsApp with Pre-filled Message
```

## Admin Order Management Flow

```mermaid
sequenceDiagram
    autonumber
    participant A as Admin
    participant F as Frontend (Vue)
    participant B as Backend (Laravel)
    participant DB as Database
    participant WA as WhatsApp
    participant C as Customer

    Note over A,C: View Orders
    A->>F: Open Admin Dashboard
    F->>B: GET /admin/orders
    B->>DB: Query Orders with Filters
    DB-->>B: Paginated Orders
    B-->>F: Admin/Orders/Index.vue
    F-->>A: Display Order List

    Note over A,C: Update Order Status
    A->>F: Click "Konfirmasi" on Order
    F->>B: PATCH /admin/orders/{id}/status
    B->>DB: Update Order Status
    B->>DB: Set confirmed_at timestamp
    DB-->>B: Updated Order
    B-->>F: Success + WhatsApp URL
    F-->>A: Show Success Message

    Note over A,C: Notify Customer
    A->>F: Click "Kirim Notifikasi WA"
    F->>WA: Open wa.me URL
    WA-->>C: WhatsApp Message Received
    C-->>A: Customer Confirms Receipt
```

## Order Processing Timeline

```mermaid
gantt
    title Order Lifecycle Timeline
    dateFormat HH:mm
    axisFormat %H:%M

    section Customer
    Browse Products           :c1, 09:00, 15m
    Add to Cart              :c2, after c1, 5m
    Checkout                 :c3, after c2, 10m
    Send WhatsApp            :c4, after c3, 2m
    
    section Admin
    Receive Notification     :a1, after c4, 1m
    Confirm Order            :a2, after a1, 2m
    Process Order            :a3, after a2, 30m
    Mark Ready               :a4, after a3, 2m
    
    section Delivery
    Deliver to Customer      :d1, after a4, 20m
    Mark Delivered           :d2, after d1, 1m
```

## Order Data Flow

```mermaid
flowchart TB
    subgraph Customer["Customer Actions"]
        Browse["Browse Products"]
        AddCart["Add to Cart"]
        Checkout["Checkout"]
        SendWA["Send WhatsApp"]
    end

    subgraph System["System Processing"]
        CartService["CartService<br/>getCart(), addItem()"]
        OrderService["OrderService<br/>createOrder()"]
        WhatsAppGen["Generate<br/>WhatsApp URL"]
    end

    subgraph Database["Database"]
        CartTable[("carts +<br/>cart_items")]
        OrderTable[("orders +<br/>order_items")]
    end

    subgraph Admin["Admin Actions"]
        ViewOrders["View Orders"]
        UpdateStatus["Update Status"]
        NotifyCustomer["Notify Customer"]
    end

    Browse --> AddCart
    AddCart --> CartService
    CartService --> CartTable
    
    Checkout --> OrderService
    OrderService --> CartTable
    OrderService --> OrderTable
    OrderService --> WhatsAppGen
    WhatsAppGen --> SendWA

    ViewOrders --> OrderTable
    UpdateStatus --> OrderTable
    UpdateStatus --> NotifyCustomer

    style Customer fill:#dbeafe,stroke:#3b82f6
    style System fill:#dcfce7,stroke:#22c55e
    style Database fill:#fef3c7,stroke:#f59e0b
    style Admin fill:#fee2e2,stroke:#ef4444
```

