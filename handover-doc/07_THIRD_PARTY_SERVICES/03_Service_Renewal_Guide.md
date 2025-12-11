# Panduan Renewal Services

## Overview
Panduan lengkap untuk renewal third-party services yang digunakan dalam aplikasi Simple Store, yaitu: schedule, procedures, dan checklist untuk memastikan uninterrupted service.

## Renewal Calendar

### Annual Renewals
| Service | Renewal Date | Cost | Critical | Lead Time |
|---------|-------------|------|----------|-----------|
| Domain | [Date] | Rp XXX | ✓✓✓ | 30 days |
| SSL Certificate | [Date] | Rp XXX | ✓✓✓ | 30 days |
| Hosting | [Date] | Rp XXX | ✓✓✓ | 30 days |
| [Service] | [Date] | Rp XXX | ✓✓ | 14 days |

### Monthly/Subscription Services
| Service | Billing Cycle | Cost | Auto-Renew | Payment Method |
|---------|--------------|------|------------|----------------|
| WhatsApp Business API | Monthly | Rp XXX | Yes | [Card/Transfer] |
| Email Service | Monthly | Rp XXX | Yes | [Card/Transfer] |
| Cloud Storage | Monthly | Rp XXX | Yes | [Card/Transfer] |

### Pay-as-You-Go Services
| Service | Billing | Typical Monthly Cost | Budget Alert |
|---------|---------|---------------------|--------------|
| Payment Gateway | Per transaction | Rp XXX | Rp XXX |
| SMS Gateway | Per SMS | Rp XXX | Rp XXX |
| Cloud CDN | Per GB | Rp XXX | Rp XXX |

## Renewal Procedures

### 30 Days Before Renewal

#### Step 1: Review Current Usage
```bash
# Analyze usage untuk determine jika masih diperlukan
# Check analytics, logs, dan billing reports

# WhatsApp messages sent
# Payment transactions
# Email sent
# Storage used
# etc.
```

**Questions to Ask**:
- [ ] Is this service still needed?
- [ ] Is current plan appropriate atau need upgrade/downgrade?
- [ ] Are there better alternatives?
- [ ] Is pricing competitive?

#### Step 2: Budget Approval
- [ ] Review cost dengan stakeholders
- [ ] Get approval untuk renewal
- [ ] Allocate budget
- [ ] Arrange payment method

#### Step 3: Check for Updates
- [ ] Review service updates/new features
- [ ] Check for pricing changes
- [ ] Review terms of service changes
- [ ] Check for promotional offers

### 14 Days Before Renewal

#### Step 4: Prepare for Renewal
- [ ] Verify payment method valid
- [ ] Check account credentials accessible
- [ ] Review current configuration
- [ ] Backup current settings/data
- [ ] Document any custom configurations

#### Step 5: Communication
- [ ] Notify team about upcoming renewal
- [ ] Schedule maintenance window jika diperlukan
- [ ] Inform users jika akan ada downtime

### 7 Days Before Renewal

#### Step 6: Pre-Renewal Checklist
- [ ] Confirm renewal date
- [ ] Confirm payment details
- [ ] Verify auto-renewal settings
- [ ] Prepare rollback plan
- [ ] Test backup credentials (jika ada)

### Renewal Day

#### Step 7: Execute Renewal
1. **Login to service provider account**
2. **Verify renewal details**
   - Plan type
   - Pricing
   - Billing period
   - Terms
3. **Process payment**
4. **Verify renewal successful**
5. **Download invoice/receipt**
6. **Update internal records**

#### Step 8: Verify Service Continuity
```bash
# Test service masih berfungsi normal
# Check API connectivity
# Verify features still working
# Monitor logs untuk errors
```

### Post-Renewal

#### Step 9: Documentation
- [ ] Update renewal date dalam dokumentasi
- [ ] Store invoice/receipt
- [ ] Update credentials jika changed
- [ ] Document any changes dalam configuration
- [ ] Update team wiki/docs

#### Step 10: Set Next Reminder
- [ ] Add reminder untuk next renewal (30 days before)
- [ ] Update renewal calendar
- [ ] Schedule review meeting (jika diperlukan)

## Service-Specific Renewal Guides

### Domain Renewal

**Critical Level**: ✓✓✓ CRITICAL
**Lead Time**: 30 days minimum

**Procedure**:
1. Login ke domain registrar
2. Check domain expiry date
3. Enable auto-renew (recommended)
4. Verify payment method
5. Complete renewal
6. Verify WHOIS information
7. Check DNS settings unchanged

**Important Notes**:
- Domain expiry causes complete site downtime
- Grace period typically 30 days
- After grace period, domain may go to auction
- Always renew early to avoid issues

---

### SSL Certificate Renewal

**Critical Level**: ✓✓✓ CRITICAL
**Lead Time**: 30 days minimum

**Procedure**:

#### Option 1: Let's Encrypt (Free, Auto-Renew)
```bash
# Check certificate expiry
sudo certbot certificates

# Test renewal
sudo certbot renew --dry-run

# Force renewal jika diperlukan
sudo certbot renew --force-renewal

# Verify renewal successful
sudo systemctl reload nginx
```

#### Option 2: Commercial Certificate
1. Generate CSR jika diperlukan
2. Purchase renewal dari provider
3. Complete validation
4. Download new certificate
5. Install pada server
6. Test HTTPS functionality
7. Update redirect rules jika diperlukan

