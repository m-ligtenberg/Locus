// API configuration for GitHub Pages deployment with Vercel backend
const API_BASE_URL = import.meta.env.PROD 
  ? 'https://locus-mich-ligtenbergs-projects.vercel.app'
  : '';

export { API_BASE_URL };