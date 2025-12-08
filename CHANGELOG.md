# Changelog

All notable changes to `laravel-qol` will be documented in this file.

## [Unreleased]

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
