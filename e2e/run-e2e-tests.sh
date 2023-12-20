#!/bin/bash

# Check if PHP server is running on localhost:8000
if ! lsof -n -i:8000 | grep -q PHP; then
    echo "Starting PHP server..."
    # Start the PHP server in the background
    php -S localhost:8000 -t ../pubroot &
    echo "PHP server started on localhost:8000"
else
    echo "PHP server is already running on localhost:8000"
fi
npm ci
npx playwright --version
npx playwright test
