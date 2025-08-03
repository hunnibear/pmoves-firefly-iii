# Fix for Supabase Analytics/Vector on Windows

## 🔍 The Problem
Supabase Analytics (Vector) fails on Windows because:
- Vector tries to connect to Docker socket for log collection
- Windows Docker Desktop doesn't expose the socket by default
- Error: "Network unreachable (os error 101)"

## ✅ Solution 1: Configure Vector for Windows (Recommended)

Create a custom Vector configuration that doesn't require Docker socket access:

### Step 1: Create Custom Vector Config
Create `supabase/vector.yaml`:

```yaml
api:
  enabled: true
  address: "0.0.0.0:9001"

sources:
  # Remove docker_host source that causes issues on Windows
  # docker_host:
  #   type: docker_logs

  # Use internal sources instead
  http_logs:
    type: http
    address: "0.0.0.0:8080"
    path: "/logs"

transforms:
  router:
    type: route
    inputs: ["http_logs"]
    route:
      auth_logs: '.container_name == "supabase_auth"'
      rest_logs: '.container_name == "supabase_rest"'
      storage_logs: '.container_name == "supabase_storage"'
      realtime_logs: '.container_name == "supabase_realtime"'
      db_logs: '.container_name == "supabase_db"'
      kong_logs: '.container_name == "supabase_kong"'
      kong_err: '.container_name == "supabase_kong" and .level == "error"'

sinks:
  logflare_auth:
    type: http
    inputs: ["router.auth_logs"]
    uri: "http://logflare:4000/logs/supabase_auth"
    
  logflare_rest:
    type: http
    inputs: ["router.rest_logs"]
    uri: "http://logflare:4000/logs/supabase_rest"
    
  logflare_storage:
    type: http
    inputs: ["router.storage_logs"]
    uri: "http://logflare:4000/logs/supabase_storage"
    
  logflare_realtime:
    type: http
    inputs: ["router.realtime_logs"]
    uri: "http://logflare:4000/logs/supabase_realtime"
    
  logflare_db:
    type: http
    inputs: ["router.db_logs"]
    uri: "http://logflare:4000/logs/supabase_db"
    
  logflare_kong:
    type: http
    inputs: ["router.kong_logs"]
    uri: "http://logflare:4000/logs/supabase_kong"
```

## ✅ Solution 2: Enable Docker TCP (Full Analytics)

**Warning: This exposes Docker daemon - only for development!**

### Step 1: Enable Docker TCP in Docker Desktop
1. Open Docker Desktop
2. Go to Settings → General
3. Check "Expose daemon on tcp://localhost:2375 without TLS"
4. Click "Apply & Restart"

### Step 2: Restart Supabase
```bash
supabase start
```

## ✅ Solution 3: Disable Analytics (Simple)

If you don't need analytics, disable it in `supabase/config.toml`:

```toml
[analytics]
enabled = false
```

## 📊 What You Get With Analytics

When working properly, Supabase Analytics provides:
- **Real-time Logs**: All service logs in one dashboard
- **Performance Metrics**: Request rates, response times, errors
- **Resource Usage**: CPU, memory, database performance
- **Custom Dashboards**: Create your own analytics views
- **Log Search**: Search across all services
- **Alerting**: Set up alerts for issues

## 🚀 Recommended Next Steps

1. **Try Solution 3 first** (disable analytics) to get everything working
2. **Then try Solution 2** (enable Docker TCP) if you want full analytics
3. **Use Solution 1** (custom config) for a middle ground

The core Supabase functionality (database, auth, API, storage) works perfectly without analytics!