**Important Notes**:
- Expired SSL = Browser warnings = Lost customers
- Let's Encrypt auto-renews every 60 days
- Commercial certificates typically 1-2 years
- Test renewal process regularly

---

### Hosting Renewal

**Critical Level**: ✓✓✓ CRITICAL
**Lead Time**: 30 days minimum

**Procedure**:
1. Login ke hosting panel
2. Review current plan
3. Check for promotions/better plans
4. Complete payment
5. Verify renewal confirmation
6. Test site immediately after
7. Verify all services running

**Pre-Renewal Backup**:
```bash
# Backup website files
tar -czf website_backup_$(date +%Y%m%d).tar.gz /var/www/simple-store

# Backup database
# See: 06_DATABASE/02_Backup_Procedures.md

# Store backups securely offsite
```

---

### WhatsApp Business API Renewal

**Critical Level**: ✓✓ HIGH
**Lead Time**: 14 days

**Procedure**:
1. Review message usage
2. Check pricing tiers
3. Verify payment method
4. Complete renewal dengan provider
5. Test message sending
6. Verify webhook still functioning

**Cost Optimization**:
- Review template usage
- Optimize message frequency
- Consider tier downgrade jika usage low

---

### Payment Gateway Renewal

**Critical Level**: ✓✓✓ CRITICAL
**Lead Time**: 30 days

**Procedure**:
1. Review transaction volume
2. Review fee structure
3. Negotiate better rates jika applicable
4. Complete renewal/contract
5. Update API credentials jika changed
6. Test payment flow
7. Monitor first few transactions

**Important Notes**:
- Payment issues = Lost sales
- Have backup payment gateway ready
- Test thoroughly after renewal

## Emergency Renewal Procedures

Jika service expired unexpectedly:

### Immediate Actions
1. **Assess Impact**
   - What's broken?
   - How many users affected?
   - Business impact?

2. **Enable Maintenance Mode** (jika diperlukan)
   ```bash
   php artisan down --message="Maintenance in progress"
   ```

3. **Contact Provider Immediately**
   - Explain situation
   - Request grace period
   - Process emergency renewal

4. **Process Renewal ASAP**
   - Use fastest payment method
   - Request expedited processing
   - Get confirmation

5. **Restore Service**
   ```bash
   # Clear caches
   php artisan config:clear
   php artisan cache:clear
   
   # Test functionality
   # Disable maintenance mode
   php artisan up
   ```

6. **Communicate**
   - Notify users
   - Apologize untuk inconvenience
   - Explain resolution

7. **Post-Mortem**
   - Why did it expire?
   - How to prevent?
   - Update procedures

## Cost Management

### Annual Budget Planning
```
Hosting:                Rp XXX,XXX / year
Domain:                 Rp XXX,XXX / year
SSL:                    Rp XXX,XXX / year
WhatsApp API:           Rp XXX,XXX / year
Email Service:          Rp XXX,XXX / year
Payment Gateway:        Rp XXX,XXX / year (est.)
Cloud Storage:          Rp XXX,XXX / year
Monitoring:             Rp XXX,XXX / year
-------------------------------------------
Total Estimated:        Rp X,XXX,XXX / year
```

### Cost Optimization Tips
1. **Annual vs Monthly**: Pay annually untuk discount (typically 10-20%)
2. **Bundle Services**: Some providers offer package deals
3. **Review Regularly**: Cancel unused services
4. **Negotiate**: Especially untuk high-value services
5. **Use Credits**: Signup credits, promotional codes
6. **Right-Size**: Don't over-provision

## Renewal Checklist Template

```markdown
# Service Renewal Checklist
**Service**: [Service Name]
**Current Expiry**: [Date]
**Renewal Date**: [Date]
**Cost**: Rp XXX,XXX

## Pre-Renewal (30 days before)
- [ ] Review usage
- [ ] Check alternatives
- [ ] Get budget approval
- [ ] Verify payment method
- [ ] Backup current settings

## Renewal (14 days before)
- [ ] Confirm renewal details
- [ ] Notify team
- [ ] Schedule if needed
- [ ] Prepare rollback plan

## Execute (Renewal day)
- [ ] Process renewal
- [ ] Verify successful
- [ ] Test functionality
- [ ] Download receipt

## Post-Renewal
- [ ] Update documentation
- [ ] Store invoice
- [ ] Update credentials if changed
- [ ] Set next reminder
- [ ] Notify team completion

## Notes
[Any special notes atau issues]
```

## Automated Reminders

### Setup Calendar Reminders
```
Google Calendar / Outlook:
- 30 days before: "Review [Service] renewal"
- 14 days before: "Prepare [Service] renewal"
- 7 days before: "Execute [Service] renewal"
- Renewal day: "Complete [Service] renewal"
```

### Email Alerts
Setup email forwards untuk:
- Provider renewal notices
- Payment receipts
- Service updates
- Billing alerts

## Contact Information

### Service Provider Contacts
- **Hosting**: [Provider contact]
- **Domain**: [Registrar contact]
- **WhatsApp**: [Provider contact]
- **Payment**: [Gateway support]

### Internal Contacts
- **Primary**: Zulfikar Hidayatullah (+62 857-1583-8733)
- **Backup**: [Name & Contact]
- **Finance**: [Name & Contact untuk payment]

## Document Updates
**Last Updated**: [Date]
**Next Review**: [Date]
**Maintained By**: Zulfikar Hidayatullah


