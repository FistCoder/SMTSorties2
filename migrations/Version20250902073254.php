<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902073254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14541F802E');
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14A76ED395');
        $this->addSql('DROP TABLE user_hangout');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E5D83CC1');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E64D218E');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E876C4DDA');
        $this->addSql('DROP INDEX IDX_20C5B31E876C4DDA ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31E5D83CC1 ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31E64D218E ON hangout');
        $this->addSql('ALTER TABLE hangout DROP organizer_id, DROP state_id, DROP location_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AF5D55E1');
        $this->addSql('DROP INDEX IDX_8D93D649AF5D55E1 ON user');
        $this->addSql('ALTER TABLE user DROP campus_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_hangout (user_id INT NOT NULL, hangout_id INT NOT NULL, INDEX IDX_78C8AD14A76ED395 (user_id), INDEX IDX_78C8AD14541F802E (hangout_id), PRIMARY KEY(user_id, hangout_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14541F802E FOREIGN KEY (hangout_id) REFERENCES hangout (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hangout ADD organizer_id INT DEFAULT NULL, ADD state_id INT NOT NULL, ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E5D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E64D218E FOREIGN KEY (location_id) REFERENCES location (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_20C5B31E876C4DDA ON hangout (organizer_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E5D83CC1 ON hangout (state_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E64D218E ON hangout (location_id)');
        $this->addSql('ALTER TABLE `user` ADD campus_id INT NOT NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D649AF5D55E1 ON `user` (campus_id)');
    }
}
