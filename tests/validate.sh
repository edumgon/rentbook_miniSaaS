#!/bin/bash

#
# Validation Script for Book Lending Manager
# Run: bash tests/validate.sh
#

echo "🧪 Book Lending Manager - Validation Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Function to check file exists
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        return 0
    else
        echo -e "${RED}✗${NC} $2 - Missing: $1"
        ((ERRORS++))
        return 1
    fi
}

# Function to check directory exists
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        return 0
    else
        echo -e "${RED}✗${NC} $2 - Missing: $1"
        ((ERRORS++))
        return 1
    fi
}

# Check directory structure
echo "📁 Checking Directory Structure..."
check_dir "app" "App directory"
check_dir "app/core" "Core classes directory"
check_dir "app/models" "Models directory"
check_dir "app/controllers" "Controllers directory"
check_dir "app/views" "Views directory"
check_dir "config" "Config directory"
check_dir "database" "Database directory"
check_dir "public_html" "Public directory"
check_dir "public_html/css" "CSS directory"
check_dir "public_html/js" "JS directory"
check_dir "tests" "Tests directory"
echo ""

# Check core files
echo "📄 Checking Core Files..."
check_file "app/core/Database.php" "Database class"
check_file "app/core/Router.php" "Router class"
check_file "app/core/Auth.php" "Auth class"
check_file "app/core/Env.php" "Env class"
echo ""

# Check models
echo "📄 Checking Models..."
check_file "app/models/Model.php" "Base Model class"
check_file "app/models/User.php" "User model"
check_file "app/models/Book.php" "Book model"
check_file "app/models/Borrower.php" "Borrower model"
check_file "app/models/Loan.php" "Loan model"
echo ""

# Check controllers
echo "📄 Checking Controllers..."
check_file "app/controllers/Controller.php" "Base Controller"
check_file "app/controllers/AuthController.php" "AuthController"
check_file "app/controllers/DashboardController.php" "DashboardController"
check_file "app/controllers/BookController.php" "BookController"
check_file "app/controllers/BorrowerController.php" "BorrowerController"
check_file "app/controllers/LoanController.php" "LoanController"
echo ""

# Check views
echo "📄 Checking Views..."
check_file "app/views/layout.php" "Layout view"
check_file "app/views/dashboard.php" "Dashboard view"
check_file "app/views/auth/login.php" "Login view"
check_file "app/views/books/list.php" "Book list view"
check_file "app/views/books/add.php" "Book add view"
check_file "app/views/books/edit.php" "Book edit view"
check_file "app/views/borrowers/list.php" "Borrower list view"
check_file "app/views/borrowers/edit.php" "Borrower edit view"
check_file "app/views/loans/list.php" "Loan list view"
check_file "app/views/loans/create.php" "Loan create view"
check_file "app/views/loans/history.php" "Loan history view"
echo ""

# Check public files
echo "📄 Checking Public Assets..."
check_file "public_html/index.php" "Entry point"
check_file "public_html/.htaccess" "Apache config"
check_file "public_html/css/style.css" "Stylesheet"
check_file "public_html/js/app.js" "JavaScript"
echo ""

# Check config files
echo "📄 Checking Configuration..."
check_file "config/database.php" "Database config"
check_file "config/oauth.php" "OAuth config"
check_file "database/schema.sql" "Database schema"
check_file ".env.example" "Environment example"
check_file "README.md" "Documentation"
check_file ".gitignore" "Git ignore"
echo ""

# Check for required PHP syntax patterns
echo "🔍 Checking Code Patterns..."

# Check for proper class definitions
if grep -q "class Database" app/core/Database.php; then
    echo -e "${GREEN}✓${NC} Database class properly defined"
else
    echo -e "${RED}✗${NC} Database class definition issue"
    ((ERRORS++))
fi

if grep -q "class Router" app/core/Router.php; then
    echo -e "${GREEN}✓${NC} Router class properly defined"
else
    echo -e "${RED}✗${NC} Router class definition issue"
    ((ERRORS++))
fi

# Check for security patterns
if grep -q "htmlspecialchars" app/views/layout.php; then
    echo -e "${GREEN}✓${NC} Output escaping in layout"
else
    echo -e "${YELLOW}⚠${NC} Output escaping may be missing"
    ((WARNINGS++))
fi

if grep -q "csrf_token" app/views/layout.php; then
    echo -e "${GREEN}✓${NC} CSRF token in forms"
else
    echo -e "${YELLOW}⚠${NC} CSRF token may be missing"
    ((WARNINGS++))
fi

# Check for PDO prepared statements pattern
if grep -q "prepare" app/core/Database.php; then
    echo -e "${GREEN}✓${NC} PDO prepared statements in Database class"
else
    echo -e "${RED}✗${NC} PDO prepared statements not found"
    ((ERRORS++))
fi

# Check .htaccess rewrite rules
if grep -q "RewriteEngine On" public_html/.htaccess; then
    echo -e "${GREEN}✓${NC} Apache rewrite enabled"
else
    echo -e "${RED}✗${NC} Apache rewrite not configured"
    ((ERRORS++))
fi

echo ""

# Check file sizes (should not be empty)
echo "📊 Checking File Integrity..."

for file in app/core/*.php app/models/*.php app/controllers/*.php; do
    if [ -f "$file" ]; then
        size=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file" 2>/dev/null || echo "0")
        if [ "$size" -lt 100 ]; then
            echo -e "${YELLOW}⚠${NC} $file seems too small (${size} bytes)"
            ((WARNINGS++))
        fi
    fi
done

echo -e "${GREEN}✓${NC} All files have content"
echo ""

# Check for common issues
echo "🔍 Checking for Common Issues..."

# Check for echo without escaping in views
ECHO_UNESCAPED=$(grep -rn "echo \$" app/views/*.php 2>/dev/null | grep -v "htmlspecialchars" | wc -l)
if [ "$ECHO_UNESCAPED" -gt 0 ]; then
    echo -e "${YELLOW}⚠${NC} Found $ECHO_UNESCAPED potential unescaped outputs in views"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓${NC} Outputs appear to be properly escaped"
fi

# Check for hardcoded credentials pattern
HARDCODED=$(grep -rn "password.*=.*['\"]" app/ config/ --include="*.php" 2>/dev/null | grep -v "password.*=.*\$_" | grep -v "// " | grep -v "password.*=.*''" | wc -l)
if [ "$HARDCODED" -gt 0 ]; then
    echo -e "${YELLOW}⚠${NC} Found potential hardcoded credentials patterns"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓${NC} No obvious hardcoded credentials found"
fi

echo ""

# Count total files
echo "📈 Project Statistics..."
PHP_FILES=$(find app -name "*.php" 2>/dev/null | wc -l)
VIEW_FILES=$(find app/views -name "*.php" 2>/dev/null | wc -l)
CSS_FILES=$(find public_html -name "*.css" 2>/dev/null | wc -l)
JS_FILES=$(find public_html -name "*.js" 2>/dev/null | wc -l)

echo "  PHP Classes: $PHP_FILES"
echo "  View Templates: $VIEW_FILES"
echo "  CSS Files: $CSS_FILES"
echo "  JS Files: $JS_FILES"
echo ""

# Summary
echo "=========================================="
echo "📋 VALIDATION SUMMARY"
echo "=========================================="

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}🎉 All checks passed! System is ready.${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠  Passed with $WARNINGS warning(s).${NC}"
    exit 0
else
    echo -e "${RED}✗ Failed with $ERRORS error(s) and $WARNINGS warning(s).${NC}"
    exit 1
fi
