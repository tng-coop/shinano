#!/bin/bash
set -e

# Set script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

# Run setup script
bash setup-e2e-tests.sh

# Pass all arguments to npx playwright
npx playwright "$@"
