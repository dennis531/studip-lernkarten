<?php

namespace Lernkarten\Models;

use DBManager;
use SimpleORMap;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Card extends SimpleORMap
{
    use HasPolicy;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lernkarten_cards';

        $config['belongs_to']['deck'] = [
            'class_name' => Deck::class,
            'foreign_key' => 'deck_id',
        ];
        $config['belongs_to']['note'] = [
            'class_name' => Note::class,
            'foreign_key' => 'note_id',
        ];
        $config['belongs_to']['original_card'] = [
            'class_name' => Card::class,
            'foreign_key' => 'original_card_id',
        ];
        $config['has_many']['derived_cards'] = [
            'class_name' => Card::class,
            'assoc_foreign_key' => 'original_card_id',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        $config['registered_callbacks']['before_create'][] = function (Card $card) {
            // set defaults
            $now = time();
            $card->due = $now;
            $card->last_review = $now;
        };

        $config['registered_callbacks']['after_create'][] = function (Card $card) {
            $card->addColearningCards();
        };

        $config['registered_callbacks']['after_update'][] = function (Card $card) {
            $card->updateColearningCards();
        };

        $config['registered_callbacks']['after_delete'][] = function (Card $card) {
            $card->removeColearningCards();
            $card->disconnectDerivedCards();
            Note::prune();
        };

        parent::configure($config);
    }

    public function updateFields(array $fields): void
    {
        $this->note = $this->note->cloneWithFields($fields);
        $this->store();
        $this->updateColearningCards();
        Note::prune();
    }

    private function getColearningDeckIds(): array
    {
        return DBManager::get()->fetchFirst(
            'SELECT id FROM lernkarten_decks WHERE colearning = 1 AND template_id = ?',
            [$this->deck_id]
        );
    }

    private function addColearningCards(): void
    {
        DBManager::get()->execute(
            'INSERT INTO lernkarten_cards (note_id, original_card_id, deck_id) ' .
                'SELECT ? as note_id, ? as original_card_id, id as deck_id ' .
                'FROM lernkarten_decks ' .
                'WHERE id IN (?)',
            [$this->note_id, $this->id, $this->getColearningDeckIds()]
        );
    }

    private function removeColearningCards(): void
    {
        DBManager::get()->execute(
            'DELETE FROM lernkarten_cards WHERE deck_id IN (?) AND original_card_id = ?',
            [$this->getColearningDeckIds(), $this->id]
        );
    }

    private function disconnectDerivedCards(): void
    {
        DBManager::get()->execute(
            'UPDATE lernkarten_cards SET original_card_id = NULL WHERE original_card_id = ?',
            [$this->id]
        );
    }

    private function updateColearningCards(): void
    {
        $this->removeColearningCards();
        $this->addColearningCards();
    }
}
