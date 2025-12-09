# Changelog

All notable changes to `laravel-qol` will be documented in this file.

## [1.1.0]

### 1. **CommonScopes Trait** (`src/Traits/CommonScopes.php`)
- 20+ reusable query scopes for Eloquent models
- Time-based scopes: `today()`, `thisWeek()`, `thisMonth()`, `thisYear()`, `recent()`, `older()`
- Status scopes: `active()`, `inactive()`, `published()`, `draft()`, `featured()`
- Filtering scopes: `whereIds()`, `whereNotIds()`, `whereLike()`, `whereEmpty()`, `whereNotEmpty()`
- Utility scopes: `random()`, `popular()`, `smartPaginate()`

### 2. **ApiResponse Trait** (`src/Traits/ApiResponse.php`)
- Standardized JSON responses for API controllers
- Methods: `success()`, `error()`, `created()`, `updated()`, `deleted()`
- Error responses: `notFound()`, `unauthorized()`, `forbidden()`, `validationError()`, `serverError()`
- Special responses: `paginated()`, `noContent()`

### 3. **Request Macros** (`src/Support/RequestMacros.php`)
- Enhanced Request methods via Laravel macros
- `hasAny()`, `hasAll()` - Check multiple keys
- `boolean()` - Properly parse boolean values from strings
- `ids()` - Parse comma-separated or array IDs
- `search()` - Sanitized search terms
- `realIp()` - Get real IP (proxy-aware)
- `isMobile()` - Detect mobile devices
- `sort()` - Parse sort parameters
- `filters()` - Get non-empty filters

### 4. **Collection Macros** (`src/Support/CollectionMacros.php`)
- Extended Collection functionality via Laravel macros
- `recursive()` - Recursively convert to arrays
- `groupByMultiple()` - Group by multiple keys
- `toCsv()` - Export to CSV string
- `hasDuplicates()` - Check for duplicate values
- `transpose()` - Transpose arrays
- `percentage()` - Calculate percentage distribution
- `stats()` - Get statistical analysis (count, sum, avg, min, max, median)
- `filterNull()`, `filterEmpty()` - Filter null/empty values
- `paginate()` - Manual pagination

### 5. **Validation Rules** (`src/Rules/`)
- **PhoneNumber** - Validate phone numbers with customizable pattern
- **StrongPassword** - Enforce strong password requirements
  - Configurable min length
  - Optional uppercase, lowercase, numbers, special chars
- **Username** - Validate usernames with rules
  - Length constraints
  - Allowed characters (dash, underscore, dot)
  - Must start with letter

### 6. **Helper Functions** (`src/Support/helpers.php`)
- 14 global utility functions
- String helpers: `str_limit_words()`, `truncate_middle()`, `sanitize_filename()`
- Number helpers: `money_format_simple()`, `percentage()`, `bytes_to_human()`, `human_to_bytes()`
- Array helpers: `array_filter_recursive()`
- Security: `generate_random_string()`
- Validation: `is_json()`
- Date: `carbon_parse_safe()`
- Browser: `get_client_browser()`
- Navigation: `active_route()`

### 7. **QueryLogger Utility** (`src/Utilities/QueryLogger.php`)
- Database query logging and analysis
- Track all executed queries with bindings and execution time
- Methods: `enable()`, `disable()`, `getQueries()`, `getTotalTime()`, `getCount()`
- Analysis: `getSlowestQueries()`, `logToFile()`, `dump()`
- Perfect for debugging N+1 queries and performance issues

### 8. **ModelUtility** (`src/Utilities/ModelUtility.php`)
- Model introspection and manipulation utilities
- Schema inspection: `getTableColumns()`, `getFillableColumns()`, `getHiddenColumns()`
- State inspection: `getDirtyAttributes()`, `getChangedAttributes()`, `getOriginalAttributes()`
- Relation helpers: `getLoadedRelations()`, `isRelationLoaded()`, `hasRelation()`
- Utilities: `cloneModel()`, `diff()`, `exists()`, `wasRecentlyCreated()`

## Integration

All utilities are automatically registered via `LaravelQolServiceProvider`:
- Request & Collection macros are registered on boot
- Helper functions are autoloaded via composer.json
- All classes use PSR-4 autoloading

## Total Count

**New Files Created: 11**
- 2 Traits (CommonScopes, ApiResponse)
- 3 Validation Rules (PhoneNumber, StrongPassword, Username)
- 3 Support Classes (RequestMacros, CollectionMacros, helpers.php)
- 2 Utilities (QueryLogger, ModelUtility)
- 1 Updated: LaravelQolServiceProvider

**New Functionality:**
- 20+ Query Scopes
- 10+ API Response Methods
- 9 Request Macros
- 11 Collection Macros
- 3 Validation Rules
- 14 Helper Functions
- 8 QueryLogger Methods
- 17 ModelUtility Methods

## [1.0.1]

### Added
- Initial release
- **Media Management**
  - HasImages trait for image management
  - HasFiles trait for generic file handling
  - HasVideos trait for video uploads with metadata
  - PhotoImage model with helper methods
  - File model with type detection and download support
  - Video model with duration, resolution, and quality checks
- **Model Enhancement Traits**
  - HasHistory trait for automatic change tracking
  - HasRoles trait for complete authorization system
  - HasIntegrations trait for managing user integrations
  - HasUuid trait for automatic UUID generation
  - HasSlug trait for automatic slug generation
  - HasStatus trait for status management
  - Sortable trait for ordering functionality
  - Cacheable trait for model-level caching
  - Searchable trait for simple and full-text search
- **Authorization System**
  - Role model with hierarchical levels
  - Permission model with groups
  - Complete role and permission management
  - Route middleware (role, permission, role_or_permission)
  - Blade directives for template authorization
  - Automatic Laravel Gate registration
  - Super admin with wildcard permissions
- **User Integrations System**
  - UserIntegration model for managing third-party integrations
  - HasIntegrations trait for User model
  - Automatic encryption of sensitive credentials
  - Support for OAuth (access/refresh tokens)
  - Support for API keys and secrets
  - Integration metadata and status management
  - Token expiration tracking
  - Multiple integration types (oauth, api_key, webhook, custom)
- **Production-Ready Middleware**
  - SetLocale: Automatic localization from headers/session/user
  - RestrictAccess: Maintenance mode with IP/role restrictions
  - ForceJsonResponse: Force JSON for API applications
  - LogRequestResponse: Request/response logging with sanitization
  - SecurityHeaders: Automatic security headers (HSTS, CSP, etc.)
  - RateLimitByUser: User/IP-based rate limiting
  - ConvertEmptyStringsToNull: Clean request data
  - TrimStrings: Auto-trim inputs with exceptions
  - ApiVersioning: Multi-version API support
  - CorsMiddleware: Advanced CORS configuration
- **Universal Settings System**
  - App-wide settings via Settings facade
  - Per-user settings with HasSettings trait
  - Per-model settings for any Eloquent model
  - Support for all data types (string, int, float, bool, array, json)
  - Group organization for logical separation
  - Public/private visibility control
  - Automatic caching with configurable TTL
  - Increment/decrement numeric values
  - Toggle boolean values
  - Metadata support for additional context
  - Batch set/get/clear operations
- **Database**
  - Migrations for all tables (model_histories, photo_images, files, videos, roles, permissions, user_integrations, pivot tables)
  - Example seeder for roles and permissions
- **Configuration**
  - Customizable settings for all features
  - Environment-based configuration support
- **Documentation**
  - Comprehensive README with usage examples
  - Configuration examples
  - Best practices guide
