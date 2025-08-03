# Quick Fix for Supabase Analytics on Windows

## 🎯 Enable Docker TCP Access

To get Supabase Analytics/Vector working on Windows:

1. **Open Docker Desktop**
2. **Go to Settings** (gear icon in top right)
3. **Click "General" tab**
4. **Check the box**: "Expose daemon on tcp://localhost:2375 without TLS"
5. **Click "Apply & Restart"**
6. **Wait for Docker to restart**

## ✅ Then restart Supabase:
```bash
supabase start
```

## ⚠️ Security Note
This is safe for local development but don't do this on production servers!

## 🔄 Alternative: Disable Analytics Temporarily
If you prefer to keep Docker secure for now, edit `supabase/config.toml`:

```toml
[analytics]
enabled = false
```

Then restart with `supabase start`.
