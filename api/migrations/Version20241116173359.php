<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116173359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entries metadata table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE entries_metadata (id SERIAL NOT NULL, entry_id UUID NOT NULL, metadata JSONB NOT NULL, processor VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2153D45CBA364942 ON entries_metadata (entry_id)');
        $this->addSql('CREATE INDEX idx_entry_metatada_processor ON entries_metadata (processor)');
        $this->addSql('CREATE INDEX idx_entry_metatada_created_at ON entries_metadata (created_at)');
        $this->addSql('COMMENT ON COLUMN entries_metadata.entry_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN entries_metadata.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE entries_metadata ADD CONSTRAINT FK_2153D45CBA364942 FOREIGN KEY (entry_id) REFERENCES entries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX unique_entry_processor ON entries_metadata (entry_id, processor)');

        // Indexes for complexity processor fields
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_flesch_kincaid_grade_level ON entries_metadata ((metadata->>'flesch_kincaid_grade_level')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_flesch_reading_ease ON entries_metadata ((metadata->>'flesch_reading_ease')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_gunning_fog_index ON entries_metadata ((metadata->>'gunning_fog_index')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_smog_index ON entries_metadata ((metadata->>'smog_index')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_automated_readability_index ON entries_metadata ((metadata->>'automated_readability_index')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_coleman_liau_index ON entries_metadata ((metadata->>'coleman_liau_index')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_linsear_write_formula ON entries_metadata ((metadata->>'linsear_write_formula')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_dale_chall_readability_score ON entries_metadata ((metadata->>'dale_chall_readability_score')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_readability_consensus ON entries_metadata ((metadata->>'readability_consensus')) WHERE processor = 'complexity'");
        $this->addSql("CREATE INDEX idx_entry_metatada_complexity_complexity_rating ON entries_metadata ((metadata->>'complexity_rating')) WHERE processor = 'complexity'");

        // Indexes for sentiment processor fields
        $this->addSql("CREATE INDEX idx_entry_metatada_sentiment_neutral ON entries_metadata ((metadata->>'neutral')) WHERE processor = 'sentiment'");
        $this->addSql("CREATE INDEX idx_entry_metatada_sentiment_compound ON entries_metadata ((metadata->>'compound')) WHERE processor = 'sentiment'");
        $this->addSql("CREATE INDEX idx_entry_metatada_sentiment_negative ON entries_metadata ((metadata->>'negative')) WHERE processor = 'sentiment'");
        $this->addSql("CREATE INDEX idx_entry_metatada_sentiment_positive ON entries_metadata ((metadata->>'positive')) WHERE processor = 'sentiment'");

        // Indexes for keywords processor fields
        $this->addSql("CREATE INDEX idx_entry_metatada_keywords_key ON entries_metadata USING gin (metadata jsonb_ops) WHERE processor = 'keywords'");

        // Index for summary field
        $this->addSql("CREATE INDEX idx_entry_metatada_keywords_summary ON entries_metadata ((metadata->>'summary')) WHERE processor = 'sumary'");

        // Generic index for processor field
        $this->addSql("CREATE INDEX idx_entry_metatada_metadata_generic ON entries_metadata USING gin (metadata jsonb_path_ops)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entries_metadata DROP CONSTRAINT FK_2153D45CBA364942');
        $this->addSql('DROP TABLE entries_metadata');

        $this->addSql("DROP INDEX idx_entry_metatada_complexity_flesch_kincaid_grade_level");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_flesch_reading_ease");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_gunning_fog_index");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_smog_index");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_automated_readability_index");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_coleman_liau_index");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_linsear_write_formula");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_dale_chall_readability_score");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_readability_consensus");
        $this->addSql("DROP INDEX idx_entry_metatada_complexity_complexity_rating");

        $this->addSql("DROP INDEX idx_entry_metatada_sentiment_neutral");
        $this->addSql("DROP INDEX idx_entry_metatada_sentiment_compound");
        $this->addSql("DROP INDEX idx_entry_metatada_sentiment_negative");
        $this->addSql("DROP INDEX idx_entry_metatada_sentiment_positive");

        $this->addSql("DROP INDEX idx_entry_metatada_keywords_key");

        $this->addSql("DROP INDEX idx_entry_metatada_keywords_summary");

        $this->addSql("DROP INDEX idx_entry_metatada_metadata_generic");
    }
}
