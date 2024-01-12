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
class SharedDeck extends SimpleORMap
{
    use HasPolicy;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lernkarten_shared_decks';

        $config['belongs_to']['deck'] = [
            'class_name' => Deck::class,
            'foreign_key' => 'deck_id',
        ];

        $config['belongs_to']['sharer'] = [
            'class_name' => User::class,
            'foreign_key' => 'sharer_id',
        ];

        $config['registered_callbacks']['after_delete'][] = function ($sharedDeck) {
            // delete all colearning decks of this shared deck
            Deck::deleteBySql('colearning = 1 AND shared_deck_id = ?', [$sharedDeck->id]);

            // disconnect all copies from the shared deck
            DBManager::get()->execute(
                'UPDATE lernkarten_decks SET shared_deck_id = NULL, template_id = NULL WHERE shared_deck_id = ?',
                [$sharedDeck->id]
            );
        };

        parent::configure($config);
    }

    public static function findByUser(User $user): iterable
    {
        $sharedByMe = self::findBySql('sharer_id = ?', [$user->id]);
        $sharedWithMe = self::findBySql('recipient_id = ? AND recipient_type = ?', [
            $user->id,
            User::class,
        ]);

        $ids = DBManager::get()->fetchFirst(
            'SELECT seminar_id FROM seminar_user WHERE user_id = ?',
            [$user->id]
        );
        $sharedWithCourse = self::findBySql('recipient_id IN (?) AND recipient_type = ?', [
            $ids,
            Course::class,
        ]);

        return array_merge($sharedByMe, $sharedWithMe, $sharedWithCourse);
    }

    /**
     * @param User|Course|null $recipient
     */
    public static function isShared(Deck $deck, $recipient): bool
    {
        return !!self::findOneBySql('deck_id = ? AND recipient_id = ? AND recipient_type = ?', [
            $deck->id,
            $recipient->getId(),
            get_class($recipient),
        ]);
    }

    public function colearn(User $user): Deck
    {
        $existing = $this->getColearningDeck($user);
        if ($existing) {
            return $existing;
        }

        $resource = Deck::create([
            'folder_id' => null,
            'context_id' => $this->recipient_id,
            'context_type' => $this->recipient_type,
            'name' => $this->deck->name,
            'description' => $this->deck->description,
            'metadata' => $this->deck->metadata,
            'owner_id' => $user->id,
            'shared_deck_id' => $this->id,
            'template_id' => $this->deck_id,
            'colearning' => 1,
        ]);

        $resource->importCardsFromDeck($this->deck);

        return $resource;
    }

    public function copyToWorkPlace(User $user): Deck
    {
        return $this->deck->copyToWorkPlace($user, $this);
    }

    public function getColearningDeck(User $user): ?Deck
    {
        return Deck::findOneBySql('template_id = ? AND owner_id = ? AND colearning = 1', [
            $this->deck_id,
            $user->id,
        ]);
    }

    /**
     * @return User|Course|null
     */
    public function getRecipient()
    {
        switch ($this->recipient_type) {
            case Course::class:
                /** @var Course|null */
                return Course::find($this->recipient_id);
            case User::class:
                /** @var User|null */
                return User::find($this->recipient_id);
        }

        throw new RuntimeException('Unknown recipient_type.');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function isSharedWith(User $user): bool
    {
        switch ($this->recipient_type) {
            case Course::class:
                return $GLOBALS['perm']->have_studip_perm(
                    'autor',
                    $this->recipient_id,
                    $user->id
                );
            case User::class:
                return $this->recipient_id === $user->id;
        }
    }
}
