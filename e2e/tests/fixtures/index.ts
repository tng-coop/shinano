import { test as baseTest } from '@playwright/test';
import { pageFactory } from './page-factory';
import { lockFactory } from './lock-factory';
const test = baseTest.extend({ lockFactory, pageFactory })
export { test }; 