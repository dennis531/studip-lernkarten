<?php

namespace Lernkarten\Models;

use DBManager;
use JsonApi\Errors\InternalServerError;
use Lernkarten\Llm\OpenaiClient;
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

    /**
     * Generate new cards
     *
     * @param Deck $deck target deck
     * @param string $content file text content
     * @param int $number number of cards to be generated
     * @return Card[] generated cards
     */
    public static function generate(Deck $deck, string $content, int $number): array
    {
        // Collect existing cards
        $exiting_cards = self::findBySQL("deck_id = ?", [$deck->id]);
        $existing_cards_fields = array_map(function ($card) {
            $fields = json_decode($card->note->fields, true);

            return [
                'front' => $fields['front'],
                'back' => $fields['back'],
            ];
        }, $exiting_cards);
        $exiting_cards_json = json_encode($existing_cards_fields);

        // Build prompts
        $system_prompt = "Sie sind Student/-in an einer Hochschule und erstellen Lernkarten.";

        // TODO: Use template
        $user_prompt = "Die Inhalte für die Lernkarten:
            
$content

Erstellen Sie ";

        if ($number === 1) {
            $user_prompt .= 'eine Karte mit Musterlösung';
        } else {
            $user_prompt .= $number . ' Karten mit Musterlösungen';
        }

        $user_prompt .= ' zum Inhalt. Formatieren Sie die Ausgabe als JSON.';

        if (!empty($exiting_cards)) {
            $user_prompt .= " Stellen Sie nur NEUE Lernkarten, die sich von den unten stehenden unterscheiden!
                    
                    $exiting_cards_json  
                ";
        } else {
            $user_prompt .= ' Beispiel:

[
    {
        "front": "Das ist eine Aufgabe",
        "back": "Das ist eine Musterlösung"
    }
]
                ';
        }

        if ($number === 1) {
            $user_prompt .= "\nNun die nächste Lernkarte:";
        } else {
            $user_prompt .= "\nNun die nächsten $number Lernkarten:";
        }

        //file_put_contents('user_prompt.txt', $user_prompt);

        $client = OpenaiClient::getInstance();
        $response = $client->request($system_prompt, $user_prompt);

        //file_put_contents('openai_response.txt', $response['choices'][0]['message']['content']);

        $text = $response['choices'][0]['message']['content'];
        // Extract json from text
        if (!preg_match('/\[.*\]/xs', $text, $matches)) {
            preg_match('/\{.*\}/xs', $text, $matches);
        }

        // Process OpenAI response
        $generated_cards = json_decode($matches[0], true);
        if (!$generated_cards) {
            throw new InternalServerError('Could not generate cards');
        }

        if (!is_array($generated_cards)) {
            // Ensure data is array
            $generated_cards = [$generated_cards];
        }

        $card_objects = [];

        // Store generated cards
        foreach ($generated_cards as $generated_card) {
            if (array_key_exists('front', $generated_card) && array_key_exists('back', $generated_card)) {
                $fields = json_encode($generated_card);
                $note = Note::create(['model' => 'basic', 'fields' => $fields]);

                $card_objects[] = self::create([
                    'deck_id' => $deck->id,
                    'note_id' => $note->id,
                ]);
            }
        }

        return $card_objects;
    }

    public function getFields(): array
    {
        return json_decode($this->note['fields'], true);
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
