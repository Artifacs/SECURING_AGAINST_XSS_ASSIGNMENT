
DROP DATABASE IF EXISTS data_consulting_db;
CREATE DATABASE data_consulting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE data_consulting_db;

-- ------------------------------------------------------------
-- USER ENTITY: people who can log in to the system
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name      VARCHAR(50)  NOT NULL,
    last_name       VARCHAR(50)  NOT NULL,
    address         VARCHAR(150) NOT NULL,
    age             TINYINT UNSIGNED NOT NULL,
    email           VARCHAR(100) NOT NULL,
    username        VARCHAR(30)  NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,          -- NEVER store plain passwords
    date_registered DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PARENT: clients
--   added_by     = user who inserted the record
--   updated_by   = user who last updated it (NULL until first update)
--   last_updated = timestamp of the last update (set on insert too)
-- ------------------------------------------------------------
CREATE TABLE clients (
    client_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_name   VARCHAR(100) NOT NULL,
    contact_person VARCHAR(80)  NOT NULL,
    email          VARCHAR(100) NOT NULL,
    phone_number   VARCHAR(20)  NOT NULL,
    industry       VARCHAR(60)  NOT NULL,
    date_added     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by       INT UNSIGNED NOT NULL,
    updated_by     INT UNSIGNED NULL DEFAULT NULL,
    last_updated   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (client_id),
    UNIQUE KEY uq_client_email (email),
    CONSTRAINT fk_clients_added_by   FOREIGN KEY (added_by)   REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_clients_updated_by FOREIGN KEY (updated_by) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CHILDREN: projects (many per client)
-- ------------------------------------------------------------
CREATE TABLE projects (
    project_id   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    client_id    INT UNSIGNED  NOT NULL,
    project_name VARCHAR(120)  NOT NULL,
    service_type VARCHAR(60)   NOT NULL,
    status       VARCHAR(20)   NOT NULL DEFAULT 'Planned',
    budget       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    start_date   DATE          NOT NULL,
    deadline     DATE          NOT NULL,
    date_added   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by     INT UNSIGNED  NOT NULL,
    updated_by   INT UNSIGNED  NULL DEFAULT NULL,
    last_updated DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id),
    CONSTRAINT fk_projects_client     FOREIGN KEY (client_id)  REFERENCES clients (client_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_projects_added_by   FOREIGN KEY (added_by)   REFERENCES users (user_id)     ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_projects_updated_by FOREIGN KEY (updated_by) REFERENCES users (user_id)     ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- No sample rows: register a user first, then add clients/projects as that user.
