sequenceDiagram
    participant C as Customer
    participant FE as Frontend
    participant BE as Backend
    participant DB as Database
    participant WA as WhatsApp
    participant O as Owner

    Note over C: Must be logged in
    C->>FE: Click Checkout
    FE->>BE: POST /checkout (rate limited)
    BE->>DB: Create Order + Items
    BE->>BE: Generate WhatsApp URL with admin link
    BE->>FE: Redirect to OrderSuccess
    FE->>C: Show success + WhatsApp button
    C->>WA: Click "Konfirmasi via WhatsApp"
    WA->>O: Message with Invoice + Admin Link
    
    Note over O: Owner clicks link in WhatsApp
    O->>FE: Open Admin Order Detail
    O->>BE: Update status to "confirmed"
    O->>WA: Click "Kirim via WhatsApp" (template)
    WA->>C: Confirmation message
    
    Note over BE: Background Job (every minute)
    BE->>DB: Check pending orders > X minutes
    BE->>DB: Auto-cancel expired orders
