# Firefly III Docker Troubleshooting Guide

## Quick Diagnostics

### 1. Container Status Check

```powershell
# Check all containers are running
docker-compose -f docker-compose.local.yml ps

# Check container logs for errors
docker-compose -f docker-compose.local.yml logs firefly_iii_core
docker-compose -f docker-compose.local.yml logs supabase_db_pmoves-firefly-iii
```

### 2. Database Connection Test

```powershell
# Test database connectivity
docker exec firefly_iii_core php artisan tinker --execute="DB::connection()->getPdo();"

# Check if migrations are complete
docker exec firefly_iii_core php artisan migrate:status
```

### 3. User Authentication Test

```powershell
# Check user groups exist
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "SELECT id, title FROM user_groups;"

# Check user group memberships
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT u.email, ug.title as group_name, ur.title as role_name 
FROM users u 
JOIN group_memberships gm ON u.id = gm.user_id 
JOIN user_groups ug ON gm.user_group_id = ug.id 
JOIN user_roles ur ON gm.user_role_id = ur.id;"
```

## Common Issues & Solutions

### Issue: "User has no user group"

**Symptoms:**
- Login fails with user group error
- Dashboard shows authentication errors

**Diagnosis:**
```sql
-- Check if user exists but has no group
SELECT id, email, user_group_id FROM users WHERE user_group_id IS NULL;
```

**Solution:**
```sql
-- Create user group and assign user
INSERT INTO user_groups (id, title, created_at, updated_at) 
VALUES (1, 'user@example.com', NOW(), NOW());

-- Get owner role ID
SELECT id FROM user_roles WHERE title = 'owner';

-- Create group membership (replace [ROLE_ID] with actual ID)
INSERT INTO group_memberships (user_id, user_group_id, user_role_id, created_at, updated_at)
VALUES (1, 1, [ROLE_ID], NOW(), NOW());

-- Update user with group
UPDATE users SET user_group_id = 1 WHERE id = 1;
```

### Issue: Database Migration Failures

**Symptoms:**
- Container fails to start
- Migration errors in logs

**Diagnosis:**
```powershell
# Check migration status
docker exec firefly_iii_core php artisan migrate:status

# Check for missing columns
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
AND table_type = 'BASE TABLE' 
ORDER BY table_name;"
```

**Solution:**
```powershell
# Reset and remigrate
docker exec firefly_iii_core php artisan migrate:reset
docker exec firefly_iii_core php artisan migrate
docker exec firefly_iii_core php artisan db:seed
```

### Issue: Missing soft delete columns

**Symptoms:**
- Error: "Column 'deleted_at' doesn't exist"
- Eloquent model errors

**Solution:**
```sql
-- Add missing deleted_at columns
ALTER TABLE accounts ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transaction_journals ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
-- (See DATABASE_SETUP_GUIDE.md for complete list)
```

### Issue: PostgreSQL "order" column errors

**Symptoms:**
- Syntax errors with order column
- Transaction journal errors

**Solution:**
```sql
-- Quote the order column name
ALTER TABLE transaction_journals ADD COLUMN IF NOT EXISTS "order" INTEGER DEFAULT 0;
```

### Issue: Network connectivity problems

**Symptoms:**
- Containers can't communicate
- Database connection failures

**Diagnosis:**
```powershell
# Check network exists
docker network ls | grep supabase_network_pmoves-firefly-iii

# Check container network assignment
docker inspect firefly_iii_core | grep NetworkMode
```

**Solution:**
```powershell
# Recreate network
docker network create supabase_network_pmoves-firefly-iii

# Restart with network recreation
docker-compose -f docker-compose.local.yml down
docker-compose -f docker-compose.local.yml up -d
```

## Emergency Recovery Procedures

### Complete Environment Reset

```powershell
# Stop all containers
docker-compose -f docker-compose.local.yml down -v

# Remove all data (WARNING: This deletes all data!)
docker volume prune -f

# Remove and recreate network
docker network rm supabase_network_pmoves-firefly-iii
docker network create supabase_network_pmoves-firefly-iii

# Start fresh
docker-compose -f docker-compose.local.yml up -d

# Wait for database to be ready
Start-Sleep 30

# Run migrations
docker exec firefly_iii_core php artisan migrate
docker exec firefly_iii_core php artisan db:seed
```

### Database-Only Reset

```powershell
# Stop only database
docker-compose -f docker-compose.local.yml stop supabase_db_pmoves-firefly-iii

# Remove database volume
docker volume rm pmoves-firefly-iii_supabase_db_data

# Restart database
docker-compose -f docker-compose.local.yml up -d supabase_db_pmoves-firefly-iii

# Wait and remigrate
Start-Sleep 20
docker exec firefly_iii_core php artisan migrate
docker exec firefly_iii_core php artisan db:seed
```

## Health Check Commands

### Application Health

```powershell
# Test web interface
curl http://localhost:8080/login

# Test API endpoint
curl -H "Accept: application/json" http://localhost:8080/api/v1/about
```

### Database Health

```powershell
# Test database connection
docker exec supabase_db_pmoves-firefly-iii pg_isready -U postgres

# Check table count
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT count(*) as table_count 
FROM information_schema.tables 
WHERE table_schema = 'public';"

# Verify key tables exist
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
AND table_name IN ('users', 'accounts', 'transactions', 'user_groups');"
```

## Performance Optimization

### Container Resource Check

```powershell
# Check container resource usage
docker stats --no-stream

# Check disk usage
docker system df
```

### Database Performance

```powershell
# Check database connections
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT count(*) as active_connections 
FROM pg_stat_activity 
WHERE state = 'active';"

# Check slow queries (if needed)
docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d firefly -c "
SELECT query, calls, total_time, mean_time 
FROM pg_stat_statements 
ORDER BY total_time DESC 
LIMIT 10;"
```

## Success Indicators

Your environment is healthy when:

- ✅ All containers show "Up" status
- ✅ Web interface accessible at http://localhost:8080
- ✅ Database migrations are complete
- ✅ Users can log in without group errors
- ✅ No errors in container logs
- ✅ Database queries execute without column errors

## Emergency Contacts & Resources

- **Firefly III Documentation**: <https://docs.firefly-iii.org/>
- **Docker Compose Reference**: <https://docs.docker.com/compose/>
- **PostgreSQL Documentation**: <https://www.postgresql.org/docs/>
- **Laravel Troubleshooting**: <https://laravel.com/docs/debugging>
