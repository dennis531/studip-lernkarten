<?php

namespace Lernkarten\Models;

use Course;
use RuntimeException;
use SimpleORMap;
use User;

/**
 * @property int $id database column
 * @property int $parent_id database column
 * @property string $context_id database column
 * @property string $context_type database column
 * @property string $name database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property this $parent database relationship
 * @property this[] $children database relationship
 * @property Deck[] $decks database relationship
 */
class Folder extends SimpleORMap
{
    use HasPolicy;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lernkarten_folders';

        $config['belongs_to']['parent'] = [
            'class_name' => Folder::class,
            'foreign_key' => 'parent_id',
        ];

        $config['has_many']['children'] = [
            'class_name' => Folder::class,
            'assoc_foreign_key' => 'parent_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        $config['has_many']['decks'] = [
            'class_name' => Deck::class,
            'assoc_foreign_key' => 'folder_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        parent::configure($config);
    }

    public static function findByUser(User $user): iterable
    {
        return self::findBySql('context_id = ? AND context_type = ?', [$user->id, User::class]);
    }

    /**
     * @return User|Course|null
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
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
}
