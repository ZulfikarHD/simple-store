# Deployment Architecture Diagrams

**Penulis**: Zulfikar Hidayatullah

## Production Deployment Architecture

```mermaid
flowchart TB
    subgraph Internet["🌐 Internet"]
        Users["Users/Customers"]
        Admin["Admin"]
    end

    subgraph CDN["CDN (Optional)"]
        CloudFlare["CloudFlare/AWS CloudFront"]
    end

    subgraph Server["🖥️ Production Server"]
        subgraph WebServer["Web Server"]
            Nginx["Nginx<br/>:443 (HTTPS)"]
        end
        
        subgraph App["Application"]
            PHP["PHP-FPM 8.4"]
            Laravel["Laravel 12"]
            Queue["Queue Worker"]
        end
        
        subgraph Storage["Storage"]
            SQLite[("SQLite<br/>Database")]
            Files["File Storage<br/>(uploads)"]
            Logs["Logs"]
            Cache["Cache"]
        end
    end

    subgraph SSL["SSL/TLS"]
        LetsEncrypt["Let's Encrypt<br/>Certificate"]
    end

    subgraph External["External Services"]
        WhatsApp["WhatsApp API"]
        Mail["SMTP Server"]
    end

    Users --> CloudFlare
    Admin --> CloudFlare
    CloudFlare --> Nginx
    Nginx --> PHP
    PHP --> Laravel
    Laravel --> SQLite
    Laravel --> Files
    Laravel --> Cache
    Laravel --> Logs
    Laravel --> Queue
    Queue --> Mail
    Laravel --> WhatsApp
    Nginx -.-> LetsEncrypt

    style Internet fill:#e0f2fe,stroke:#0284c7
    style Server fill:#f0fdf4,stroke:#16a34a
    style External fill:#fce7f3,stroke:#db2777
```

## Docker Deployment Architecture

```mermaid
flowchart TB
    subgraph DockerHost["Docker Host"]
        subgraph Network["Docker Network: simple-store"]
            subgraph NginxContainer["nginx container"]
                Nginx["Nginx<br/>:80, :443"]
            end
            
            subgraph AppContainer["app container"]
                PHP["PHP-FPM 8.4"]
                Laravel["Laravel App"]
            end
            
            subgraph Volumes["Shared Volumes"]
                AppCode["/var/www<br/>(app code)"]
                Storage["/var/www/storage"]
                SQLiteDB["/var/www/database"]
            end
        end
    end

    subgraph External["External"]
        Internet["Internet"]
        SSL["SSL Certificate"]
    end

    Internet -->|":80/:443"| Nginx
    Nginx -->|":9000"| PHP
    PHP --> Laravel
    Laravel --> AppCode
    Laravel --> Storage
    Laravel --> SQLiteDB
    SSL -.-> Nginx

    style DockerHost fill:#1e3a5f,color:#fff
    style Network fill:#2d4a6f,color:#fff
    style External fill:#f3f4f6,stroke:#6b7280
```

## Request Flow in Production

```mermaid
sequenceDiagram
    participant U as User
    participant DNS as DNS
    participant CF as CloudFlare (CDN)
    participant NG as Nginx
    participant PHP as PHP-FPM
    participant LV as Laravel
    participant DB as SQLite

    U->>DNS: Resolve domain
    DNS-->>U: IP Address
    U->>CF: HTTPS Request
    
    alt Static Asset
        CF-->>U: Cached Response
    else Dynamic Request
        CF->>NG: Forward Request
        NG->>PHP: FastCGI
        PHP->>LV: Process Request
        LV->>DB: Query Data
        DB-->>LV: Results
        LV-->>PHP: Response
        PHP-->>NG: HTML/JSON
        NG-->>CF: Response
        CF-->>U: Final Response
    end
```

## CI/CD Pipeline

