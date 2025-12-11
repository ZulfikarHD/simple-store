# Authentication Flow Diagrams

**Penulis**: Zulfikar Hidayatullah

## Login Flow

```mermaid
sequenceDiagram
    autonumber
    participant U as User
    participant F as Frontend
    participant L as Laravel Fortify
    participant DB as Database
    participant S as Session

    U->>F: Navigate to /login
    F->>L: GET /login
    L-->>F: Login.vue Page

    U->>F: Enter Credentials
    F->>L: POST /login
    
    alt Invalid Credentials
        L-->>F: 422 Validation Error
        F-->>U: Show Error Message
    else Valid Credentials
        L->>DB: Verify User
        DB-->>L: User Found
        
        alt 2FA Enabled
            L-->>F: Redirect to /two-factor-challenge
            F-->>U: Show 2FA Form
            U->>F: Enter OTP Code
            F->>L: POST /two-factor-challenge
            L->>DB: Verify OTP
            
            alt Valid OTP
                L->>S: Create Session
                L-->>F: Redirect to Dashboard
            else Invalid OTP
                L-->>F: 422 Error
                F-->>U: Show Error
            end
        else No 2FA
            L->>S: Create Session
            L-->>F: Redirect to Dashboard
        end
    end
    
    F-->>U: Show Dashboard
```

## Registration Flow

```mermaid
sequenceDiagram
    autonumber
    participant U as User
    participant F as Frontend
    participant L as Laravel Fortify
    participant A as CreateNewUser Action
    participant DB as Database
    participant M as Mail

    U->>F: Navigate to /register
    F->>L: GET /register
    L-->>F: Register.vue Page

    U->>F: Fill Registration Form
    F->>L: POST /register
    L->>A: CreateNewUser Action
    
    A->>A: Validate Input
    
    alt Validation Failed
        A-->>L: Validation Error
        L-->>F: 422 with Errors
        F-->>U: Show Validation Errors
    else Validation Passed
        A->>DB: Create User
        DB-->>A: User Created
        A->>M: Send Verification Email
        A-->>L: User Instance
        L-->>F: Redirect to Dashboard
        F-->>U: Show "Verify Email" Notice
    end
```

## Two-Factor Authentication Setup

```mermaid
sequenceDiagram
    autonumber
    participant U as User
    participant F as Frontend
    participant L as Laravel Fortify
    participant DB as Database

    Note over U,DB: Enable 2FA
    U->>F: Go to Settings > 2FA
    F->>L: POST /user/two-factor-authentication
    L->>DB: Generate & Store Secret
    L-->>F: Success

    U->>F: View QR Code
    F->>L: GET /user/two-factor-qr-code
    L-->>F: QR Code SVG
    F-->>U: Display QR Code

    U->>U: Scan with Authenticator App
    U->>F: Enter Confirmation Code
    F->>L: POST /user/confirmed-two-factor-authentication
    
    alt Valid Code
        L->>DB: Set two_factor_confirmed_at
        L-->>F: Success
        F-->>U: Show Recovery Codes
    else Invalid Code
        L-->>F: 422 Error
        F-->>U: Show Error
    end

    Note over U,DB: Get Recovery Codes
    U->>F: View Recovery Codes
    F->>L: GET /user/two-factor-recovery-codes
    L-->>F: Recovery Codes Array
    F-->>U: Display Codes (Save These!)
```

## Password Reset Flow

```mermaid
sequenceDiagram
    autonumber
    participant U as User
    participant F as Frontend
    participant L as Laravel Fortify
    participant DB as Database
    participant M as Mail

    U->>F: Click "Forgot Password"
    F->>L: GET /forgot-password
    L-->>F: ForgotPassword.vue

    U->>F: Enter Email
    F->>L: POST /forgot-password
    L->>DB: Find User by Email
    
    alt User Not Found
        L-->>F: 422 Error
        F-->>U: "Email not found"
    else User Found
        L->>DB: Create Reset Token
        L->>M: Send Reset Email
        L-->>F: Success
        F-->>U: "Check your email"
    end

    Note over U,M: User clicks email link
    U->>F: Click Reset Link
    F->>L: GET /reset-password/{token}
    L-->>F: ResetPassword.vue

    U->>F: Enter New Password
    F->>L: POST /reset-password
    L->>DB: Verify Token
    
    alt Invalid/Expired Token
        L-->>F: 422 Error
        F-->>U: "Invalid token"
    else Valid Token
        L->>DB: Update Password
        L->>DB: Delete Token
        L-->>F: Redirect to Login
        F-->>U: "Password updated!"
    end
```

## Authentication State Diagram

```mermaid
stateDiagram-v2
    [*] --> Guest: Initial State
    
    Guest --> Login: POST /login
    Login --> Dashboard: Success (no 2FA)
    Login --> TwoFactorChallenge: 2FA Required
    Login --> Guest: Failed
    
    TwoFactorChallenge --> Dashboard: Valid OTP
    TwoFactorChallenge --> Guest: Failed
    
    Guest --> Register: POST /register
    Register --> VerifyEmail: Email Sent
    VerifyEmail --> Dashboard: Email Verified
    
    Dashboard --> Guest: POST /logout
    Dashboard --> PasswordConfirm: Sensitive Action
    PasswordConfirm --> Dashboard: Confirmed
    PasswordConfirm --> Dashboard: Cancelled
    
    state Dashboard {
        [*] --> CustomerArea: role=customer
        [*] --> AdminArea: role=admin
    }
```

## Role-Based Access Control

```mermaid
flowchart TB
    subgraph Guest["Guest (Unauthenticated)"]
        G1["Browse Products"]
        G2["View Cart"]
        G3["Checkout"]
        G4["Login/Register"]
    end

    subgraph Customer["Customer Role"]
        C1["All Guest Actions"]
        C2["View Order History"]
        C3["Account Settings"]
        C4["2FA Management"]
    end

    subgraph Admin["Admin Role"]
        A1["All Customer Actions"]
        A2["Admin Dashboard"]
        A3["Manage Products"]
        A4["Manage Categories"]
        A5["Manage Orders"]
        A6["Store Settings"]
    end

    Guest -->|"auth"| Customer
    Customer -->|"admin middleware"| Admin

    style Guest fill:#f3f4f6,stroke:#6b7280
    style Customer fill:#dbeafe,stroke:#3b82f6
    style Admin fill:#fee2e2,stroke:#ef4444
```

## Session Management

```mermaid
flowchart LR
    subgraph Client
        Browser["Browser"]
        Cookie["Session Cookie<br/>(Encrypted)"]
    end

    subgraph Server
        Laravel["Laravel"]
        Session["Session Handler"]
    end

    subgraph Storage
        DB[("sessions table")]
    end

    Browser -->|"Request + Cookie"| Laravel
    Laravel --> Session
    Session <-->|"Read/Write"| DB
    Session -->|"Set Cookie"| Cookie
    Cookie --> Browser

    style Client fill:#dbeafe,stroke:#3b82f6
    style Server fill:#dcfce7,stroke:#22c55e
    style Storage fill:#fef3c7,stroke:#f59e0b
```

