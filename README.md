rzx-me (remake)

This repository contains a modernized remake of the original rzx.me site.

Notes:
- Public static assets are served from `public/assets/`.
- Source CSS (development) previously lived in `app/css/` (formerly `includes/css/`). During this update those files were copied into `public/assets/css/`.

Recommended workflow:
1. Edit CSS in `app/css/` (or `public/assets/css/`) during development, then run the publish script to copy to `public/assets/css/` before committing (or edit `public/assets/css/` directly if you prefer).
2. Use `git status` to verify staged changes before commit.

If you need me to remove the backup zip from the repo or to revert the staged deletions, tell me which option to perform.

## Version 0.8.0 - Complete Architecture Modernization

### 🚀 Major Structural Improvements

**Front Controller Pattern Implementation:**
- **Unified Entry Point**: Complete migration to `public/index.php` as the single front controller
- **View Template System**: All page views moved to `app/views/` with consistent naming (`*-body.php`)
- **Page Data Handler**: Centralized page metadata management via `app/Handlers/page_data_handler.php`
- **Dynamic Routing**: Smart route matching with fallback for legacy URL patterns

**Asset & Resource Organization:**
- **Favicon Migration**: Moved `favicon.ico` to `/assets/images/` with proper HTML link references
- **SEO Enhancement**: Unified meta tags (copyright, keywords, description, author) in index.php
- **Responsive Design**: Maintained viewport meta tag for mobile compatibility
- **CSS/JS Optimization**: Standardized asset loading and path references

### ⚡ Technical Modernization

**Pure JavaScript Implementation (Sketch Gallery):**
- **jQuery Elimination**: Complete rewrite of sketch page animations using vanilla JavaScript
- **Custom Animation Engine**: `animateElement()` function with requestAnimationFrame and easeOutQuart easing
- **Feature Parity**: All original animations preserved (thumbnail spread, hover effects, navigation)
- **Performance Enhancement**: Reduced dependencies and improved load times
- **Cycling Navigation**: Added infinite loop support for prev/next buttons within albums
- **Thumbnail Collection**: Proper state management for album view transitions

**Maintained Compatibility:**
- **jQuery Test Version**: `ray-sketch-test-body.php` preserved for comparison and fallback
- **Backup Files**: All original files backed up as `*-copy.php` in public directory
- **Legacy Support**: Existing URLs continue to work through route mapping

### 📁 Architecture Benefits

**Development Experience:**
- **Modular Structure**: Clean separation of concerns (views, handlers, assets)
- **Maintainable Codebase**: Consistent file organization and naming conventions
- **Error Handling**: Graceful exception handling with meaningful error messages
- **Debug Friendly**: Clear file paths and logical code organization

**Performance & SEO:**
- **Single Entry Point**: Reduced server overhead and improved caching
- **Meta Tag Optimization**: Comprehensive SEO metadata management
- **Asset Efficiency**: Optimized resource loading and minimal dependencies
- **Mobile Optimized**: Full responsive design support maintained

**Security & Reliability:**
- **Input Sanitization**: Proper HTML escaping and security practices
- **Route Validation**: Secure path handling and file existence checks
- **Exception Handling**: Robust error management without exposing internals
- **Asset Security**: Proper asset path validation and serving

### 🎯 Migration Summary

**Eliminated Dependencies:**
- Removed jQuery from sketch page (32KB+ savings)
- Eliminated multiple entry point files (ray-*.php)
- Consolidated duplicate CSS/JS loading logic

**Enhanced Features:**
- Infinite album navigation cycling
- Improved animation performance and smoothness
- Better mobile device compatibility
- Centralized configuration management

**Backward Compatibility:**
- All existing URLs continue to function
- Original visual design and functionality preserved
- Legacy files maintained as backups
- Gradual migration path for future updates

This represents the most significant architectural improvement in the site's history, establishing a solid foundation for future development while maintaining full feature compatibility and improving performance across all devices.
