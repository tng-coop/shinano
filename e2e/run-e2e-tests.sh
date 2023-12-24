#!/bin/bash
set -e
# Check if PHP server is running on localhost:8000
php -S localhost:8000 -t ../pubroot &
echo "PHP server started on localhost:8000"
npm ci
npx playwright --version
npx playwright test
