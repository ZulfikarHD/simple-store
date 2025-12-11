# Struktur File

## Root Directory
```
simple-store/
├── app/                    # Core aplikasi
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── database/               # Database migrations, seeders, factories
├── public/                 # Public assets & entry point
├── resources/              # Views, JS, CSS
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploads
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── .env                    # Environment configuration
├── artisan                 # Laravel CLI
├── composer.json           # PHP dependencies
├── package.json            # Node dependencies
└── vite.config.js          # Vite configuration
```

## App Directory (`app/`)
```
app/
├── Console/
│   └── Commands/           # Artisan commands (auto-registered)
├── Exceptions/             # Exception handling
├── Http/
│   ├── Controllers/        # Request controllers
│   ├── Middleware/         # Custom middleware (jika ada)
│   └── Requests/           # Form request validation
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies (jika ada)
├── Providers/              # Service providers
└── Services/               # Business logic services (optional)
```

## Resources Directory (`resources/`)
```
resources/
├── css/
│   └── app.css             # Main stylesheet (Tailwind)
├── js/
│   ├── Components/         # Vue components
│   ├── Layouts/            # Layout components
│   ├── Pages/              # Inertia pages
│   ├── actions/            # Wayfinder generated actions
│   ├── routes/             # Wayfinder generated routes
│   └── app.js              # Main JS entry
└── views/
    └── app.blade.php       # Root Blade template untuk Inertia
```

## Database Directory (`database/`)
```
database/
├── factories/              # Model factories untuk testing
├── migrations/             # Database migrations
└── seeders/                # Database seeders
```

## Routes Directory (`routes/`)
```
routes/
├── console.php             # Console commands routes
├── web.php                 # Web routes
└── api.php                 # API routes (jika ada)
```

## Config Directory (`config/`)
```
config/
├── app.php                 # Application config
├── auth.php                # Authentication config
├── database.php            # Database connections
├── fortify.php             # Fortify config
└── [other configs]
```

## Storage Directory (`storage/`)
```
storage/
├── app/
│   ├── public/             # User uploads (symlinked to public/storage)
│   └── private/            # Private files
├── framework/
│   ├── cache/              # Framework cache
│   ├── sessions/           # Session files
│   └── views/              # Compiled views
└── logs/                   # Application logs
```

## Tests Directory (`tests/`)
```
tests/
├── Feature/                # Feature tests
│   └── Api/                # API tests (jika ada)
└── Unit/                   # Unit tests
```

## Public Directory (`public/`)
```
public/
├── build/                  # Vite compiled assets
├── storage/                # Symlink ke storage/app/public
├── favicon.ico
└── index.php               # Application entry point
```

## Key Files

### Configuration
- `.env`: Environment variables
- `.env.example`: Environment template
- `composer.json`: PHP dependencies & scripts
- `package.json`: Node dependencies & scripts
- `vite.config.js`: Frontend build config

### Laravel Core
- `artisan`: CLI tool
- `bootstrap/app.php`: Application bootstrap & middleware registration
- `bootstrap/providers.php`: Service providers registration

### Version Control
- `.gitignore`: Git ignore rules
- `.gitattributes`: Git attributes

### Code Quality
- `pint.json`: Laravel Pint configuration (jika ada)
- `eslint.config.js`: ESLint configuration (jika ada)
- `.prettierrc`: Prettier configuration (jika ada)

## Naming Conventions

### Controllers
- Format: `{Resource}Controller.php`
- Example: `UserController.php`, `ProductController.php`

### Models
- Format: Singular, PascalCase
- Example: `User.php`, `Product.php`

### Migrations
- Format: `yyyy_mm_dd_hhmmss_description.php`
- Example: `2024_01_01_000000_create_users_table.php`

### Vue Components
- Format: PascalCase
- Example: `UserProfile.vue`, `ProductCard.vue`

### Services (jika ada)
- Format: `{Resource}Service.php`
- Example: `UserService.php`, `PaymentService.php`


