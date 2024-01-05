import fs from 'fs';
import path from 'path';

// Function to remove all .lock files in the directory
export const removeLockFiles = () => {
  // Path to the directory containing the .lock files
  const lockDir = path.join(__dirname, 'tests', 'fixtures');

  fs.readdir(lockDir, (err, files) => {
    if (err) {
      console.error(`Error reading directory: ${err.message}`);
      return;
    }

    files.forEach(file => {
      if (file.endsWith('.lock')) {
        const filePath = path.join(lockDir, file);

        fs.unlink(filePath, unlinkErr => {
          if (unlinkErr) {
            console.error(`Error deleting file ${file}: ${unlinkErr.message}`);
            return;
          }
          console.log(`${file} was deleted`);
        });
      }
    });
  });
};
