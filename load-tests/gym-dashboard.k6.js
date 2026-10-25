// Read-only(ish) synthetic load test for a STAGING copy of Gym Dashboard.
// /nutrition may initialize empty daily-meal rows; do not target production.
// Requires: BASE_URL, TEST_EMAIL, TEST_PASSWORD environment variables.
import http from 'k6/http';
import { check, fail, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 2 },
    { duration: '1m', target: 10 },
    { duration: '1m', target: 25 },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<1500'],
  },
};

const base = (__ENV.BASE_URL || '').replace(/\/+$/, '');
const email = __ENV.TEST_EMAIL || '';
const password = __ENV.TEST_PASSWORD || '';

if (!base || !email || !password) {
  throw new Error('Set BASE_URL, TEST_EMAIL and TEST_PASSWORD for a staging account.');
}

// Every VU logs in once and keeps its own session cookie jar.
let signedIn = false;

function logIn() {
  const page = http.get(`${base}/login`, { tags: { page: 'login-form' } });
  const token = page.html().find('input[name="_token"]').first().attr('value');
  if (page.status !== 200 || !token) {
    fail('Login page did not provide a CSRF token. Check the base URL.');
  }

  const response = http.post(`${base}/login`, {
    _token: token,
    email,
    password,
  }, {
    redirects: 0,
    tags: { page: 'login-submit' },
  });

  // Breeze redirects successful web-session logins to the dashboard.
  if (response.status !== 302 || (response.headers.Location || '').includes('/login')) {
    fail('Test account login failed. Use a valid active member on staging.');
  }
  signedIn = true;
}

const pages = [
  { id: 'dashboard', path: '/dashboard' },
  { id: 'nutrition', path: '/nutrition' },
  { id: 'nutrition-history', path: '/nutrition/history' },
  { id: 'food-search', path: '/nutrition/foods/search?q=%D8%AC%D8%A8%D9%86%D8%A9' },
  { id: 'programs', path: '/programs' },
  { id: 'exercise-search', path: '/programs/exercises/search?q=chest' },
  { id: 'exercises', path: '/exercises' },
  { id: 'workout-history', path: '/history' },
  { id: 'measurements', path: '/measurements' },
];

export default function () {
  if (!signedIn) logIn();

  const page = pages[Math.floor(Math.random() * pages.length)];
  const res = http.get(`${base}${page.path}`, {
    redirects: 0,
    tags: { page: page.id },
  });
  check(res, {
    'authenticated page returns 200': (r) => r.status === 200,
  });
  sleep(1 + Math.random() * 2);
}
