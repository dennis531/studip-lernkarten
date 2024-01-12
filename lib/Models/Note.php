<?php

namespace Lernkarten\Models;

use DBManager;
use SimpleORMap;

class Note extends SimpleORMap
{
    use HasPolicy;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lernkarten_notes';

        $config['has_many']['cards'] = [
            'class_name' => Card::class,
            'assoc_foreign_key' => 'note_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        $config['registered_callbacks']['before_create'][] = function ($note) {
            do {
                $guid = sha1(uniqid(__CLASS__, true));
            } while (self::exists($guid));

            $note->guid = $guid;
        };

        parent::configure($config);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function cloneWithFields(array $fields): Note
    {
        $clone = Note::create(['model' => $this->model, 'fields' => json_encode($fields)]);

        return $clone;
    }

    public static function prune(): void
    {
        $ids = \DBManager::get()->fetchFirst(
            'SELECT `lernkarten_notes`.`id` FROM `lernkarten_notes` ' .
                'LEFT JOIN `lernkarten_cards` ON `lernkarten_notes`.id = `lernkarten_cards`.`note_id`' .
                ' WHERE `lernkarten_cards`.`id` IS NULL'
        );
        self::deleteBySql('id IN (?)', [$ids]);
    }
}
