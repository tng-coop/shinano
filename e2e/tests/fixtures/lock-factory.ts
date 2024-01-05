import { promises as fsPromises } from 'fs';
import fs from 'fs';
import path from 'path';

// Function to map a lock name to a lock file path
const lockNameToPath = (lockName) => {
  return path.join(__dirname, `${lockName}.lock`);
};

// Playwright fixture with a file-based mutex using a polling mechanism
const lockFactory: any = async ({ }, use) => {
  // Release lock function
  const releaseLock = async (filePath) => {
    // console.log("Releasing lock: " + filePath);
    await fsPromises.unlink(filePath).catch(err => console.error('Error releasing lock:', err));
  };

  const attemptLockCreation = async (lockFilePath) => {
    try {
      fs.writeFileSync(lockFilePath, 'locked', { flag: 'wx' });
      // console.log("Lock acquired: " + lockFilePath);
      return true;
    } catch (err) {
      if (err.code === 'EEXIST') {
        return false; // Lock file already exists
      } else {
        throw err; // Other errors are thrown
      }
    }
  };

  const waitForLock = async (lockFilePath) => {
    while (!await attemptLockCreation(lockFilePath)) {
      // console.log(`Waiting for lock: ${lockFilePath}`);
      await new Promise(resolve => setTimeout(resolve, 100)); // Wait for 1 second before retrying
    }
  };

  const createLock = async (lockName) => {
    const lockFilePath = lockNameToPath(lockName);
    await waitForLock(lockFilePath);
    return () => releaseLock(lockFilePath); // Return a function that calls releaseLock with the correct path
  };

  await use(createLock);
};

export { lockFactory };
