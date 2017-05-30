### Changelogs ###
#### 3.17 ####
- Fix cache refreshment when modifying specific prices
#### 3.16 ####
- Avoid loosing the session when redirected to HTTPS protocole
#### 3.15 ####
- Fix a conflict with PopExit module
#### 3.14 ####
- Add hooks for advanced stock management
- Add a workaround for a bug in LiteSpeed server
#### 3.12 ####
- Fix permissions of files and directories in '/cache/pagecache' directory
#### 3.11 ####
- Update Media override to avoid the cache to be cleared to many times
#### 3.10 ####
- Fix images URL in info box
- Update cccCss prototype to latest one
#### 3.09 ####
- Better check parameters for security reasons (avoid XSS)
#### 3.08 ####
- Remove module's parameters in production mode to avoid them to be referenced in search engines
#### 3.07 ####
- Improvement: do not insert DIV around dynamic modules if current page will not be cached
- Fix: native customization is not a module and therefore cannot be refreshed. The workaround is to disable cache for these products.
#### 3.06 ####
- Merge footer and header hook for better compatibility
- Fix an SQL problem in hookActionFeature*
#### 3.05 ####
- Fix problem with restricted countries
- Fix the issue that was allowing the display of infos box even in production mode
#### 3.04 ####
- Refresh cache when CSS or JS have been changed
#### 3.03 ####
- Now PageCache will not fail on PHP command line call
#### 3.02 ####
- Improve CSS to avoid style problems
- Add workaround in case of bad datas in cart rules
#### 3.01 ####
- Fix CSS styles for Prestashop 1.5
- Fix cache refreshment for single language stores
#### 3.00 ####
- Improve infos box
- Redesign of configuration page
- Step by step configuration feature
#### 2.71 ####
- Fix specific price detection (problem of seconds)
#### 2.70 ####
- Fix country detection (when detected with browser settings)
#### 2.69 ####
- Handle countries with multiple names (Array)
#### 2.68 ####
- Handle modules that have controller named like standard controllers
#### 2.67 ####
- Fix issue with some overrides with version >= 1.6.1.0
#### 2.66 ####
- Improve dynamic modules feature to avoid CSS issues
- Fix groups detection with Advanced Top Menu module
#### 2.65 ####
- Fix default group detection
#### 2.64 ####
- Fix SQL query in hookActionProductAttributeUpdate()
- Fix the check of overrides activation
#### 2.63 ####
- Add ';' to fix javascript
- Prepare for Prestashop v1.6.1
#### 2.62 ####
- Fix ProductController hook call
#### 2.61 ####
- Update Hook override
- Fix javascript error
#### 2.60 ####
- Fix 'logged out' after SSL redirection
- Fix CSS in back office
#### 2.59 ####
- Fix maintenance detection
#### 2.58 ####
- Drop useless column modules
#### 2.57 ####
- Fix javascript order.
#### 2.56 ####
- Fix carriage return in front controllers overrides
#### 2.55 ####
- Fix warning with 1.6.0.1
#### 2.54 ####
- Fix Workaround for a bug in Prestashop (multiple same JS script when CCC is activated)
- Compatibility with 1.6.0.1
- Fix a problem with 1.6.0.14
- Cast variable for addons validation
#### 2.53 ####
- Check for Express Cache installation
- Improve speed and robustness of dynamic modules display
- Fix URL with '|' character
#### 2.52 ####
- Fix clear cache for multistore
#### 2.51 ####
- Add Readme.md, move js, css and img to views directory
- Avoid bug PSCSX-4773
- Check bug PSCSX-4794
#### 2.50 ####
- Improve mobile version
- Fix products comparison
- Fix missing modules when clearing cache
#### 2.49 ####
- Fix PSCSX-4507 (clear cache of null product id)
#### 2.48 ####
- Remove useless StoresController override
- Avoid "Invalid argument supplied for foreach()" warning
- It's now possible to delete specific pages with CRON URL (controller=comma separated list of ID)
- Now handle country when it's based on users's address (delivery or invoice)
- Fix a bug when geolocalisation is enabled
#### 2.47 ####
- Handle maintenance mode
- Fix problem with blockwishlist (and more generally with Media::addJsDef feature)
#### 2.46 ####
- Fix recursive problem in Hook
- Improve forwarding of dbgpagecache parameter
#### 2.45 ####
- Handle cookie encryption
- Execute javascript even if there is no dynamic modules (to refresh cart)
#### 2.44 ####
- New functionnality to let dynamic modules bloc empty in cached pages
#### 2.43 ####
- Add option to disable cache for logged in users
#### 2.42 ####
- Handle cookies set by dynamic modules
- Fix jquery-cooki(e)-plugins insertion
#### 2.41 ####
- Fix CRC32 column value
#### 2.40 ####
- Workaround for PSCFV-10168 (before 1.5.6.0)
- Fix jquery-cookie insertion
#### 2.39 ####
- Reduce database size by using CRC32 instead of MD5 hashes
- Reduce database size by storing modules's id instead of modules's name
- Reduce backlinks count
- Forward dbgpagecache parameter so testing is easier, no need to add it on every page
- Controls known compatibility issues
- Improve infos block: real cache type is displayed (no cache, server cache or browser cache)
- Move CSS and JS into pagecache.css and pagecache.js
#### 2.38 ####
- Fix a bug with advanced configuration (javascript executed after dynamic modules)
- Fix a bug with Prestashop <= 1.6.0.7 (PS_JS_DEFER)
#### 2.37 ####
- Fix a bug with multi-store
- German translation
#### 2.36 ####
- Fix Prestashop addons valitator issues
- Optimize isLogged() override
- Handle access denied pages for dynamic modules
- Do not block store if module is not well uninstalled
- Improve uninstallation (removeOverride)
#### 2.35 ####
- Prevent AJAX requests to be cached
#### 2.34 ####
- Fix delpagecache feature
- Fix product update refreshment (on price)
#### 2.33 ####
- Make browser cache private
#### 2.32 ####
- Replace intval() by cast
- Fix browser cache min value
#### 2.31 ####
- Add a delpagecache parameter to force the cache be reffreshed
- Add link to documentation
- A workaround in case overrides have not been well uninstalled
#### 2.30 ####
- Fix country detection
#### 2.29 ####
- Handle enabled modules per device 
#### 2.28 ####
- Fix cookie problem when changing language (found with ajaxfilter module)
#### 2.27 ####
- Workaround for a bug in Prestashop (multiple same JS script when CCC is activated)
#### 2.26 ####
- Now compatible with Duplicate URL Redirect
#### 2.25 ####
- Fix for $context->link not being initialized
#### 2.24 ####
- Handle specific price changes (for flash sales modules)
#### 2.23 ####
- Add 1.6.0.5 compatibility
#### 2.22 ####
- Fix a problem with Mailalerts
#### 2.21 ####
- Fix a problem with BlockWishlist
- Avoid javascript error if the user click before the cart is refreshed.
#### 2.20 ####
- Use of Module::isEnabled('pagecache')
#### 2.19 ####
- Handle specific prices feature
- Handle module restrictions on groups
- Fix problem with URL fragments
#### 2.18 ####
- Fix for user groups dependency
#### 2.17 ####
- Replace _GET and _POST by Tools::getValue()
- Fix a bug that will improve cache statistics
#### 2.16 ####
- Add CRON URL
#### 2.15 ####
- Add user groups dependency
#### 2.14 ####
- Add compatibility with ClearURL module
- Add compatibility with 1.4 themes
#### 2.13 ####
- Fix auto detect language feature in cookie
#### 2.12 ####
- Multiple shop compatibility
#### 2.11 ####
- Ignore ad tracking parameters to be more efficient
- Add log levels to debug faster
- Add link to addons forum
#### 2.10 ####
- Add possibility to add javascript to be called after dynamic module have been displayed (to solve specific theme issues)
#### 2.9 ####
- Disable pagecache footer in debug mode and when it's not needed
#### 2.8 ####
- Improve cache refreshment speed
#### 2.7 ####
- Add browser cache feature
- Add possibility to disable statistics and save some more milliseconds
- Check product quantity to know how we should refresh the cache on order validation
- Fix a bug with _PS_BASE_URL_ which is not defined in some cases
#### 2.6 ####
- SQL optimisations
#### 2.5 ####
- Fix random number at bottom of cached pages
#### 2.4 ####
- Fix HTTPs issue
- Fix mobile version
#### 2.3 ####
- Add a debug mode to be able to test on production site
- Add logs possibilities
- Fix currency selection
#### 2.2 ####
- Fix a "white page" issue when module is disabled
#### 2.1 ####
- Fix logout feature
#### 2.0 ####
- Hit statistics to know how the cache is efficient
- Improve cache management by deleting cache files only when necessary (you can modify how cache is refreshed)
- Now compatible with CloudCache (do not override same methods anymore)
- Save cache files in multiple directories to avoid filesystems to slow down
- Enable auto-update so you can update your module from your backoffice
- New logo
- Fix an issue with prices-drop, new-products and best-sales controllers
- Fix an issue with default empty cookie name which is now a random name and expires immediately
#### 1.7 ####
- Add default configuration for dynamic modules and a button to go back to this default configuration
#### 1.6 ####
- Hook class updated to merge with version 1.5.4.1
- Disable cache and display an error if tokens are enabled
#### 1.5 ####
- Keep 'id_lang' cookie to be compatible with some modules

