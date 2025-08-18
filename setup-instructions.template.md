# Firefly III Setup Instructions (Template)

## Environment Setup

1. Copy .env.example to .env and configure:
   - Database connection settings
   - APP_KEY (generate with: php artisan key:generate)
   - STATIC_CRON_TOKEN (generate random 32-character string)

2. Set up AI API keys (obtain from respective providers):
   - OPENAI_API_KEY=your_openai_api_key_here
   - GROQ_API_KEY=your_groq_api_key_here
   - ANTHROPIC_API_KEY=your_anthropic_api_key_here

3. Configure database connection:
   - For Supabase: DB_HOST=localhost, DB_PORT=54322
   - For production: Use your production database settings

4. Run migrations:
   `
   php artisan migrate
   php artisan db:seed
   `

## Security Notes
- Never commit actual API keys to version control
- Use different keys for development and production
- Regularly rotate API keys for security
