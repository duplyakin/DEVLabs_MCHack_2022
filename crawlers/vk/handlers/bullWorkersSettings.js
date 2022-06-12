const Bull = require('bull');

const redis = {
  host: '127.0.0.1',
  port: 6379,
  maxRetriesPerRequest: null,
  connectTimeout: 180000
  };

const defaultJobOptions = {
  removeOnComplete: true,
  removeOnFail: false,
};

const limiter = {
  max: 10000,
  duration: 1000,
  bounceBack: false,
};

const settings = {
  lockDuration: 600000, // Key expiration time for job locks.
  stalledInterval: 5000, // How often check for stalled jobs (use 0 for never checking).
  maxStalledCount: 2, // Max amount of times a stalled job will be re-processed.
  guardInterval: 5000, // Poll interval for delayed jobs and added jobs.
  retryProcessDelay: 30000, // delay before processing next job in case of internal error.
  drainDelay: 5, // A timeout for when the queue is in drained state (empty waiting for jobs).
};

const bull_workers = new Bull('workers_queue', { redis, defaultJobOptions, settings, limiter });

module.exports = { bull_workers };
