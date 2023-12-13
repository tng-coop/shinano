# User Guide for GitHub Artifact Downloader Script

## Introduction
This guide describes using a Python script to automate downloading and extracting
artifacts from GitHub Actions runs, specifically designed for easy viewing of
Playwright run results.

## Prerequisites
- **GitHub Token:** A `GITHUB_TOKEN` must be set in your environment variables.
- **Python Installation:** Ensure Python 3 and modules (`requests`, `shutil`,
  `subprocess`, `os`, `zipfile`) are installed.
- **Clipboard Access:** `wl-paste` is needed on Wayland for clipboard reading.
- **npm Installation:** `npm` should be installed for the live server feature.

## How to Use
1. **Copy GitHub Actions Run URL:** 
   Copy the URL of the GitHub Actions run related to Playwright tests to your
   clipboard. Format: `https://github.com/<owner>/<repo>/actions/runs/<run_id>`.

2. **Run the Script:**
   Execute the script in your terminal. It will read the URL from your clipboard.

3. **Automatic Processing:**
   The script performs the following:
   - Validates the URL.
   - Fetches artifact details from GitHub.
   - Downloads and extracts artifacts to a directory.

4. **View Playwright Test Results:**
   Extracted artifacts, mainly Playwright test results, will be in the 'artifacts'
   directory. The script also launches a live server for convenient viewing.

## Troubleshooting
- **GitHub Token Issue:** Verify `GITHUB_TOKEN` is correctly set.
- **Clipboard Problem:** Ensure the GitHub Actions run URL is correctly copied.
- **Errors During Execution:** Check console messages. Confirm all prerequisites,
  including npm, are fulfilled.

## Notes
- Tailored for downloading and viewing Playwright test artifacts from GitHub
  Actions.
- Internet connection is necessary to fetch artifacts from GitHub.

For further assistance, refer to the script's comments or contact your system
administrator.
