<?php

namespace Lernkarten\Models;

use Course;
use DBManager;
use RuntimeException;
use SimpleORMap;
use User;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Deck extends SimpleORMap
{
    use HasPolicy;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lernkarten_decks';

        $config['has_many']['cards'] = [
            'class_name' => Card::class,
            'assoc_foreign_key' => 'deck_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        $config['has_many']['shared_decks'] = [
            'class_name' => SharedDeck::class,
            'assoc_foreign_key' => 'deck_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        $config['belongs_to']['folder'] = [
            'class_name' => Folder::class,
            'foreign_key' => 'folder_id',
        ];

        $config['belongs_to']['owner'] = [
            'class_name' => User::class,
            'foreign_key' => 'owner_id',
        ];

        $config['belongs_to']['template'] = [
            'class_name' => Deck::class,
            'foreign_key' => 'template_id',
        ];

        $config['belongs_to']['shared_deck'] = [
            'class_name' => SharedDeck::class,
            'foreign_key' => 'shared_deck_id',
        ];

        $config['registered_callbacks']['after_delete'][] = function ($deck) {
            DBManager::get()->execute(
                'UPDATE lernkarten_decks SET template_id = NULL WHERE template_id = ?',
                [$deck->id]
            );
        };

        parent::configure($config);
    }

    public function copy(User $user): Deck
    {
        $resource = self::create([
            'folder_id' => $this->folder_id,
            'context_id' => $user->id,
            'context_type' => User::class,
            'name' => $this->name,
            'description' => $this->description,
            'owner_id' => $user->id,
            'template_id' => $this->id,
            'shared_deck_id' => null,
        ]);

        $resource->importCardsFromDeck($this);

        return $resource;
    }

    public function getNumberOfCards(): int
    {
        return Card::countBySql('deck_id = ?', [$this->id]);
    }

    /**
     * @return User|Course|null
     */
    public function getContext()
    {
        switch ($this->context_type) {
            case Course::class:
                /** @var Course|null */
                return Course::find($this->context_id);
            case User::class:
                /** @var User|null */
                return User::find($this->context_id);
        }

        throw new RuntimeException('Unknown context_type.');
    }

    /**
     * @return array{0:int, 1:int, 2:int, 3:int} an array containing the number of cards per state.
     *                                           The state is encoded as 0-3 and used as the key
     *                                           to the array.
     */
    public function getProgress(): array
    {
        $sql =
            'SELECT state, COUNT(state) as count FROM `lernkarten_cards` WHERE deck_id = ? AND state IS NOT NULL GROUP BY state';
        $results = DBManager::get()->fetchPairs($sql, [$this->id], function ($x) {
            return (int) $x;
        });

        $progress = array_fill(0, 4, 0);
        foreach ($results as $key => $value) {
            $progress[(int) $key] = $value;
        }

        $progress[0] += $this->getNumberOfCards() - array_sum($results);

        return $progress;
    }

    public function getSharedWith(): iterable
    {
        return $this->shared_decks->map(function ($sharedDeck) {
            return $sharedDeck->getRecipient();
        });
    }

    public function importCardsFromDeck(Deck $deck): void
    {
        if ($deck->id === $this->id) {
            return;
        }

        DBManager::get()->execute(
            'INSERT INTO lernkarten_cards (note_id, original_card_id, deck_id) ' .
                'SELECT note_id, id as original_card_id, ? as deck_id ' .
                'FROM `lernkarten_cards` ' .
                'WHERE deck_id = ?',
            [$this->id, $deck->id]
        );
    }

    public function isSharedWith(User $user): bool
    {
        foreach ($this->shared_decks as $sharedDeck) {
            if ($sharedDeck->isSharedWith($user)) {
                return true;
            }
        }

        return false;
    }
}
