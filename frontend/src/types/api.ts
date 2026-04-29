/**
 * Tipos compartidos para las respuestas de la API Laravel /api/v1.
 */

export type ApiEnvelope<T> = {
 data: T;
};

export type PaginationMeta = {
 current_page: number;
 from: number | null;
 last_page: number;
 per_page: number;
 to: number | null;
 total: number;
 path?: string;
};

export type PaginationLinks = {
 first: string | null;
 last: string | null;
 prev: string | null;
 next: string | null;
};

/**
 * Forma estándar de un`AnonymousResourceCollection` paginado de Laravel.
 */
export type PaginatedResponse<T> = {
 data: T[];
 links: PaginationLinks;
 meta: PaginationMeta;
};

export type SortDir ='asc' |'desc';
