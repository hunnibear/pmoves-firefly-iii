# Gemini Work Log

## Couples Budget Planner Integration into Firefly III

This log details the steps taken to integrate a "Couples Budget Planner" (originally `app.html`) into the Firefly III personal finance management application.

### Phase 1: Initial Setup and Data Loading

*   **Cloned Firefly III Repository:** The Firefly III open-source project was cloned into the local environment.
*   **New Web Route (`/couples`):** A new web route was added to Firefly III's `routes/web.php` to serve the Couples Budget Planner.
*   **CouplesController Creation:** A new Laravel controller (`FireflyIII\Http\Controllers\CouplesController`) was created to handle requests for the planner page.
*   **Twig View Integration:** The HTML, CSS, and JavaScript from the original `app.html` were integrated into a new Twig template (`resources/views/couples/index.twig`), extending Firefly III's default layout. Twig's `{% raw %}` and `{% endraw %}` tags were used to prevent conflicts with JavaScript.
*   **Navigation Link:** A new link to the Couples Budget Planner was added to Firefly III's main sidebar navigation (`resources/views/partials/menu-sidebar.twig`).
*   **API Endpoint for State (`GET /api/v1/couples/state`):** A new API endpoint was created in `routes/api.php` to provide the initial state data for the planner.
*   **API Controller for State:** The `FireflyIII\Api\V1\Controllers\Couples\CouplesController` was implemented to fetch data from the Firefly III database, including:
    *   Authenticated user's name and calculated income for the current month (from revenue accounts).
    *   Expenses categorized by tags (`couple-p1`, `couple-p2`, `couple-shared`) for personal and shared expenses.
    *   Unassigned expenses (transactions without couple-related tags).
    *   Goals (mapped from Firefly III's "Piggy Banks").
*   **Frontend State Loading:** The frontend JavaScript was refactored to fetch its initial state from this new API endpoint, replacing the original `localStorage` persistence.

### Phase 2: Core CRUD Operations and UI Interactivity

*   **Transaction Creation (`POST /api/v1/couples/transactions`):**
    *   A new API endpoint was added to handle the creation of new transactions.
    *   The `storeTransaction` method in `CouplesController` was implemented to create `TransactionJournal` and `Transaction` records, assigning appropriate `couple-` tags based on the input column.
    *   The frontend's `handleFormSubmit` function was refactored to call this API endpoint.
*   **Transaction Update (`PUT /api/v1/couples/transactions/{transaction}`):**
    *   A new API endpoint was added for updating existing transactions.
    *   The `updateTransaction` method in `CouplesController` was implemented to modify `TransactionJournal` and `Transaction` details.
    *   The frontend's `handleListInteraction` function was refactored to call this API endpoint when transaction details are edited.
*   **Transaction Deletion (`DELETE /api/v1/couples/transactions/{transaction}`):**
    *   A new API endpoint was added for deleting transactions.
    *   The `deleteTransaction` method in `CouplesController` was implemented to soft-delete `TransactionJournal` records.
    *   The frontend's `handleListInteraction` function was refactored to call this API endpoint when the delete button is clicked.
*   **Transaction Tag Update (Drag-and-Drop) (`PUT /api/v1/couples/transactions/{transaction}/tag`):**
    *   A new API endpoint was added to update the tags of a transaction.
    *   The `updateTransactionTag` method in `CouplesController` was implemented to remove existing `couple-` tags and attach a new one based on the target column.
    *   The frontend's `handleDrop` function was refactored to call this API endpoint for drag-and-drop operations.
*   **Goal Creation (`POST /api/v1/couples/goals`):**
    *   A new API endpoint was added for creating new goals (piggy banks).
    *   The `storeGoal` method in `CouplesController` was implemented to create `PiggyBank` records.
    *   The frontend's `addGoal` function was refactored to call this API endpoint.
*   **Goal Deletion (`DELETE /api/v1/couples/goals/{goal}`):**
    *   A new API endpoint was added for deleting goals.
    *   The `deleteGoal` method in `CouplesController` was implemented to delete `PiggyBank` records.
    *   The frontend's `removeGoal` function was refactored to call this API endpoint.
*   **Tailwind CSS Integration:**
    *   Tailwind CSS dependencies were added to `resources/assets/v2/package.json`.
    *   `tailwind.config.js` and `postcss.config.js` were created in `resources/assets/v2`.
    *   Tailwind directives were added to `resources/assets/v2/src/sass/app.scss`.

### Phase 3: Person 2 Configuration

*   **Partner Selection UI:** Added a dropdown to the "Settings" tab of the Couples Budget Planner to allow selecting a partner user.
*   **API for User Listing (`GET /api/v1/couples/users`):** Implemented an API endpoint to fetch users within the authenticated user's `UserGroup` for partner selection.
*   **API for Partner Preference Saving (`POST /api/v1/couples/partner`):** Implemented an API endpoint to save the selected partner's user ID as a user preference.
*   **Dynamic Partner Data Fetching:** Modified the `CouplesController@state` method to fetch and include the partner's name, income, and transactions (tagged `couple-p2`) in the state response if a partner is selected.
*   **Frontend Partner Selection:** Updated the frontend JavaScript to populate the partner dropdown dynamically and pre-select the saved partner.

### Phase 5: Comprehensive Docker Documentation

*   **Complete Setup Documentation:** Created a comprehensive `README.md` file detailing the full Docker setup process with local Supabase integration, including:
    *   **Prerequisites and System Requirements:** Detailed installation requirements for Docker, Node.js, and Supabase CLI across Windows, macOS, and Linux.
    *   **Step-by-Step Setup Guide:** Complete installation process from repository cloning through application access.
    *   **Environment Configuration:** Detailed `.env` file configuration with security considerations and key generation.
    *   **Supabase Integration:** Proper initialization and startup procedures for local Supabase stack.
    *   **Architecture Overview:** Visual representation of the containerized system with component relationships.

*   **Troubleshooting and Maintenance:**
    *   **Common Issues Guide:** Solutions for database connection failures, port conflicts, container startup issues, and cron service problems.
    *   **Useful Commands Reference:** Container management, Supabase operations, and database access commands.
    *   **Data Management:** Comprehensive backup and restore procedures for both database and file uploads.
    *   **Production Deployment:** Security hardening, performance optimization, and HTTPS configuration guidance.

*   **Feature Documentation:**
    *   **API Endpoint Reference:** Complete documentation of all custom couples budget planner endpoints.
    *   **Database Schema Integration:** Explanation of custom tags, Firefly III integration points, and data relationships.
    *   **Development Setup:** Local development configuration with hot reload and debugging capabilities.

*   **Validation and Testing:**
    *   **Docker Compose Configuration:** Verified current `docker-compose.yml` is properly configured for external Supabase connection.
    *   **Environment Variable Validation:** Confirmed `.env` file structure matches Supabase connection requirements.
    *   **Security Token Configuration:** Documented proper `STATIC_CRON_TOKEN` and `APP_KEY` generation procedures.
    *   **Supabase CLI Update:** Updated Supabase CLI from v2.33.9 to v2.34.3 using the recommended npm dev dependency method (`npm i supabase --save-dev`), and updated all documentation to use `npx supabase` commands instead of global installation.
        DB_CONNECTION=pgsql
        DB_HOST=localhost
        DB_PORT=54322
        DB_DATABASE=postgres
        DB_USERNAME=postgres
        DB_PASSWORD=postgres
        ```
### Phase 6: Successful Deployment and Application Launch

*   **Environment Setup Completed:**
    *   **Supabase Instance:** Successfully started local Supabase stack on standard ports (54321-54324)
    *   **Docker Containers:** Successfully launched Firefly III application and cron service containers
    *   **Database Connection:** Firefly III connecting to Supabase PostgreSQL database on localhost:54322
    *   **Application Status:** Both firefly_iii_core and firefly_iii_cron containers running and healthy

*   **Application Access Points:**
    *   **Main Firefly III Application:** Available at http://localhost
    *   **Couples Budget Planner:** Integrated at http://localhost/couples
    *   **Supabase Studio:** Management interface at http://localhost:54323
    *   **API Endpoints:** Complete REST API for couples budget planner functionality

*   **Integration Verification:**
    *   **Routes Confirmed:** Both web routes (/couples) and API routes (/api/v1/couples/*) are properly configured
    *   **Controllers Present:** Main CouplesController and API CouplesController exist in the firefly-iii directory
    *   **Views Available:** Twig template at firefly-iii/resources/views/couples/index.twig ready for rendering
    *   **Database Schema:** Custom couple-* tags system integrated with Firefly III's existing transaction structure

*   **Deployment Architecture:**
    *   **External Database:** Firefly III connects to externally managed Supabase PostgreSQL instance
    *   **Containerized Application:** Main application runs in Docker container with persistent volume storage
    *   **Automated Tasks:** Cron service container handles scheduled maintenance and data processing
    *   **Network Configuration:** All services communicate through dedicated Docker bridge network

### Phase 7: V2 Dashboard Fix

*   **Problem:** The main dashboard was consistently rendering the old `v1` Twig-based layout, even when the `.env` file was configured to use the `v2` layout.
*   **Root Cause Analysis:** Investigation of `routes/web.php` and `app/Http/Controllers/HomeController.php` revealed that the `HomeController@index` method, while correctly identifying the `v2` configuration, was calling `view('index')`. This caused Laravel to render the default `resources/views/index.twig` instead of the correct `resources/views/v2/index.blade.php`.
*   **Solution:** The `HomeController.php` was modified to explicitly point to the correct v2 view. The return statement in the `indexV2` method was changed from `return view('index', ...)` to `return view('v2.index', ...)`.
*   **Verification:** This change ensures that when `FIREFLY_III_LAYOUT=v2` is set, the application correctly serves the new React-based dashboard, resolving the layout issue and enabling the transition to Phase 2 development.
