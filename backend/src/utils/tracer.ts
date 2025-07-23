import tracer from 'dd-trace';
import env from './env.js';

// Initialize the tracer
tracer.init({
  env: env.NODE_ENV,
  service: 'locus-backend',
  version: '5.0.0',
  logInjection: true
});

// Export so it can be imported and used in other files
export default tracer;
