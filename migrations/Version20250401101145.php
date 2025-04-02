<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401101145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE campus_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE etat_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE lieu_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE participant_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE sortie_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE ville_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE campus (id INT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE etat (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE lieu (id INT NOT NULL, ville_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, rue VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2F577D59A73F0036 ON lieu (ville_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participant (id INT NOT NULL, campus_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, administrateur BOOLEAN NOT NULL, actif BOOLEAN NOT NULL, pseudo VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D79F6B115126AC48 ON participant (mail)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D79F6B1186CC499D ON participant (pseudo)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D79F6B11AF5D55E1 ON participant (campus_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sortie (id INT NOT NULL, etat_id INT DEFAULT NULL, campus_id INT DEFAULT NULL, lieu_id INT DEFAULT NULL, organisateur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, date_heure_debut TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duree TIME(0) WITHOUT TIME ZONE NOT NULL, date_limite_inscription TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, nb_inscription_max INT NOT NULL, infos_sortie VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F2D5E86FF ON sortie (etat_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F2AF5D55E1 ON sortie (campus_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F26AB213CC ON sortie (lieu_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F2D936B2FA ON sortie (organisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sortie_participant (sortie_id INT NOT NULL, participant_id INT NOT NULL, PRIMARY KEY(sortie_id, participant_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E6D4CDADCC72D953 ON sortie_participant (sortie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E6D4CDAD9D1C3019 ON sortie_participant (participant_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ville (id INT NOT NULL, nom VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F26AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDADCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDAD9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE campus_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE etat_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE lieu_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE participant_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE sortie_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE ville_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu DROP CONSTRAINT FK_2F577D59A73F0036
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant DROP CONSTRAINT FK_D79F6B11AF5D55E1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP CONSTRAINT FK_3C3FD3F2D5E86FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP CONSTRAINT FK_3C3FD3F2AF5D55E1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP CONSTRAINT FK_3C3FD3F26AB213CC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP CONSTRAINT FK_3C3FD3F2D936B2FA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP CONSTRAINT FK_E6D4CDADCC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP CONSTRAINT FK_E6D4CDAD9D1C3019
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE campus
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE etat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE lieu
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sortie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sortie_participant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ville
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
