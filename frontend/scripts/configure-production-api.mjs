import { mkdir, writeFile } from 'node:fs/promises';

const value = process.env.WHATTOCOOK_API_BASE_URL;

if (!value) {
  throw new Error('WHATTOCOOK_API_BASE_URL is required for a production or Android build. Use an HTTPS URL ending in /api.');
}

let apiUrl;
try {
  apiUrl = new URL(value);
} catch {
  throw new Error('WHATTOCOOK_API_BASE_URL must be a valid absolute HTTPS URL.');
}

if (apiUrl.protocol !== 'https:' || apiUrl.username || apiUrl.password) {
  throw new Error('WHATTOCOOK_API_BASE_URL must be HTTPS and must not contain credentials.');
}

apiUrl.pathname = `${apiUrl.pathname.replace(/\/$/, '')}/api`.replace(/\/api\/api$/, '/api');
apiUrl.search = '';
apiUrl.hash = '';

const output = `// Generated at build time. Do not commit this file.\nexport const environment = {\n  production: true,\n  apiBaseUrl: ${JSON.stringify(apiUrl.toString().replace(/\/$/, ''))},\n  androidApiBaseUrl: ${JSON.stringify(apiUrl.toString().replace(/\/$/, ''))}\n};\n`;

await mkdir('src/environments', { recursive: true });
await writeFile('src/environments/environment.production.generated.ts', output, 'utf8');
