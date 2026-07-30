export const environment = {
  production: true,
  // Browser builds use the same HTTPS origin as the deployed API.
  apiBaseUrl: '/api',
  // Set the actual HTTPS API host before creating a native Android release.
  androidApiBaseUrl: 'https://api.example.com/api'
};
