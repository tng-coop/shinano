// global-setup.ts
import { removeLockFiles } from './remove-lock-files';

const scriptDir = __dirname; // The directory where your .lock files are

async function setup() {
  // Remove .lock files
  removeLockFiles(scriptDir);
}

export default setup;
