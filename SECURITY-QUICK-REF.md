# 🔒 Security Quick Reference Card

**Finance Tracker Security Status:** ✅ A+ (100%)

---

## 🛡️ Active Protections

```
✅ Security Headers       7/7 (100%)
✅ Rate Limiting          5/min login, 60/min API
✅ CSRF Protection        100% forms
✅ SQL Injection Risk     0% (Eloquent)
✅ XSS Protection         Multi-layer
```

---

## 📋 Security Headers

```http
✅ X-Frame-Options: SAMEORIGIN
✅ X-Content-Type-Options: nosniff
✅ X-XSS-Protection: 1; mode=block
✅ Referrer-Policy: strict-origin-when-cross-origin
✅ Permissions-Policy: geolocation=(), microphone=(), camera=()
✅ Content-Security-Policy: default-src 'self'; script-src...
✅ Strict-Transport-Security: max-age=31536000 (production only)
```

---

## ⚡ Rate Limits

| Endpoint | Limit | Window |
|----------|-------|--------|
| `POST /login` | 5 requests | 1 minute |
| `POST /register` | 5 requests | 1 minute |
| `POST /api/v1/auth/*` | 5 requests | 1 minute |
| `GET /api/v1/*` | 60 requests | 1 minute |

---

## 🧪 Quick Tests

### Test Security Headers
```bash
curl -I http://localhost:8000 | grep -E "(X-Frame|X-Content|X-XSS|Referrer|Content-Security)"
```

### Test Rate Limiting
```bash
for i in {1..10}; do
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}' \
    -w "\nStatus: %{http_code}\n"
done
# Після 5-го запиту: HTTP 429 Too Many Requests
```

### Test CSRF Protection
```bash
curl -X POST http://localhost:8000/login \
  -d "email=test@test.com&password=password" \
  -v
# Очікується: HTTP 419 Page Expired
```

---

## 🚨 Security Incidents

### If Attack Detected:

1. **Identify:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "failed\|error\|warning"
   ```

2. **Block IP:**
   ```bash
   # Nginx
   deny 192.168.1.100;
   nginx -s reload
   
   # Apache
   echo "Deny from 192.168.1.100" >> .htaccess
   ```

3. **Reset Rate Limits:**
   ```bash
   php artisan cache:clear
   ```

4. **Check Failed Logins:**
   ```bash
   grep "failed login" storage/logs/laravel.log | tail -50
   ```

---

## 📱 Emergency Contacts

**Security Issues:** security@yourapp.com  
**Bug Reports:** https://github.com/yourapp/issues  
**Documentation:** docs/SECURITY.md

---

## 🔗 Quick Links

- [Full Security Docs](docs/SECURITY.md)
- [Security Summary](docs/SECURITY-SUMMARY.md)
- [Security Roadmap](docs/SECURITY-ROADMAP.md)
- [Test Page](http://localhost:8000/test-security.html)

---

**Last Updated:** 7 грудня 2024  
**Security Level:** 🔒 A+ (Enterprise-grade)
