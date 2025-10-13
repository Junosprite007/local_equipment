# Prompt File

I'm working on a plug-in for Moodle version 5.0 called "Equipment". Note that in my local workspace, 'flip.funlearningco.com' is
equivalent to 'moodle'. Meaning, anytime I say (or you come across) 'flip.funlearningco.com/', I'm referring to a 'moodle/'
directory. My production site is at "flip.funlearningcompany.com". My "Equipment" plugin already has several capabilities at the
moment:

1. Partnership management
2. Equipment exchange management
3. Agreement management
4. Family registration and management
5. Automated and manual phone communication
6. Equipment tracking with QR Code and barcode scanning capabilities.

---

First, you'll need some background on how my company handles its inventory. My company houses its inventory in it sown warehouse and
handles its own deliveries. We do not manufacture our equipment; we instead order equipment from various companies including, but
not limited to, LEGO, Asus, KiwiCo, Piper, Ozobot, CoDrone, etc. Inside our rather small warehouse, we have isles of unlabeled
shelving (which we can label if you think that's necessary), each containing many different pieces of equipment: boxes, kits,
projects, etc. Each piece of equipment may or may not already have a Universal Product Code (UPC) or another barcode of some kind,
but that doesn't really help us for what we need. Our equipment gets sent out, exchanged, and returned to individuals and/or
schools, so I'd like to create a QR code system to track who has each individual piece of equipment at any given time.

For example, we could have two LEGO kits that have the same UPC number, but each one will be going to two different people this
year, returned after the school year, and go to two other people next year. As another example, a student could pickup 6 KiwiCo
Tinker Crates for our "Tinkering" course, but they don't necessarily need to return those as they are consumable. We only reuse and
exchange non-consumable kits from year to year. In addition to our main inventory system, we also have many other sub features that
work together to aid in the authorization of our inventory system. The virtual course consent form may not seem related to inventory
at first, but we use that to provide the functionality for a parent to consent to their children being able to borrow and use our
equipment throughout the school year.

---

I need you to finish executing the **REVISED DETAILED IMPLEMENTATION PLAN** that is shown below. As you'll see in this
implementation plan file, _you_ have actually already completed several parts of several Phases. We will eventually finish all of
them.

-   Using what is already shown as complete on the implementation guide, examine all necessary files to ensure that everything is
    correctly documented as complete or not complete.
-   If you do not see any ❌ OR ✅ symbol before a given line within a Phase, you SHOULD assume that part is incomplete.
-   When helping me fix these issues, use relevant Moodle APIs wherever possible. In addition, you MUST strictly adhere to Moodle
    5.0 coding standards, as well as Bootstrap 5, PHP 8.4, ES6, and Mustache templating standards.
-   You MUST NOT attempt to build and styles or JavaScript because I'm currently running `grunt watch` in the background, meaning
    style and JS files will be built automatically.
-   You MUST NOT attempt to test in your browser for the time being. I'll let you know if the UI/UX doesn't look good or isn't
    correct.
-   You SHOULD strictly follow this implementation plan in **sequential order**. DO NOT make your own implementation plan. You MUST
    use _this_ plan only.
-   You may add sections or subsections to this implementation plan, only if you _truly_ feel like they should be added.

IMPORTANT NOTES BEFORE YOU BEGIN:

At any point, you should always feel free to ask me any additional clarifying questions about what you're working on or what you are
planning to work on next. Refer to this implementation plan and your full plug-in subfeature examination before asking questions,
though. If you have no clarifying questions at the moment, then we can get started.

---

# **REVISED DETAILED IMPLEMENTATION PLAN**

This is the implementation plan for fixing the virtual course consent submission view for administrators. At the current time, the
view is not working properly so we must implement a large and cohesive fix to make the experience with using the view more
frictionless for administrators.

### **Phase 0: Pre-Implementation Audit**

**0.1 Template Override Detection**

-   ✅ Scan for theme overrides of equipment templates
    **0.2 Data Validation**
-   ✅ Verify courseids JSON format across existing records
    **0.3 Dependency Analysis**
-   ✅ Check for any hardcoded references to individual exchange columns
    **0.4 Backup Strategy**
-   ✅ Implement database backup before column restructuring

### **Phase 1: Core Foundation & Pagination Issues**

**1.1 Fix Pagination Implementation**

-   ✅ Replace current `$table->out(25, true)` with proper Moodle 5.0 `table_sql` pagination API
-   ✅ Implement `$table->pageable(true)` with AJAX support and fallback to standard pagination
-   ✅ Add user-selectable page sizes (10, 25, 50, 100) with default of 25
-   ✅ Remove the custom "Showing submissions: 25" notification in favor of proper pagination controls
-   ✅ Implement proper page state management in URLs

**1.2 Update Database Query Optimization**

-   ✅ Modify `vcc_submission_service::build_table_sql()` to support proper pagination with LIMIT/OFFSET
-   ✅ Optimize joins to reduce query complexity for large datasets
-   ✅ Add proper SQL indexing recommendations if needed
-   ✅ Analyze query performance impact of switching from current enrollment checking to JSON-based course display
-   ✅ Monitor database load pattern changes with new course display logic

### **Phase 2: Table Column Restructuring**

**2.1 Consolidate Exchange Pickup Columns (Option A)**

-   ✅ Combine these columns into single `exchange_pickup_info` column:
    -   ✅ `exchange_pickup_method`
    -   ✅ `exchange_pickup_person`
    -   ✅ `exchange_pickup_phone`
    -   ✅ `exchange_pickup_details`
    -   ✅ `exchange_address_details`
-   ✅ Keep separate: `exchange_partnership` and `exchange_timeframe`
-   ✅ Create new template `vcc_exchange_pickup_cell.mustache` for multi-line display
-   ✅ Determine sorting strategy for consolidated `exchange_pickup_info` column (make non-sortable or implement custom sorting logic)

**2.2 Add Missing Columns in Correct Order**

-   ✅ Add `usernotes` and `adminnotes` columns right before `electronicsignature`
-   ✅ Updated column order:
    -   ✅ ...exchange_timeframe
    -   ✅ exchange_pickup_info (NEW consolidated column)
    -   ✅ **usernotes** (NEW)
    -   ✅ **adminnotes** (NEW)
    -   ✅ electronicsignature...
-   ✅ Create proper column formatters: `col_exchange_pickup_info()`, `col_usernotes()`, and `col_adminnotes()`
-   ✅ Ensure all column formatters (`col_usernotes()`, `col_adminnotes()`, `col_exchange_pickup_info()`) use Mustache templates instead of direct HTML output

### **Phase 3: Course Display Logic Fixes**

**3.1 Fix Student Course Display Logic**

-   ✅ Modify `vcc_submission_service::get_students_display_data()` to use the `courseids` JSON field from `local_equipment_vccsubmission_student` table
-   ✅ Decode JSON courseids (like '[841]') and get course information for ALL courses listed
-   ✅ **CRITICAL:** Show ALL courses from the JSON regardless of:
    -   ✅ Current enrollment status
    -   ✅ Course end dates
    -   ✅ Course active status
-   ✅ This preserves historical record of what courses student was enrolled in when parent filled out form
-   ✅ Implement course data caching strategy to handle individual database calls for each course ID
-   ✅ Preserve existing 'No courses' logic while implementing JSON-based approach

**3.2 Enhanced Course Display with Status Indicators**

-   ✅ For each course, check current enrollment status and show appropriate styling:
    -   ✅ Active enrollment: standard `badge text-bg-primary`
    -   ✅ Suspended enrollment: `badge text-bg-warning`
    -   ✅ No longer enrolled: `badge text-bg-secondary`
    -   ✅ Course ended: `badge text-bg-light`
-   ✅ Handle edge case where student has no courses in JSON (show "No courses")
-   ✅ Create enhanced `vcc_students_cell.mustache` template for better course display
-   ✅ Handle malformed courseids JSON data: `null`, `""`, `"[]"`, `"[null]"`, etc.

### **Phase 4: Filter System Overhaul**

**4.1 Enhance Filter Form Layout**

-   ✅ Fix layout issues in `vcc_filter_form.php` using proper Bootstrap 5 grid system
-   ✅ Implement collapsible filter section with better spacing and responsive design
-   ✅ Replace separate start/end dates with proper date range functionality
-   ✅ Add mobile-responsive design improvements

**4.2 Add Advanced Filter Capabilities**

-   ✅ Add quick filter presets (This Week, This Month, This Year)
-   ✅ Implement proper filter persistence in URL parameters
-   ✅ Add filter count badges and clear indicators
-   ✅ Improve form validation and error handling

### **Phase 5: Column Management System**

**5.1 Implement JavaScript Column Hiding/Showing**

-   ✅ Create new AMD module `amd/src/vcc_table_columns.js` (ES6 standard)
-   ✅ Add column visibility controls with minimal space usage for hidden columns
-   ✅ Save column preferences per-table using Moodle user preferences API:
    -   ✅ Preference name: `local_equipment_vcc_table_columns`
    -   ✅ Store as JSON: `{\"hidden_columns\": [\"mailing_address\", \"billing_address\"]}`
-   ✅ Add column management UI (dropdown or modal)
-   ✅ Create `vcc_column_controls.mustache` for column visibility toggle interface

**5.2 Implement JavaScript Column Resizing**

-   ✅ Add interactive column resizing using JavaScript/CSS resize functionality
-   ✅ Save column widths in user preferences:
    -   ✅ Preference name: `local_equipment_vcc_table_column_widths`
    -   ✅ Store as JSON: `{\"timecreated\": \"120px\", \"firstname\": \"150px\"}`
-   ✅ Implement smooth resize animations and constraints

**5.3 Enhanced Table Functionality**

-   ✅ Add horizontal scrolling with proper fixed headers
-   ✅ Implement column drag-and-drop reordering (stretch goal)
-   ✅ Add table state persistence across page loads

**5.4 JavaScript Integration**

-   ✅ Add JavaScript initialization in `view.php` to load and initialize the column management module
-   ✅ Export `init` function from AMD module for proper integration
-   ✅ Add error handling and DOM readiness checks

### **Phase 6: UI/UX Improvements & SCSS**

**6.1 Fix Column Wrapping and Styling Issues**

-   ✅ Update `scss/vcc_table.scss` with proper column width constraints
-   ✅ Implement text truncation with tooltips for long content (addresses, notes)
-   ✅ Add responsive breakpoints for different screen sizes
-   ✅ Import enhanced styles into `scss/styles.scss`

**6.2 Enhanced Table Styling & Bootstrap 5**

-   ✅ Improve table classes and styling with proper Bootstrap 5 components
-   ✅ Add loading states, empty states, and better status indicators
-   ✅ Implement hover effects and improved accessibility features
-   ✅ Add print styles and dark mode support

### **Phase 7: Template & Frontend Updates**

**7.1 Create/Update Mustache Templates**

-   ✅ Create `vcc_exchange_pickup_cell.mustache` for consolidated exchange pickup info
-   ✅ Update `vcc_students_cell.mustache` for enhanced course display with status badges
-   ✅ Update `vcc_filters.mustache` for improved filter layout
-   ✅ Add `vcc_column_manager.mustache` for column visibility controls
-   ✅ Update existing templates that reference old separate exchange columns to use new consolidated approach
-   ✅ Create `vcc_loading_state.mustache` for AJAX loading indicators
-   ✅ Create `vcc_empty_state.mustache` for when no submissions are found
-   ✅ Create `vcc_error_state.mustache` for error handling display
-   ✅ Create `vcc_pagination_ajax.mustache` for AJAX pagination content

**7.2 JavaScript Enhancements**

-   ✅ Create `amd/src/vcc_table_columns.js` for column management (ES6)
-   ✅ Update existing `amd/src/vccsubmissions.js` for enhanced functionality
-   ✅ Implement AJAX pagination with proper error handling
-   ✅ Add table state management and user preference saving
-   ✅ Implement proper sesskey validation for all AJAX calls to ensure Moodle security compliance
-   ✅ Add browser compatibility checks for JavaScript APIs used in column resizing (ResizeObserver, etc.)

### **Phase 8: Service Layer Updates**

**8.1 Enhanced VCC Submission Service**

-   ✅ Update `get_students_display_data()` for new course logic
-   ✅ Add `get_exchange_pickup_display_data()` method for consolidated column
-   ✅ Implement proper pagination support in `build_table_sql()`
-   ✅ Add user preference management methods

**8.2 External Web Services for AJAX**

-   ✅ Create external function for column preference saving
-   ✅ Add AJAX endpoint for pagination data
-   ✅ Implement proper capability checks and security
-   ✅ Ensure all external AJAX functions include proper sesskey parameter validation
-   ✅ Ensure all AJAX response content uses Mustache templates for proper theme override support

**8.3 Database Services & External Functions Setup**

    -   ✅ Update `db/services.php` to define AJAX web services for column preferences and pagination
    -   ✅ Create external function files in `classes/external/` for:
        -   ✅ `save_column_preferences.php`
        -   ✅ `get_table_data.php` (for AJAX pagination)
    -   ✅ Ensure proper capability checks and parameter validation

### **Phase 9: Testing & Validation**

**9.1 Data Validation & Error Handling**

-   ✅ Create comprehensive test framework for VCC submission data validation
-   ✅ Test course display with various JSON formats and edge cases:
    -   ✅ Valid formats: `["841"]`, `["841","842"]`, `[841,842]` (mixed string/int)
    -   ✅ Invalid formats: `null`, `""`, `"[]"`, `"[null]"`, `"invalid_json"`, `"[,]"`
    -   ✅ Edge cases: `"[0]"`, `"[-1]"`, `"[999999]"`, `"[\"\"]"`, `"[\"null\"]"`
    -   ✅ Unicode/special chars: `"[\"test\"]"`, courseids with emoji characters
-   ✅ Create test data generator for malformed courseids scenarios
-   ✅ Test service methods in `vcc_submission_service.php`:
    -   ✅ `get_student_courses()` error handling with all JSON variants
    -   ✅ Course cache behavior with malformed data and memory usage
    -   ✅ Database fallback mechanisms and timeout handling
-   ✅ Validate column management with different screen sizes and user preferences:
    -   ✅ Test malformed JSON in user preferences
    -   ✅ Validate column width constraints with edge cases (negative, zero, extremely large values)
    -   ✅ Test column visibility persistence across page reloads, browser restarts, session timeouts
    -   ✅ Test SQL injection attempts in column preference data
-   ✅ Test pagination with large datasets (1000+ records):
    -   ✅ Create database population script with realistic data patterns
    -   ✅ Performance benchmarking for query execution times and memory usage
    -   ✅ AJAX response time measurement and database connection pool stress testing
-   ✅ Ensure proper error handling for malformed data across all components
-   ✅ Test JSON decoding error handling for malformed courseids data
-   ✅ Test upgrade path for existing installations with current column preferences
-   ✅ Test export functionality (CSV/Excel) with new consolidated column format and course display logic:
    -   ✅ Test with Unicode characters, special formatting, and large datasets
    -   ✅ Validate data integrity and character encoding preservation
    -   ✅ Monitor memory usage during export operations
-   ✅ Specific edge case testing for malformed courseids JSON data variations
-   ✅ Performance comparison testing between old enrollment-checking and new JSON-based course display methods
-   ✅ Test all Mustache templates for proper theme override functionality

**9.2 Theme Override & Template Compatibility**

-   ✅ Scan for existing theme overrides of equipment templates
-   ✅ Test template inheritance with popular Moodle themes:
    -   ✅ Classic, Boost, Adaptable, Moove themes
    -   ✅ Custom institutional themes
-   ✅ Template variable validation:
    -   ✅ Test for missing template variables in overrides
    -   ✅ Check for deprecated template syntax
    -   ✅ Validate CSS class conflicts between themes and plugin
-   ✅ Test all VCC templates with edge case data:
    -   ✅ Empty arrays, null values, extremely long strings
    -   ✅ HTML entities, special characters, malformed URLs
-   ✅ Template security validation:
    -   ✅ XSS prevention in user-generated content
    -   ✅ Proper escaping of dynamic content
    -   ✅ Safe handling of user preference data

**9.3 Mobile & Touch Interface Testing**

-   ✅ CSS breakpoint testing with real devices:
    -   ✅ iOS Safari, Android Chrome, mobile Edge browsers
    -   ✅ Tablet landscape/portrait orientations
    -   ✅ Small screen devices (320px width minimum)
-   ✅ Touch interaction testing:
    -   ✅ Column resizing with touch drag functionality
    -   ✅ Dropdown menus on touch devices
    -   ✅ Filter form usability on mobile interfaces
    -   ✅ Table scrolling behavior and performance
-   ✅ Mobile-specific edge case testing:
    -   ✅ Touch gesture conflicts (column resize vs. table scroll)
    -   ✅ Dropdown activation vs. page scroll conflicts
    -   ✅ Filter collapse/expand behavior on mobile
-   ✅ Virtual keyboard interaction testing:
    -   ✅ Form field focus behavior adjustments
    -   ✅ Screen real estate adjustments
    -   ✅ Input validation on mobile keyboards

**9.4 Cross-browser & Performance Testing**

-   ✅ Create cross-browser compatibility testing suite
-   ✅ Test JavaScript column functionality across browsers:
    -   ✅ Modern browsers (Chrome 90+, Firefox 85+, Safari 14+, Edge 90+)
    -   ✅ JavaScript API compatibility (ResizeObserver support and polyfills)
    -   ✅ Local Storage quotas and behavior differences
    -   ✅ ES6 module loading differences
    -   ✅ Bootstrap 5 dropdown API variations
-   ✅ CSS feature support validation:
    -   ✅ CSS Grid/Flexbox implementation differences
    -   ✅ CSS custom properties browser support
    -   ✅ Scroll behavior inconsistencies
-   ✅ Create responsive design validation tools
-   ✅ Validate responsive design on mobile/tablet/desktop
-   ✅ Performance testing with large datasets and many columns:
    -   ✅ Create performance benchmarking system
    -   ✅ Query execution time monitoring and memory usage tracking
    -   ✅ AJAX response time measurement
    -   ✅ Database connection pool stress testing

**9.5 Accessibility Compliance (WCAG 2.1 AA)**

-   ✅ Create accessibility testing framework
-   ✅ Test accessibility with screen readers using actual tools:
    -   ✅ NVDA, JAWS, VoiceOver testing
    -   ✅ Table navigation announcement patterns
    -   ✅ Column management control accessibility
    -   ✅ Status badge and tooltip announcements
-   ✅ Test accessibility compliance specifically for new column management controls and AJAX pagination with screen readers
-   ✅ Validate keyboard navigation for all new interactive elements:
    -   ✅ Tab order through table and controls
    -   ✅ Enter/Space key activation of buttons
    -   ✅ Escape key behavior in dropdowns
    -   ✅ Focus management during AJAX updates
-   ✅ Visual accessibility validation:
    -   ✅ Color contrast ratio verification (4.5:1 minimum)
    -   ✅ High contrast mode compatibility
    -   ✅ Focus indicator visibility
    -   ✅ Text scaling up to 200% support

**9.6 Integration & Security Testing**

-   ✅ AJAX Security & Error Handling validation:
    -   ✅ Sesskey verification in all endpoints
    -   ✅ Parameter sanitization and type checking
    -   ✅ Rate limiting and CSRF protection testing
    -   ✅ SQL injection prevention validation
-   ✅ Error handling robustness testing:
    -   ✅ Network failure scenarios
    -   ✅ Server timeout responses
    -   ✅ Malformed AJAX responses
    -   ✅ Graceful degradation when JavaScript disabled
-   ✅ Database Performance & Indexing analysis:
    -   ✅ Query optimization analysis and execution plan analysis
    -   ✅ Index usage verification and join optimization effectiveness
    -   ✅ Real-world performance testing with concurrent user simulation
    -   ✅ Database load under stress and memory usage patterns

**9.7 Upgrade Path & Backward Compatibility**

-   ✅ User preference migration testing:
    -   ✅ Existing preference format conversion
    -   ✅ Rollback capability verification
    -   ✅ Data corruption prevention
-   ✅ Database schema compatibility testing:
    -   ✅ Version upgrade testing
    -   ✅ Downgrade scenario handling
    -   ✅ Foreign key constraint validation
-   ✅ Breaking change impact assessment:
    -   ✅ Template dependency analysis for custom template override impact
    -   ✅ CSS class name changes and JavaScript API modifications
    -   ✅ URL compatibility testing for existing bookmarks and external links

### **Phase 10: Final Integration & Moodle 5.0 Compliance**

**10.1 Code Standards Compliance**

-   ✅ Ensure all code follows Moodle 5.0 coding standards exactly
-   ✅ Add proper PHPDoc comments and strict type hints
-   ✅ Implement proper capability checks and security measures
-   ✅ Follow ES6 standards for all JavaScript code
-   ✅ Update `version.php` with new version number to reflect added functionality
-   ✅ Consider RTL language support implications for new column management interface
    -   ✅ Add inline documentation in `amd/src/vcc_table_columns.js` for RTL considerations
    -   ✅ Document RTL implications in `scss/vcc_table.scss` for column resizing handles
    -   ✅ Add comments for dropdown positioning and text alignment in exchange pickup cells
    -   ✅ Document drag/drop and resize handle positioning for RTL layouts

**10.2 Comprehensive Logging and Debugging Infrastructure**

-   ✅ Implement configurable debug flags with safe error handling:
    -   ✅ Add debug flag constants at top of `classes/service/vcc_submission_service.php`
    -   ✅ Add debug flag constants at top of `classes/external/get_table_data.php`
    -   ✅ Add debug flag constants at top of `classes/external/save_column_preferences.php`
    -   ✅ Add debug flag constants at top of `amd/src/vcc_table_columns.js`
    -   ✅ Add debug flag constants at top of `amd/src/vccsubmissions.js`
-   ✅ Implement comprehensive logging for column preference operations:
    -   ✅ Log column preference save/load operations with timing
    -   ✅ Log column preference validation errors and recovery
    -   ✅ Log column width constraint violations and adjustments
    -   ✅ Log column visibility state changes and persistence
-   ✅ Implement comprehensive logging for AJAX operations:
    -   ✅ Log AJAX request/response timing and payload sizes
    -   ✅ Log AJAX error scenarios and recovery attempts
    -   ✅ Log pagination request performance and database query times
    -   ✅ Log table data refresh operations and cache hits/misses
-   ✅ Implement comprehensive logging for course data processing:
    -   ✅ Log courseids JSON parsing operations and malformed data handling
    -   ✅ Log course database query performance and cache effectiveness
    -   ✅ Log course status determination logic and enrollment checks
    -   ✅ Log course data validation and sanitization operations
-   ✅ Implement JavaScript error tracking and performance logging:
    -   ✅ Log column management operation failures and recovery
    -   ✅ Log table rendering performance and DOM manipulation timing
    -   ✅ Log browser compatibility issues and polyfill usage
    -   ✅ Log memory usage during large dataset operations
-   ✅ Ensure all debug functionality uses try-catch blocks to prevent fatal errors
-   ✅ Add conditional logging that can be disabled for production performance

**10.3 End-to-End Testing Framework**

-   ✅ Create comprehensive administrator workflow testing scenarios:
    -   ✅ Test complete filtering workflow from basic to advanced filters
    -   ✅ Test column management workflow: show/hide, resize, reorder, persist
    -   ✅ Test pagination workflow: AJAX pagination, page size changes, state persistence
    -   ✅ Test export workflow: CSV/Excel with all column configurations
    -   ✅ Test course display workflow: JSON parsing, status badges, enrollment states
-   ✅ Create performance testing with large datasets:
    -   ✅ Test with 1000+ VCC submission records
    -   ✅ Test with multiple concurrent administrator sessions
    -   ✅ Test memory usage during export operations
    -   ✅ Test database query performance under load
    -   ✅ Test AJAX response times with large result sets
-   ✅ Create cross-browser functionality validation:
    -   ✅ Test JavaScript column management in Chrome 90+, Firefox 85+, Safari 14+, Edge 90+
    -   ✅ Test ResizeObserver API compatibility and polyfill fallbacks
    -   ✅ Test Local Storage behavior and quota management
    -   ✅ Test ES6 module loading across browser implementations
    -   ✅ Test Bootstrap 5 dropdown API variations
-   ✅ Create mobile and touch interface testing:
    -   ✅ Test responsive design on mobile devices (320px+ width)
    -   ✅ Test touch-based column resizing and management
    -   ✅ Test filter form usability on mobile interfaces
    -   ✅ Test table scrolling and navigation on touch devices
-   ✅ Document testing procedures and expected results for future validation

**10.4 Database Upgrade Path and Data Migration**

-   ✅ Enhance `db/upgrade.php` with new upgrade step for Phase 10 features:
    -   ✅ Add upgrade step for column preference data validation and migration
    -   ✅ Add upgrade step for malformed courseids JSON data cleanup
    -   ✅ Add upgrade step for user preference format standardization
    -   ✅ Add upgrade step for debug configuration initialization
-   ✅ Implement rollback procedures for database changes:
    -   ✅ Create backup validation before preference migration
    -   ✅ Add rollback capability for preference format changes
    -   ✅ Implement data corruption prevention and validation
    -   ✅ Add foreign key constraint validation and repair
-   ✅ Handle existing user preferences that might conflict:
    -   ✅ Validate existing `local_equipment_vcc_table_columns` preferences
    -   ✅ Validate existing `local_equipment_vcc_table_column_widths` preferences
    -   ✅ Migrate malformed JSON preference data to valid format
    -   ✅ Handle edge cases in preference data (null, empty, invalid JSON)
-   ✅ Increment version number in `version.php` to trigger upgrade process
-   ✅ Test upgrade path with various existing data scenarios

**10.5 Final Integration Validation**

-   ✅ Ensure all SCSS imports work correctly in `scss/styles.scss`
-   ✅ Validate all Moodle 5.0 coding standards compliance across modified files
-   ✅ Verify proper capability checks and security measures in all new functionality
-   ✅ Confirm ES6 standards adherence in all JavaScript modifications
-   ✅ Test integration between all Phase 10 components (logging, testing, RTL docs, upgrades)
-   ✅ Validate that debug functionality can be safely enabled/disabled without affecting core functionality

### **Phase 11: Language Strings & Configuration Updates**

**11.1 Language String Updates**

-   ✅ Add new strings in `lang/en/local_equipment.php` for: - Consolidated exchange pickup column header and help text - Column management interface strings ("Show/Hide Columns", "Reset Column Layout", etc.) - New error messages for AJAX operations - Accessibility strings for screen readers
    **11.2 Database Configuration**
-   ✅ Update `db/services.php` with new external function definitions
-   ✅ Add any new capability definitions in `db/access.php` if needed for column management

### **Phase 12: Performance Optimization & Logging**

**12.1 Performance Optimization**

-   Implement caching strategies for AJAX table data requests
-   Optimize JavaScript event handlers to prevent memory leaks during column operations
-   Add debouncing for column resize operations to improve performance
-   Query optimization analysis comparing old vs new course display database patterns
-   Implement and test course data caching to reduce individual course ID lookups
-   Consider deployment strategy for active users and table state management during updates

**12.2 Enhanced Logging & Debugging**

-   Add comprehensive error logging for AJAX operations and user preference saving
-   Implement JavaScript error tracking for column management failures
-   Add performance timing logs for large dataset operations

### **Add Phase 13: Migration & Backward Compatibility**

**13.1 Column Reference Migration**

-   Update any hardcoded column references

**13.2 URL Compatibility Layer**

-   Ensure old pagination URLs still work

**13.3 Data Format Validation**

-   Handle edge cases in existing courseids JSON data

**13.4 Rollback Plan**

-   Prepare rollback procedures if issues arise

**13.5 Preference Migration:**

-   Check for and handle any existing related user preferences that might conflict with new column preference format

---

## **POTENTIAL BREAKING CHANGES TO ADDRESS:**

**1. Column Consolidation Breaking Change:**

-   Removing individual exchange columns (`exchange_pickup_person`, `exchange_pickup_phone`, etc.) and replacing with consolidated `exchange_pickup_info` will break:
    -   Any custom theme template overrides that reference these columns
    -   Any custom CSS that targets these specific column classes
    -   Any existing sorting/filtering logic that depends on individual column names

**2. Template Dependencies:**

-   Existing `vcc_students_cell.mustache` template changes could break if there are theme overrides
-   Need to check for any templates in theme directories that override the equipment templates

**3. Course Display Logic Breaking Change:**

-   Completely changing from live enrollment checking to JSON-based historical display is a fundamental logic change
-   If existing `courseids` JSON format differs from expected format, this could cause errors
-   Need migration strategy for any malformed or missing courseids data

**4. Pagination URL Structure:**

-   AJAX pagination might break existing bookmarked URLs or external links that rely on current pagination parameters
-   Need to ensure backward compatibility with existing URL parameters

**5. User Preference Conflicts:**

-   Adding new user preferences could conflict if users already have related preferences set

---
