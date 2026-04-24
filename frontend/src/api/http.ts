/**
 * Cliente HTTP mínimo para la API Laravel (prefijo /api/v1).
 * El Bearer lo añade {@link ../auth/oidcAdapter} (sesión OIDC); el perfil de negocio es GET /me.
 */
import { appendBearerAuthorization, triggerSignIn } from '../auth/oidcAdapter';

const DEFAULT_BASE_URL = 'http://logs-api.localhost/api/v1';

export class ApiHttpError extends Error {
  readonly status: number;

  constructor(message: string, status: number) {
    super(message);
    this.name = 'ApiHttpError';
    this.status = status;
  }
}

function getBaseUrl(): string {
  const raw = import.meta.env.VITE_API_URL ?? DEFAULT_BASE_URL;
  return raw.replace(/\/$/, '');
}

function buildUrl(path: string): string {
  const base = getBaseUrl();
  const normalizedPath = path.replace(/^\//, '');
  return path.startsWith('http') ? path : `${base}/${normalizedPath}`;
}

async function authHeaders(jsonBody: boolean): Promise<HeadersInit> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
  };

  if (jsonBody) {
    headers['Content-Type'] = 'application/json';
  }

  await appendBearerAuthorization(headers);

  return headers;
}

async function parseErrorMessage(response: Response): Promise<string> {
  const ct = response.headers.get('content-type') ?? '';
  if (ct.includes('application/json')) {
    try {
      const body = (await response.json()) as { message?: string; error?: string };
      return body.message ?? body.error ?? response.statusText;
    } catch {
      return response.statusText;
    }
  }
  return response.statusText;
}

export type ApiFetchOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
};

export async function apiFetchJson<T>(path: string, options: ApiFetchOptions = {}): Promise<T> {
  const method = options.method ?? 'GET';
  const hasBody = options.body !== undefined && method !== 'GET';
  const url = buildUrl(path);

  const response = await fetch(url, {
    method,
    headers: await authHeaders(hasBody),
    body: hasBody ? JSON.stringify(options.body) : undefined,
  });

  if (!response.ok) {
    if (response.status === 401) {
      triggerSignIn();
    }
    const msg = await parseErrorMessage(response);
    throw new ApiHttpError(msg || `HTTP ${response.status}`, response.status);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  const text = await response.text();
  if (text === '') {
    return undefined as T;
  }

  return JSON.parse(text) as T;
}

export async function apiGetJson<T>(path: string): Promise<T> {
  return apiFetchJson<T>(path, { method: 'GET' });
}

/**
 * Construye una URL absoluta a la API respetando VITE_API_URL. Útil para SSE / streaming
 * donde no podemos usar `fetch` con el mismo envelope JSON.
 */
export function buildApiUrl(path: string): string {
  return buildUrl(path);
}

/**
 * Devuelve el Bearer token actual si hay sesión OIDC activa. Útil para construir
 * conexiones EventSource (SSE) con `EventSourcePolyfill` que sí acepta headers.
 */
export async function getBearerToken(): Promise<string | null> {
  const headers: Record<string, string> = {};
  await appendBearerAuthorization(headers);
  if (!headers.Authorization) return null;
  return headers.Authorization.replace(/^Bearer\s+/i, '');
}
