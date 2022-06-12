<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210625132524 extends AbstractMigration
{
    private $priority = [
        'основатель',
        'фаундер',
        'founder',
        'edutech',
        'edtech',
        'CTO',
        'CEO',
        'COO',
        'CFO',
        'cео',
        'сто',
        'руководитель',
        'программист',
        'adtech',
        'edtech',
        'коуч',
        'ментор',
        'инвестор',
        'инвестици',
        'network',
        'нетворк',
        'продукт',
        'предприниматель',
        'предприятие',
        'travel',
        'medtech',
        'marketplace',
        'AI',
        'product',
        'it',
        'продюсер',
        'smm',
        'партнер',
        'e-commerce',
        'маркет',
        'B2B',
        'B2C',
        'HR',
        'Health',
        'digital',
        'Analytic',
        'saas',
        'сша',
        'китай',
        'евро',
        'ml',
        'market',
        'scient',
        'bussines',
        'businessman',
        'businesswoman',
        'ит',
        'startup',
        'стартап',
        'robot',
        'робот',
        'медицна',
        'сельхоз',
        'промышленность',
        'психолог',
        'психология',
        'обучение',
        'финансы',
        'ветеринар',
        'животные',
        'fintech',
        'finance',
        'онбординг',
        'директ',
        'cpo',
        'разработка',
        'разработчик',
        'owner',
        'fitness',
        'спорт',
        'фитнес',
        'инфобизнес',
        'еда',
        'food',
        'доставка',
        'венчурный',
        'venture',
        'фонд',
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE priority_metric_keyword_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE priority_metric_keyword (id INT NOT NULL, keyword VARCHAR(255) NOT NULL, weight DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function postUp(Schema $schema): void
    {
        $query = $this->connection->createQueryBuilder()
            ->insert('priority_metric_keyword');

        foreach ($this->priority as $item) {
            $query->values([
                'id' => '(SELECT nextval(\'priority_metric_keyword_id_seq\'))',
                'keyword' => ':k',
            ])->setParameter('k', $item)->execute();
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE priority_metric_keyword_id_seq CASCADE');
        $this->addSql('DROP TABLE priority_metric_keyword');
    }
}
