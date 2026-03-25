-- Ejecutar como superusuario (sail/postgres)
CREATE USER panel_user WITH PASSWORD 'your_secure_password';

-- logs: solo lectura (gestionada por n8n)
GRANT SELECT ON logs TO panel_user;

-- applications: solo lectura?
GRANT SELECT ON applications TO panel_user;

-- archived_logs: lectura/escritura, sin DELETE por diseño (Escenario 2)
GRANT SELECT, INSERT, UPDATE ON archived_logs TO panel_user;
REVOKE DELETE, TRUNCATE ON archived_logs FROM panel_user;

-- comments: lectura/escritura (polimórfica: cubre archived_logs y error_codes)
GRANT SELECT, INSERT, UPDATE ON comments TO panel_user;

-- error_codes: lectura/escritura
GRANT SELECT, INSERT, UPDATE ON error_codes TO panel_user;

-- users: lectura e inserción
GRANT SELECT, INSERT ON users TO panel_user;

-- Sequences necesarias para INSERT
GRANT USAGE, SELECT ON SEQUENCE archived_logs_id_seq TO panel_user;
GRANT USAGE, SELECT ON SEQUENCE comments_id_seq TO panel_user;
GRANT USAGE, SELECT ON SEQUENCE error_codes_id_seq TO panel_user;
GRANT USAGE, SELECT ON SEQUENCE users_id_seq TO panel_user;


-- NOTA F-04.9 (COULD): si se implementa borrar histórico, añadir:
-- GRANT DELETE ON archived_logs TO panel_user;
