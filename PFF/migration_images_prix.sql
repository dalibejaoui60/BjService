-- ============================================================
-- migration_images_prix.sql — à exécuter si tu as DÉJÀ importé
-- migration_chat.sql avant (sinon c'est déjà inclus dedans).
-- ============================================================
USE bdmf;

ALTER TABLE messages ADD COLUMN imagePath VARCHAR(255) NULL;
ALTER TABLE panier ADD COLUMN prixFinal DOUBLE NULL;
