## Shinano Project: End-to-End Testing Documentation

### Introduction to End-to-End Testing for Shinano Project
This document outlines the end-to-end testing process for the Shinano project.
The testing serves dual purposes: firstly, as a comprehensive end-to-end test
and secondly, as a guide for setting up the reference environment locally.

### Environment Setup
To set up the testing environment, use the following scripts:
- `e2e/setup-e2e-tests.sh`: Configures the necessary environment for the Shinano
  project.
- `e2e/run-e2e-tests.sh`: Executes the end-to-end tests using the Microsoft
  Playwright framework.

### RunTest Script
The `run-e2e-tests.sh` script acts as a wrapper for the Microsoft Playwright end-to-end
testing framework, facilitating the execution of tests in the Shinano project.

### Project Setup Methods
There are three methods for setting up the Shinano project:
- **GitHub Actions**: Utilizes CI for automated testing and deployment.
- **Docker Environment**: Sets up a Docker-based environment for MariaDB
  locally for ease of use and consistency.
- **Local-Local Environment**: A more manual setup method, requiring individual
  installations like MySQL, without using Docker.

### Client Software Requirements in Local Environment
Regardless of the environment setup method chosen, certain client software
installations are necessary:
- **MariaDB Client**: Required for database interactions.
- **PHP**: Essential for running the project.
- **PHP Extensions**: Specific extensions are necessary for full functionality.

### Artifact Viewer Integration (Convenience Tool for GitHub Actions)
For efficient and convenient retrieval and viewing of GitHub Actions runs, use
the [Artifact Viewer](https://github.com/tng-coop/artifact-viewer) script from
tng-coop.

#### Ubuntu Installation
To set up the necessary software for the Shinano project:
```
sudo apt install php-cli
sudo apt install mariadb-client
sudo apt-get install php-pdo php-mysql
sudo apt-get install php-mbstring
```

#### Usage
- Access the Artifact Viewer UI to browse action run artifacts.

For complete usage details, see the [Artifact Viewer README](https://github.com/
tng-coop/artifact-viewer).

### Additional Details
Further detailed instructions and specifications will be provided later.
