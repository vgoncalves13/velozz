# ✅ Production Deployment Checklist

Use this checklist before and after deploying to production.

## 🔧 Pre-Deployment

### Server Setup
- [ ] VPS provisioned (4GB+ RAM recommended)
- [ ] Ubuntu 22.04 LTS installed
- [ ] Docker installed and running
- [ ] Docker Compose installed
- [ ] Git installed
- [ ] Certbot installed for SSL

### DNS Configuration
- [ ] Domain `velozz.digital` pointed to server IP
- [ ] Wildcard DNS `*.velozz.digital` configured
- [ ] `app.velozz.digital` A record created
- [ ] `ws.velozz.digital` A record created
- [ ] DNS propagation verified (48h max)

### SSL Certificates
- [ ] SSL certificate generated with Let's Encrypt
- [ ] Wildcard certificate for `*.velozz.digital`
- [ ] Certificates copied to `docker/nginx/ssl/velozz.digital/`
- [ ] Auto-renewal cron job configured
- [ ] Certificate permissions set (755)

### Environment Configuration
- [ ] `.env` file created from `.env.production.example`
- [ ] `APP_KEY` generated
- [ ] `APP_ENV=production` set
- [ ] `APP_DEBUG=false` set
- [ ] Strong `DB_PASSWORD` configured
- [ ] Strong `REDIS_PASSWORD` configured
- [ ] `REVERB_APP_KEY` and `REVERB_APP_SECRET` generated
- [ ] AWS credentials configured (SES + S3)
- [ ] Stripe production keys configured
- [ ] Z-API credentials configured
- [ ] All URLs updated to production domains

### Code Preparation
- [ ] All tests passing locally
- [ ] Production branch merged
- [ ] Version tag created
- [ ] Database migrations tested
- [ ] Seeders prepared
- [ ] No sensitive data in repository

## 🚀 Deployment

### Initial Deployment
- [ ] Repository cloned to `/var/www/velozz`
- [ ] `deploy.sh` made executable
- [ ] Docker images built successfully
- [ ] All containers started
- [ ] Database migrations run
- [ ] Admin user created
- [ ] Roles seeded
- [ ] Plans seeded (if applicable)

### Service Verification
- [ ] App container running
- [ ] Queue worker running
- [ ] Scheduler running
- [ ] Reverb (WebSocket) running
- [ ] Nginx running
- [ ] MySQL running
- [ ] Redis running

### Functionality Testing
- [ ] Main site accessible: `https://velozz.digital`
- [ ] Admin panel accessible: `https://app.velozz.digital`
- [ ] Can create test tenant
- [ ] Tenant panel accessible: `https://testtenant.velozz.digital`
- [ ] Login working
- [ ] Database queries working
- [ ] Cache working (Redis)
- [ ] Queue jobs processing
- [ ] WebSocket connection working
- [ ] File uploads working
- [ ] Email sending working (SES)

### Security
- [ ] Firewall configured (UFW)
- [ ] Only ports 22, 80, 443 open
- [ ] SSH key-based authentication enabled
- [ ] Root login disabled (optional)
- [ ] Strong passwords used
- [ ] `.env` file permissions: 600
- [ ] Storage directory permissions correct

## 📊 Post-Deployment

### Monitoring Setup
- [ ] Health check endpoint tested: `/up`
- [ ] Error logging configured
- [ ] Log rotation set up
- [ ] Monitoring tools installed (optional)
- [ ] Backup strategy implemented

### Performance
- [ ] OPcache enabled and verified
- [ ] Redis cache working
- [ ] Asset compilation successful
- [ ] Page load times acceptable (<2s)
- [ ] Database queries optimized

### Backups
- [ ] Database backup script created
- [ ] Automated daily backups configured
- [ ] Backup restoration tested
- [ ] Offsite backup storage configured

### Documentation
- [ ] Admin credentials documented (securely)
- [ ] DNS settings documented
- [ ] Server access details documented
- [ ] Deployment procedures documented

## 🔄 Regular Maintenance

### Daily
- [ ] Check application logs
- [ ] Monitor queue processing
- [ ] Verify WebSocket connectivity

### Weekly
- [ ] Review error logs
- [ ] Check disk space
- [ ] Monitor database size
- [ ] Review failed jobs queue

### Monthly
- [ ] Update dependencies
- [ ] Security patches applied
- [ ] SSL certificate renewal check
- [ ] Database optimization
- [ ] Backup restoration test

## 🆘 Emergency Contacts

**Server Provider:** Hostinger
**Domain Registrar:** [Your Registrar]
**SSL Provider:** Let's Encrypt
**Email Provider:** Amazon SES

## 📝 Deployment Log

| Date       | Version | Deployed By | Notes |
|------------|---------|-------------|-------|
| 2026-02-27 | 1.0.0   | [Your Name] | Initial production deployment |
|            |         |             |       |

## 🎯 Success Criteria

Deployment is successful when:
- ✅ All services running without errors
- ✅ All domains accessible via HTTPS
- ✅ Admin can log in
- ✅ Tenant can be created and accessed
- ✅ Queue jobs processing
- ✅ WebSocket connections working
- ✅ No errors in logs for 24 hours
- ✅ Backups configured and tested

---

**Last Updated:** 2026-02-27
**Reviewed By:** [Your Name]
**Next Review:** [Date]
