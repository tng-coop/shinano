import { expect } from '@playwright/test';
import { test } from './fixtures';

test('Lock Fixture', async ({ lockFactory }) => {
  let sharedCounter = 0;
  const numberOfTasks = 5;
  const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

  // Function to increment the counter with a delay, simulating a more complex operation
  const incrementCounter = async () => {
    const releaseLock = await lockFactory('counterLock');

    const currentCount = sharedCounter; // Read the current value
    await delay(50);                  // Simulate a delay, during which race conditions can occur
    sharedCounter = currentCount + 1;  // Update the counter

    await releaseLock();
  };

  // Run multiple tasks concurrently
  const tasks = Array.from({ length: numberOfTasks }, () => incrementCounter());
  await Promise.all(tasks);

  // Verify that the counter has been incremented correctly
  expect(sharedCounter).toBe(numberOfTasks);
});
