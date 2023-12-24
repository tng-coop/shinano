#!/bin/bash
set -e
npm ci
npx playwright --version
npx playwright test
