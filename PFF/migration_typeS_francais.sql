-- ============================================================
-- migration_typeS_francais.sql — à exécuter une seule fois
-- pour convertir les catégories de service en français
-- (JEUX / PAIEMENT / LOGO / PUBLICITES) partout dans la base.
-- ============================================================
USE bdmf;

UPDATE service SET typeS = 'JEUX'        WHERE typeS = 'GAME';
UPDATE service SET typeS = 'PAIEMENT'    WHERE typeS = 'PAYMENT';
UPDATE service SET typeS = 'PUBLICITES'  WHERE typeS = 'ADS';
-- LOGO reste LOGO (déjà identique en français)
