/**
 * Tipos de dominio compartidos por las páginas de logs, archived-logs,
 * error-codes y comments. Mapean 1:1 con los Resources del backend.
 */

export type LogSeverity ='critical' |'high' |'medium' |'low' |'other';

export const LOG_SEVERITY_KEYS: ReadonlyArray<LogSeverity> = [
'critical',
'high',
'medium',
'low',
'other',
];

export type ApplicationRef = {
 id: number;
 name: string;
};

export type UserRef = {
 id: number;
 name: string;
};

export type ErrorCodeRef = {
 id: number;
 code: string;
 name: string;
};

/** Ver {@link LogResource} en el backend. */
export type Log = {
 id: number;
 severity: LogSeverity | string;
 message: string;
 metadata: Record<string, unknown> | null;
 resolved: boolean;
 file: string | null;
 line: number | null;
 created_at: string | null;
 application?: ApplicationRef;
 error_code?: ErrorCodeRef | null;
};

/** Ver {@link ArchivedLogResource}. */
export type ArchivedLog = {
 id: number;
 severity: LogSeverity | string;
 message: string;
 metadata: Record<string, unknown> | null;
 metadata_formatted: string | null;
 description: string | null;
 url_tutorial: string | null;
 original_created_at: string | null;
 archived_at: string | null;
 updated_at: string | null;
 deleted_at: string | null;
 application?: ApplicationRef | null;
 archived_by?: UserRef | null;
 error_code?: ErrorCodeRef | null;
 comments_count?: number;
};

/** Ver {@link ErrorCodeResource}. */
export type ErrorCode = {
 id: number;
 code: string;
 name: string;
 file: string | null;
 line: number | null;
 description: string | null;
 created_at: string | null;
 updated_at: string | null;
 application?: ApplicationRef;
 comments_count?: number;
};

/** Ver {@link CommentResource}. */
export type Comment = {
 id: number;
 content: string;
 commentable_type: string;
 commentable_id: number;
 created_at: string | null;
 updated_at: string | null;
 can_edit?: boolean;
 can_delete?: boolean;
 user?: UserRef | null;
};

/**
 * Entrada del payload SSE`/api/v1/logs/stream`.
 * Shape plana distinta de {@link Log}: el backend sólo proyecta campos básicos
 * (ver {@link LogService::streamPayload}).
 */
export type LogStreamItem = {
 id: number;
 severity: LogSeverity | string;
 message: string;
 application: string | null;
 error_code: string | null;
 created_at: string | null;
};

/** Payload que entrega el endpoint SSE`/api/v1/logs/stream`: array plano de items. */
export type LogStreamPayload = LogStreamItem[];