```mermaid
flowchart LR
    subgraph Dev["Development"]
        Local["Local Dev"]
        Git["Git Commit"]
    end

    subgraph CI["CI Pipeline"]
        Tests["Run Tests"]
        Lint["Code Lint"]
        Build["Build Assets"]
    end

    subgraph Deploy["Deployment"]
        Staging["Staging Server"]
        Production["Production Server"]
    end

    Local -->|"git push"| Git
    Git -->|"trigger"| Tests
    Tests --> Lint
    Lint --> Build
    Build -->|"auto deploy"| Staging
    Staging -->|"manual approve"| Production

    style Dev fill:#dbeafe,stroke:#3b82f6
    style CI fill:#fef3c7,stroke:#f59e0b
    style Deploy fill:#dcfce7,stroke:#22c55e
```

## Server Directory Structure

```mermaid
flowchart TB
    subgraph Root["/var/www/simple-store"]
        subgraph Current["current/ (symlink)"]
            App["app/"]
            Bootstrap["bootstrap/"]
            Config["config/"]
            Public["public/"]
            Resources["resources/"]
            Routes["routes/"]
            Vendor["vendor/"]
        end
        
        subgraph Shared["shared/"]
            Env[".env"]
            SharedStorage["storage/"]
            Database["database/database.sqlite"]
        end
        
        subgraph Releases["releases/"]
            R1["20251201120000/"]
            R2["20251205150000/"]
            R3["20251210180000/"]
        end
    end

    Current -->|"symlink"| R3
    Current -.->|"symlink"| Env
    Current -.->|"symlink"| SharedStorage
    Current -.->|"symlink"| Database

    style Root fill:#f3f4f6
    style Current fill:#dcfce7,stroke:#22c55e
    style Shared fill:#dbeafe,stroke:#3b82f6
    style Releases fill:#fef3c7,stroke:#f59e0b
```

## Backup Strategy

```mermaid
flowchart TB
    subgraph App["Application"]
        DB[("SQLite DB")]
        Storage["File Storage"]
        Env[".env Config"]
    end

    subgraph Backup["Backup Process"]
        Cron["Cron Job<br/>(Daily 2AM)"]
        Script["backup.sh"]
    end

    subgraph Local["Local Backup"]
        LocalDB["db-YYYYMMDD.sqlite"]
        LocalFiles["storage-YYYYMMDD.tar.gz"]
        LocalEnv["env-YYYYMMDD.gpg"]
    end

    subgraph Remote["Remote Backup (Optional)"]
        S3["AWS S3"]
        GCS["Google Cloud Storage"]
    end

    Cron --> Script
    Script --> DB
    Script --> Storage
    Script --> Env
    DB --> LocalDB
    Storage --> LocalFiles
    Env -->|"encrypted"| LocalEnv
    LocalDB --> S3
    LocalFiles --> S3
    LocalEnv --> S3

    style App fill:#fee2e2,stroke:#ef4444
    style Backup fill:#dbeafe,stroke:#3b82f6
    style Local fill:#dcfce7,stroke:#22c55e
    style Remote fill:#f3e8ff,stroke:#a855f7
```

## Monitoring Setup

```mermaid
flowchart TB
    subgraph App["Application"]
        Laravel["Laravel App"]
        Logs["Laravel Logs"]
    end

    subgraph Monitoring["Monitoring Tools"]
        Health["Health Check<br/>/up endpoint"]
        LogRotate["Log Rotation"]
        Metrics["Performance Metrics"]
    end

    subgraph Alerts["Alert Channels"]
        Email["Email"]
        Slack["Slack"]
        SMS["SMS"]
    end

    subgraph External["External Services"]
        UptimeRobot["UptimeRobot"]
        Sentry["Sentry (Optional)"]
    end

    Laravel --> Logs
    Laravel --> Health
    Logs --> LogRotate
    Health --> UptimeRobot
    Laravel --> Sentry
    UptimeRobot -->|"downtime"| Email
    Sentry -->|"errors"| Slack

    style App fill:#fee2e2,stroke:#ef4444
    style Monitoring fill:#dbeafe,stroke:#3b82f6
    style Alerts fill:#fef3c7,stroke:#f59e0b
    style External fill:#dcfce7,stroke:#22c55e
```

