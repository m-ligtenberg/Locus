import cron from 'node-cron';
import { exec } from 'child_process';
import { promisify } from 'util';
import fs from 'fs-extra';
import path from 'path';
import { logger } from '../utils/logger.js';
import env from '../utils/env.js';

const execAsync = promisify(exec);

// Ensure backup directory exists
const BACKUP_DIR = path.join(process.cwd(), 'backups');
fs.ensureDirSync(BACKUP_DIR);

// Keep last 7 daily backups
const MAX_BACKUPS = 7;

async function cleanOldBackups() {
  const files = await fs.readdir(BACKUP_DIR);
  const backupFiles = files
    .filter(file => file.endsWith('.sql.gz'))
    .map(file => ({
      name: file,
      path: path.join(BACKUP_DIR, file),
      time: fs.statSync(path.join(BACKUP_DIR, file)).mtime.getTime()
    }))
    .sort((a, b) => b.time - a.time);

  // Remove old backups
  if (backupFiles.length > MAX_BACKUPS) {
    const filesToRemove = backupFiles.slice(MAX_BACKUPS);
    for (const file of filesToRemove) {
      await fs.remove(file.path);
      logger.info(`Removed old backup: ${file.name}`);
    }
  }
}

async function createBackup() {
  try {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filename = `backup-${timestamp}.sql.gz`;
    const filepath = path.join(BACKUP_DIR, filename);

    // Extract database connection details from URL
    const dbUrl = new URL(env.DATABASE_URL);
    const host = dbUrl.hostname;
    const port = dbUrl.port;
    const database = dbUrl.pathname.slice(1);
    const username = dbUrl.username;
    const password = dbUrl.password;

    // Create backup
    const command = `PGPASSWORD=${password} pg_dump -h ${host} -p ${port} -U ${username} ${database} | gzip > ${filepath}`;
    await execAsync(command);

    logger.info(`Database backup created: ${filename}`);

    // Clean old backups
    await cleanOldBackups();
  } catch (error) {
    logger.error('Database backup failed:', error);
    throw error;
  }
}

// Schedule daily backup at 3 AM
export function setupDatabaseBackup() {
  if (env.NODE_ENV === 'production') {
    cron.schedule('0 3 * * *', async () => {
      try {
        await createBackup();
        logger.info('Scheduled database backup completed successfully');
      } catch (error) {
        logger.error('Scheduled database backup failed:', error);
      }
    });
    logger.info('Database backup scheduled for 3 AM daily');
  }
}

// Export for manual backup triggering
export { createBackup };
